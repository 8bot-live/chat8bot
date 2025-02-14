<?php
if (!defined('ABSPATH')) {
    exit; // Prevent direct access.
}

function chat8bot_chat_history_filename() {
    if (!session_id()) session_start();
    $sessionID = session_id();
    $plugin_dir = plugin_dir_path(__FILE__);
    $plugin_dir = dirname(plugin_dir_path(__FILE__), 1);
    $chat_history_dir = $plugin_dir . "//chat_history/";
    $chat_file = $chat_history_dir . "chat_{$sessionID}.json";
    return $chat_file;
}

function chat8bot_update_history($message, $role = "visitor") {

    $chat_filename = chat8bot_chat_history_filename(); 
    $chat_data = file_exists($chat_filename) ? json_decode(file_get_contents($chat_filename), true) : [];
    $ip_address = ($role == "visitor") ? $_SERVER['REMOTE_ADDR'] : $_SERVER['SERVER_ADDR'];

    $chat_data[] = [
        "role" => $role,
        "timestamp" => date("Y-m-d H:i:s"),
        "msg" => $message,
        "ip" => $ip_address
    ];

    //email chat start?
    $emailChatStart = get_option('chat8bot_email_new', 0); 
    if (
        $emailChatStart 
        && isset($chat_data[0]) //client message
        && isset($chat_data[1]) //server message
        && !isset($chat_data[2]) //no second message
    ) {

        //email body
        $email_content = "New Chat8Bot Conversation<br><br>";
        foreach($chat_data as $chatLine) {
            $email_content .= "<br>";
            $email_content .= "<b>{$chatLine['timestamp']}</b> {$chatLine['ip']}<br>"; 
            $email_content .= "<b>{$chatLine['role']}</b>: {$chatLine['msg']}<br>";
        }

        //send chat start
        if (!function_exists('wp_mail'))  require_once ABSPATH . 'wp-includes/pluggable.php';
        $emailAddress = get_option('chat8bot_email_address', get_option('admin_email'));
        $email_headers = array(
            "MIME-Version: 1.0",
            "Content-type: text/html; charset=utf-8",
            "From: $emailAddress",
        );
        wp_mail($emailAddress, "New Chat8Bot Conversation", $email_content, $email_headers);
    }

    file_put_contents($chat_filename, json_encode($chat_data, JSON_PRETTY_PRINT));
    return true;
}


function chat8bot_handle_ajax_request() {

    if (!isset($_POST['message'])) {
        wp_send_json_error(['error' => 'Invalid request']);
    }
 
    //load plugin vars
    $api_url = 'https://api.openai.com/v1/chat/completions';
    $message = sanitize_text_field($_POST['message']);
    $test_mode = get_option('chat8bot_test_mode', 1);
    $api_key = get_option('chat8bot_api_key', '');
    $gpt_model = get_option('chat8bot_gpt_model', '');
    $gpt_max_tokens = get_option('chat8bot_gpt_max_tokens', '');
    $gpt_max_word_reply = get_option('chat8bot_gpt_max_word_reply', '');
    $gpt_msg_memory = get_option('chat8bot_msg_memory', '');
    $gpt_temperature = get_option('chat8bot_gpt_temperature', '');

    //Exit early - Test Mode or Missing API
    if ($test_mode) wp_send_json_success(['response' => 'Test message received: ' . $message]);
    if (!$api_key || strlen($api_key) < 20) wp_send_json_error(['error' => 'API key not configured, please use test mode']);

    //Folders
    $plugin_dir = dirname(plugin_dir_path(__FILE__), 1);
    $cache_dir = "{$plugin_dir}/cache";

    //Load KnowledgeBase 
    $knowledgeBaseContent = get_option('chat8bot_knowlegbase', "");

    //Word Limit to KnowledgeBase
    $limitStr = "## Client Interaction - Response: \r\n-------------------------------------------\r\n";
    $limitStr = "Response Length: Limit responses to $gpt_max_word_reply words or less, unless the active message content is longer than this (respond using as many words needed). \r\n";
    $knowledgeBaseContent = $limitStr . $knowledgeBaseContent . "\r\n";

    //append chat history to knowlegbase
    $chat_file = chat8bot_chat_history_filename();
    if (intval($gpt_msg_memory) > 0 && file_exists($chat_file)) {
        $qtyComments = $gpt_msg_memory * 2; //question + reply
        $chat_json = file_get_contents($chat_file);
        $chat_data = json_decode($chat_json, true);
        $chat_data = array_slice($chat_data, - ($qtyComments), $qtyComments, true);

        file_put_contents("{$cache_dir}/chat_history_slice_$gpt_msg_memory.txt", json_encode($chat_data, JSON_PRETTY_PRINT));

        //loop chat 
        $chatStr = "## Recent Visitor Chat History: \r\n-------------------------------------------\r\n";
        foreach ($chat_data as $charArr) {
            $role = ($charArr['role'] == 'visitor') ? $charArr['role'] : 'chatGPT';
            $chatStr .= "[{$charArr['timestamp']}]\/n $role: {$charArr['msg']}\/r\/n";
        }
        //prepend
        $knowledgeBaseContent = $chatStr . $knowledgeBaseContent . "\r\n";
    }

    $request_data = [
        'model' => $gpt_model,
        'messages' => [
            ['role' => 'system', 'content' => "Use the following knowledge base for reference:\n" . $knowledgeBaseContent],
            ['role' => 'user', 'content' => $message]
        ],
        'max_tokens' => intval($gpt_max_tokens),
        'temperature' => floatval($gpt_temperature),
    ];

    chat8bot_update_history($message, "visitor");

    //save curl questions
    file_put_contents("{$cache_dir}/last_curl1_request.txt", json_encode($request_data, JSON_PRETTY_PRINT));

    $ch = curl_init($api_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $api_key,
        'Content-Type: application/json',
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($request_data));
    $response = curl_exec($ch);
    $error = curl_error($ch);
    curl_close($ch);

    if ($error) wp_send_json_error(['error' => 'API request failed: ' . $error]);
    $decoded_response = json_decode($response, true);
    $message_response = $decoded_response['choices'][0]['message']['content'] ?? 'No response from AI.';

    chat8bot_update_history($message_response, "server");

    //save curl response
    file_put_contents("{$cache_dir}/last_curl2_response.txt", json_encode(array_merge($decoded_response), JSON_PRETTY_PRINT));

    wp_send_json_success(['response' => $message_response]);
}

add_action('wp_ajax_chat8bot_send_message', 'chat8bot_handle_ajax_request');
add_action('wp_ajax_nopriv_chat8bot_send_message', 'chat8bot_handle_ajax_request');
