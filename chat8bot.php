<?php

/**
 * Plugin Name: Chat8Bot
 * Plugin URI: https://8bot.live
 * Description: A chatbot plugin using OpenAI API.
 * Version: 1.1
 * Author: Ged 
 * Author URI: https://8bot.live
 * Text Domain: chat8bot
 */


if (!defined('ABSPATH')) {
    exit; // Prevent direct access.
}

// Define plugin paths.
define('CHAT8BOT_PLUGIN_URL', plugin_dir_url(__FILE__));
define('CHAT8BOT_PLUGIN_DIR', plugin_dir_path(__FILE__));

// Include required files.
require_once CHAT8BOT_PLUGIN_DIR . 'includes/settings-page.php';
require_once CHAT8BOT_PLUGIN_DIR . 'includes/ajax-chat.php';

// Add settings and donate links to the plugin action links
function chat8bot_add_plugin_links($links) {
    $settings_link = '<a href="admin.php?page=chat8bot-settings">Settings</a>';
    $donate_link = '<a href="https://www.paypal.com/donate?hosted_button_id=YOUR_PAYPAL_ID" target="_blank">Donate</a>'; 
    array_unshift($links, $settings_link, $donate_link); 
    return $links;
}  
add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'chat8bot_add_plugin_links');

// Enqueue scripts and styles.
function enqueue_chat8bot_assets() {
    
    wp_enqueue_style('chat8bot-style', CHAT8BOT_PLUGIN_URL . 'assets/css/style-chat8bot.css');
    wp_enqueue_script('chat8bot-script', CHAT8BOT_PLUGIN_URL . 'assets/js/script-chat8bot.js', ['jquery'], null, true);
    
    // //recaptcha invisable v2
    // $recaptcha_site_key = get_option('chat8bot_recaptcha_key', ''); 
    // if(strlen($recaptcha_site_key) > 10) {
    //     wp_enqueue_script('recaptcha', 'https://www.google.com/recaptcha/api.js?render=explicit', array(), null, true);
    //     wp_localize_script('chat8bot-script', 'chat8botRecaptcha', [
    //         'recaptcha_site_key' => $recaptcha_site_key,
    //     ]);
    // }

    wp_localize_script('chat8bot-script', 'chat8botData', [
        'ajax_url' => admin_url('admin-ajax.php'),
        'chat8bot_btn_text' => get_option('chat8bot_name', 'AI chatbot'),
        'chat8bot_title' => get_option('chat8bot_title', 'AI chatbot'),
        'chat8bot_intro_message' => get_option('chat8bot_intro_message', 'Feel free to ask anything about our product or services.'),
    ]);
 
    //colors print style
    $clr8bot_1a = esc_attr(get_option('chat8bot_primary_color_text', '#FFFFFF'));
    $clr8bot_1b = esc_attr(get_option('chat8bot_primary_color_bg', '#FF5733'));
    $clr8bot_2a = esc_attr(get_option('chat8bot_secondary_color_text', '#333333'));
    $clr8bot_2b = esc_attr(get_option('chat8bot_secondary_color_bg', '#FF5733'));
    echo "
    <style>
        :root {
            --chat8bot-color1-text: $clr8bot_1a; 
            --chat8bot-color1-bg: $clr8bot_1b; 
            --chat8bot-color2-text: $clr8bot_2a;
            --chat8bot-color2-bg: $clr8bot_2b;
        }
    </style>";
    //var(--chat8bot-color1-text);
    
}
add_action('wp_enqueue_scripts', 'enqueue_chat8bot_assets');

 
// Email chats completed (hourly)
function chat8bot_email_history() {

    $plugin_dir = plugin_dir_path(__FILE__);
    $chat_history_dir = $plugin_dir . 'chat_history/';
    $sent_dir = $chat_history_dir . 'sent/';

    if (!file_exists($sent_dir))  mkdir($sent_dir, 0755, true); //create send folder?

    foreach (glob($chat_history_dir . "chat_*.json") as $chat_file) {

        $chat_json = file_get_contents($chat_file);
        $chat_data = json_decode($chat_json, true);
        if (!$chat_data) continue;

        $last_timestamp = strtotime(end($chat_data)['timestamp']);
        $emailHrs = get_option('chat8bot_email_after_hrs', 1);
        $emailAddress = get_option('chat8bot_email_address', get_option('admin_email'));
        
        if (time() - $last_timestamp < (60 * 60 * $emailHrs)) continue; // Skip if < 1hr 

        $email_content = "<b>Chat History</b><br>";
        foreach ($chat_data as $chatArr) {
            $email_content .= "[{$chatArr['timestamp']}]\n<br> {$chatArr['role']}: {$chatArr['msg']}\n\n<br><br>";
        }

        //send
        if (!function_exists('wp_mail'))  require_once ABSPATH . 'wp-includes/pluggable.php';
     
        $email_headers = array(
            "MIME-Version: 1.0",
            "Content-type: text/html; charset=utf-8",
            "From: $emailAddress",
        );
        wp_mail($emailAddress, "Chat8Bot Conversation Log", $email_content, $email_headers);
        
        //move
        $sent_file = $sent_dir . basename($chat_file);
        $counter = 1;
        while (file_exists($sent_file)) {
            $sent_file = $sent_dir . str_replace('.json', "_$counter.json", basename($chat_file));
            $counter++;
        }
        rename($chat_file, $sent_file);

    }
    return true;
}
chat8bot_email_history();

if (!wp_next_scheduled('chat8bot_email_history_event')) {
    wp_schedule_event(time(), 'hourly', 'chat8bot_email_history_event');
}
add_action('chat8bot_email_history_event', 'chat8bot_email_history');
 