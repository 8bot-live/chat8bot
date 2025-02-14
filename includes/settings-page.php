<?php

if (!defined('ABSPATH')) {
    exit; // Prevent direct access.
}


function chat8bot_add_admin_menu() {
    add_menu_page(
        'Chat8Bot Settings',
        'Chat8Bot',
        'manage_options',
        'chat8bot-settings',
        'chat8bot_settings_page',
        'dashicons-format-chat'
    );

    add_submenu_page(
        'chat8bot-settings',
        'Conversations',
        'Conversations',
        'manage_options',
        'chat8bot-conversations',
        'chat8bot_conversations_page'
    );
}
add_action('admin_menu', 'chat8bot_add_admin_menu');

// Add admin styles
function chat8bot_admin_styles() {
    wp_enqueue_style('chat8bot-admin-style', CHAT8BOT_PLUGIN_URL . 'assets/css/admin-style.css');
}
add_action('admin_enqueue_scripts', 'chat8bot_admin_styles');


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

    register_setting('chat8bot_settings_group', 'chat8bot_knowlegbase');

    register_setting('chat8bot_settings_group', 'chat8bot_email_address');
    register_setting('chat8bot_settings_group', 'chat8bot_email_new');
    register_setting('chat8bot_settings_group', 'chat8bot_email_after_hrs'); //chat8bot_email_after_min

    register_setting('chat8bot_settings_group', 'chat8bot_recaptcha_key');
    register_setting('chat8bot_settings_group', 'chat8bot_recaptcha_sec');
}
add_action('admin_init', 'chat8bot_register_settings');

// Settings page content new

function chat8bot_settings_page() {
    $current_tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'general';
    ?>
    <div class="wrap">
        <h1>Chat8Bot Settings</h1>
        
        <nav class="nav-tab-wrapper">
            <a href="?page=chat8bot-settings&tab=general" class="nav-tab <?php echo $current_tab === 'general' ? 'nav-tab-active' : ''; ?>">General Settings</a>
            <a href="?page=chat8bot-settings&tab=api" class="nav-tab <?php echo $current_tab === 'api' ? 'nav-tab-active' : ''; ?>">ChatGPT API</a>
            <a href="?page=chat8bot-settings&tab=knowledge" class="nav-tab <?php echo $current_tab === 'knowledge' ? 'nav-tab-active' : ''; ?>">Knowledge Base</a>
        </nav>

        <form method="post" action="options.php">
            <?php
            settings_fields('chat8bot_settings_group');
            do_settings_sections('chat8bot-settings');
            ?>
            
            <!-- General Settings Fields - Always present but conditionally displayed -->
            <div class="tab-content" style="display: <?php echo $current_tab === 'general' ? 'block' : 'none'; ?>">
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
            </div>

            <!-- API Settings Fields - Always present but conditionally displayed -->
            <div class="tab-content" style="display: <?php echo $current_tab === 'api' ? 'block' : 'none'; ?>">
                <h2>ChatGPT API Settings</h2>
                <small>Setup your API and pay based on token usage https://platform.openai.com/settings/</small>
                <table class="form-table">
                    <tr>
                        <th scope="row">API Key</th>
                        <td><input type="text" name="chat8bot_api_key" value="<?php echo esc_attr(get_option('chat8bot_api_key', '')); ?>" class="large-text" /></td>
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
                            <input type="text" name="chat8bot_gpt_max_word_reply" value="<?php echo esc_attr(get_option('chat8bot_gpt_max_word_reply', '100')); ?>" class="small-text" /> Appended to knowlegebase
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Remember prev questions</th>
                        <td>
                            <input type="text" name="chat8bot_msg_memory" value="<?php echo esc_attr(get_option('chat8bot_msg_memory', '10')); ?>" class="small-text" /> Chat history appended to knowlegebase
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Temperature</th>
                        <td><input type="text" name="chat8bot_gpt_temperature" value="<?php echo esc_attr(get_option('chat8bot_gpt_temperature', '1')); ?>" class="small-text" /> // 0.5 - 1.5 (stable - diverse)</td>
                    </tr>
                </table>
            </div>

            <!-- Knowledge Base Fields - Always present but conditionally displayed -->
            <div class="tab-content" style="display: <?php echo $current_tab === 'knowledge' ? 'block' : 'none'; ?>">
                <h2>Knowledge Base Content</h2>
                <p>The knowledge base allows you to provide specific information to help guide ChatGPT responses. This is sent for each chat comment.</p>
                <textarea name="chat8bot_knowlegbase" rows="20" cols="50" class="large-text"><?php echo esc_textarea(get_option('chat8bot_knowlegbase', '')); ?></textarea>
                <small>About 1000 words is roughly equivalent to 750 tokens, which is used for calculating usage costs.</small>
            </div>

            <?php submit_button(); ?>
        </form>
        <br><br>
        Developed for free by Ged. Donate via <a href="https://paypal.me/8botlive" target="_blank">paypal</a>
    </div>
    <?php
}


 