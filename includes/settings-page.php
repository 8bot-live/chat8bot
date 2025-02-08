<?php

if (!defined('ABSPATH')) {
    exit; // Prevent direct access.
}

// Add menu page
function chat8bot_add_admin_menu() {
    add_menu_page(
        'Chat8Bot Settings',
        'Chat8Bot',
        'manage_options',
        'chat8bot-settings',
        'chat8bot_settings_page'
    );
}
add_action('admin_menu', 'chat8bot_add_admin_menu');

// Register settings
function chat8bot_register_settings() {
    register_setting('chat8bot_settings_group', 'chat8bot_name');
    register_setting('chat8bot_settings_group', 'chat8bot_title');
    register_setting('chat8bot_settings_group', 'chat8bot_intro_message');

    register_setting('chat8bot_settings_group', 'chat8bot_primary_color_text');
    register_setting('chat8bot_settings_group', 'chat8bot_primary_color_bg');
    register_setting('chat8bot_settings_group', 'chat8bot_secondary_color_text');
    register_setting('chat8bot_settings_group', 'chat8bot_secondary_color_bg');

    register_setting('chat8bot_settings_group', 'chat8bot_test_mode');
    register_setting('chat8bot_settings_group', 'chat8bot_api_key');
    register_setting('chat8bot_settings_group', 'chat8bot_gpt_model');
    register_setting('chat8bot_settings_group', 'chat8bot_gpt_max_tokens');
    register_setting('chat8bot_settings_group', 'chat8bot_gpt_max_word_reply');
    register_setting('chat8bot_settings_group', 'chat8bot_gpt_temperature');
    register_setting('chat8bot_settings_group', 'chat8bot_msg_memory');

    register_setting('chat8bot_settings_group', 'chat8bot_email_address');
    register_setting('chat8bot_settings_group', 'chat8bot_email_new');
    register_setting('chat8bot_settings_group', 'chat8bot_email_after_hrs'); //chat8bot_email_after_min

    register_setting('chat8bot_settings_group', 'chat8bot_recaptcha_key');
    register_setting('chat8bot_settings_group', 'chat8bot_recaptcha_sec');
}
add_action('admin_init', 'chat8bot_register_settings');

// Settings page content
function chat8bot_settings_page() {
    // Define the file path for knowledge-base.txt
    $filenameKB = plugin_dir_path(__FILE__) . 'knowledge-base.txt';

    // Read the content of the knowledge base file
    $kbTextStr = '';
    if (file_exists($filenameKB)) {
        $kbTextStr = file_get_contents($filenameKB);
    }

    // Process form submission
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['knowledge_base_content'])) {
        check_admin_referer('chat8bot_settings_nonce');
        $filenameKB = plugin_dir_path(__FILE__) . 'knowledge-base.txt';
        $kbTextStr = sanitize_textarea_field($_POST['knowledge_base_content']);
        file_put_contents($filenameKB, $kbTextStr);
        echo '<div class="updated"><p>Knowledge Base updated successfully!</p></div>';
    }
?>
    <div class="wrap">

        <form method="post" action="options.php">
            <?php settings_fields('chat8bot_settings_group'); ?>
            <?php do_settings_sections('chat8bot-settings'); ?>
            <h1>Chatbot Settings</h1>
            <hr>
            <table class="form-table">
                <tr>
                    <th scope="row">Test Mode</th>
                    <td><input type="checkbox" name="chat8bot_test_mode" value="1" <?php checked(1, get_option('chat8bot_test_mode', 1)); ?> /> Enable Test Mode</td>
                </tr>
                <tr>
                    <th scope="row">Button Text</th>
                    <td><input type="text" name="chat8bot_name" value="<?php echo esc_attr(get_option('chat8bot_name', '•••')); ?>" class="regular-text" /></td>
                </tr>
                <tr>
                    <th scope="row">Chat Title</th>
                    <td><input type="text" name="chat8bot_title" value="<?php echo esc_attr(get_option('chat8bot_title', 'AI Website Chatbot')); ?>" class="regular-text" /></td>
                </tr>
                <tr>
                    <th scope="row">Intro Message</th>
                    <td><input type="text" name="chat8bot_intro_message" value="<?php echo esc_attr(get_option('chat8bot_intro_message', 'Hi! How can I assist you?')); ?>" class="large-text" /></td>
                </tr>
                <tr>
                    <th scope="row">Theme Colors</th>
                    <td>
                        <table>
                            <tr>
                                <td>Primary</td>
                                <td><input type="text" name="chat8bot_primary_color_text" value="<?php echo esc_attr(get_option('chat8bot_primary_color_text', '#FFFFFF')); ?>" class="" /> Text</td>
                                <td><input type="text" name="chat8bot_primary_color_bg" value="<?php echo esc_attr(get_option('chat8bot_primary_color_bg', '#FF5733')); ?>" class="" /> Background</td>
                            </tr>
                            <tr>
                                <td>Secondary</td>
                                <td><input type="text" name="chat8bot_secondary_color_text" value="<?php echo esc_attr(get_option('chat8bot_secondary_color_text', '#33333')); ?>" class="" /> Text</td>
                                <td><input type="text" name="chat8bot_secondary_color_bg" value="<?php echo esc_attr(get_option('chat8bot_secondary_color_bg', '#e3e3f1')); ?>" class="" /> Background</td>
                            </tr>
                        </table>
                                 
                    </td>
                </tr>
                <tr>
                    <th scope="row">Email Chat History</th>
                    <td><input type="text" name="chat8bot_email_address" value="<?php echo esc_attr(get_option('chat8bot_email_address', get_option('admin_email'))); ?>" class="regular-text" /> (via wp_mail code)</td>
                </tr>
                <tr>
                    <th scope="row">Email Chat Hrs</th>
                    <td>
                        <input type="number" min="1" step="1" name="chat8bot_email_after_hrs" value="<?php echo esc_attr(get_option('chat8bot_email_after_hrs', 10)); ?>" class="small-text" />
                        Emails chat log after [x]hrs since last response
                    </td>
                </tr>
                <tr>
                    <th scope="row">Email New Chat</th>
                    <td><input type="checkbox" name="chat8bot_email_new" value="1" <?php checked(1, get_option('chat8bot_email_new', 1)); ?> /> Emails new chat sessions first Q&A when submitted</td>

                </tr>
            </table>
            <?php submit_button(); ?>

            <hr>
            <h1>ChatGPT API Settings</h1>
            <small>Setup your API and pay based on token usage https://platform.openai.com/settings/</small>
            <table class="form-table">
                <tr>
                    <th scope="row">API Key</th>
                    <td><input width="100" type="text" name="chat8bot_api_key" value="<?php echo esc_attr(get_option('chat8bot_api_key', '')); ?>" class="large-text" /></td>
                </tr>
                <tr>
                    <th scope="row">Model</th>
                    <td>
                        <input type="text" name="chat8bot_gpt_model" value="<?php echo esc_attr(get_option('chat8bot_gpt_model', 'gpt-4-turbo')); ?>" class="regular-text" />
                        Refer to <a href="https://platform.openai.com/docs/models#current-model-aliases" target="_blank">current model aliases</a>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Reply Max Tokens</th>
                    <td><input type="text" name="chat8bot_gpt_max_tokens" value="<?php echo esc_attr(get_option('chat8bot_gpt_max_tokens', '500')); ?>" class="small-text" /> (100 tokens is about 75 words)</td>
                </tr>
                <tr>
                    <th scope="row">Reply Max words</th>
                    <td>
                        <!-- Response Length: Limit responses to 100 words or less. -->
                        <input type="text" name="chat8bot_gpt_max_word_reply" value="<?php echo esc_attr(get_option('chat8bot_gpt_max_word_reply', '100')); ?>" class="small-text" /> Appended to knowlegebase (Response Length: Limit responses to [qty] words or less.)
                    </td>
                </tr>
                <tr>
                    <th scope="row">Remember prev questions</th>
                    <td>
                        <input type="text" name="chat8bot_msg_memory" value="<?php echo esc_attr(get_option('chat8bot_msg_memory', '10')); ?>" class="small-text" /> Chat history appended to knowlegebase (adds token costs)
                    </td>
                </tr>
                <tr>
                    <th scope="row">Temperature</th>
                    <td><input type="text" name="chat8bot_gpt_temperature" value="<?php echo esc_attr(get_option('chat8bot_gpt_temperature', '1')); ?>" class="small-text" /> // 0.5 - 1.5 (stable - dirverse)
                </tr>
            </table>
            <?php submit_button(); ?>

            <hr>
            <h1>Recaptcha Invisable v2</h1>
            <small>Register and enter your keys using Invisable Recaptcha v2. Prevents crawlers and bots from posting.</small>
            <table class="form-table">
                <tr>
                    <th scope="row">Site Key</th>
                    <td><input type="text" name="chat8bot_recaptcha_key" value="<?php echo esc_attr(get_option('chat8bot_recaptcha_key', '')); ?>" class="regular-text" /></td>
                </tr>
                <tr>
                    <th scope="row">Secret Key</th>
                    <td><input type="text" name="chat8bot_recaptcha_sec" value="<?php echo esc_attr(get_option('chat8bot_recaptcha_sec', '')); ?>" class="regular-text" /></td>
                </tr>
            </table>
            <?php submit_button(); ?>

        </form>



        <hr>
        <form method="post">
            <?php wp_nonce_field('chat8bot_settings_nonce'); ?>
            <h1>Knowledge Base Content</h1>
            <p>The knowledge base allows you to provide specific information to help guide ChatGPT response. This is sent for each chat comment.
                To help create your knowledge base, have a conversation with ChatGPT as a user. Ask it to summarize key content from your website,
                which can help you build your knowledge base more effectively. This helps teach the API how to handle common questions,
                provide accurate information, and even filter out irrelevant queries.</p>
            <textarea name="knowledge_base_content" rows="20" cols="50" class="large-text"><?php echo esc_textarea($kbTextStr); ?></textarea>
            <small>About 1000 words is roughly equivalent to 750 tokens, which is used for calculating usage costs.</small>
            <?php submit_button('Save Knowledge Base'); ?>
        </form>

        <!-- GPT Models REF -->
        <br>
        <hr>
        <br>
        <h2>ChatGPT Information</h2>
        <table class="widefat striped ">
            <thead>
                <tr>
                    <th><strong>GTP Models</strong></th>
                    <th><strong>Best For</strong></th>
                    <th><strong>Pros</strong></th>
                    <th><strong>Cons</strong></th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><code>gpt-4-turbo</code></td>
                    <td>Fast &amp; intelligent customer support, FAQ, and general AI chat</td>
                    <td>Faster, cheaper, and optimized version of GPT-4</td>
                    <td>Slightly less capable than full GPT-4</td>
                </tr>
                <tr>
                    <td><code>gpt-4</code></td>
                    <td>High-quality, context-aware conversations</td>
                    <td>More accurate and nuanced responses</td>
                    <td>Slower &amp; more expensive</td>
                </tr>
                <tr>
                    <td><code>gpt-3.5-turbo</code></td>
                    <td>Budget-friendly and fast chatbot responses</td>
                    <td>Quick and cost-effective</td>
                    <td>Less accuracy in complex topics</td>
                </tr>
                <tr>
                    <td><code>gpt-3.5</code></td>
                    <td>Basic chatbot functionalities</td>
                    <td>Cheapest option</td>
                    <td>Limited reasoning ability</td>
                </tr>
            </tbody>
        </table>

        <hr>
        Created Free by Ged. Donate here <a href="https://8bot.live" target="_blank">8bot.live</a>
    </div>
<?php
}
