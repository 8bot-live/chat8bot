<?php
// includes/conversations-page.php

if (!defined('ABSPATH')) {
    exit;
}

function chat8bot_conversations_page() {
    // Handle any POST actions (like manual deletion)
    if (isset($_POST['action']) && $_POST['action'] === 'delete_chat' && isset($_POST['chat_file'])) {
        $file = sanitize_text_field($_POST['chat_file']);
        $full_path = CHAT8BOT_PLUGIN_DIR . 'chat_history/' . $file;
        if (file_exists($full_path)) {
            unlink($full_path);
            echo '<div class="notice notice-success"><p>Chat deleted successfully.</p></div>';
        }
    }

    // Get current view (active or archived)
    $current_view = isset($_GET['view']) ? sanitize_text_field($_GET['view']) : 'active';
    $search_term = isset($_GET['search']) ? sanitize_text_field($_GET['search']) : '';
    $emailAddress = get_option('chat8bot_email_address', get_option('admin_email'));
    
    ?>
    <div class="wrap">
        <h1>Chat8Bot Conversations</h1>
        
        <!-- View Toggle -->
        <ul class="subsubsub">
            <li>
                <a href="?page=chat8bot-conversations&view=active" class="<?php echo $current_view === 'active' ? 'current' : ''; ?>">
                    Live Chats
                </a> |
            </li>
            <li>
                <a href="?page=chat8bot-conversations&view=archived" class="<?php echo $current_view === 'archived' ? 'current' : ''; ?>">
                    Completed Chats 
                </a> (<?= $emailAddress ?>)
            </li>
        </ul>

        <!-- Search Form -->
        <form method="get" style="float: right; margin-bottom: 1em;">
            <input type="hidden" name="page" value="chat8bot-conversations">
            <input type="hidden" name="view" value="<?php echo esc_attr($current_view); ?>">
            <input type="text" name="search" value="<?php echo esc_attr($search_term); ?>" placeholder="Search conversations...">
            <input type="submit" class="button" value="Search">
        </form>

        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th>Session ID</th>
                    <th>Started</th>
                    <th>Last Message</th>
                    <th>Messages</th>
                    <th>IP Address</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Determine which directory to read
                $dir_path = CHAT8BOT_PLUGIN_DIR . 'chat_history/' . 
                           ($current_view === 'archived' ? 'sent/' : '');
                
                // Get all JSON files
                $files = glob($dir_path . "*.json");
                
                if (empty($files)) {
                    echo '<tr><td colspan="6">No conversations found.</td></tr>';
                } else {
                    foreach ($files as $file) {
                        $content = file_get_contents($file);
                        $chat = json_decode($content, true);
                        
                        // Skip if no valid content
                        if (!$chat || !is_array($chat)) continue;
                        
                        // Search filter
                        if ($search_term) {
                            $found = false;
                            foreach ($chat as $message) {
                                if (stripos($message['msg'], $search_term) !== false) {
                                    $found = true;
                                    break;
                                }
                            }
                            if (!$found) continue;
                        }
                        
                        $first_msg = reset($chat);
                        $last_msg = end($chat);
                        $session_id = basename($file, '.json');
                        ?>
                        <tr>
                            <td><?php echo esc_html($session_id); ?></td>
                            <td><?php echo esc_html($first_msg['timestamp']); ?></td>
                            <td><?php echo esc_html($last_msg['timestamp']); ?></td>
                            <td><?php echo count($chat); ?></td>
                            <td><?php echo esc_html($first_msg['ip']); ?></td>
                            <td>
                                <button type="button" class="button view-chat" 
                                        data-chat='<?php echo esc_attr(json_encode($chat)); ?>'>
                                    View
                                </button>
                                
                                <form method="post" style="display: inline;">
                                    <input type="hidden" name="action" value="delete_chat">
                                    <input type="hidden" name="chat_file" value="<?php echo esc_attr(basename($file)); ?>">
                                    <button type="submit" class="button" 
                                            onclick="return confirm('Are you sure you want to delete this chat?');">
                                        Delete
                                    </button>
                                </form>
                            </td>
                        </tr>
                        <?php
                    }
                }
                ?>
            </tbody>
        </table>
    </div>

    <!-- Chat Detail Modal -->
    <div id="chat-modal" style="display: none;" class="chat8bot-modal">
        <div class="chat8bot-modal-content">
            <span class="chat8bot-modal-close">&times;</span>
            <h2>Chat Details</h2>
            <div id="chat-content"></div>
        </div>
    </div>

    <style>
    .chat8bot-modal {
        display: none;
        position: fixed;
        z-index: 1000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0,0,0,0.4);
    }

    .chat8bot-modal-content {
        background-color: #fefefe;
        margin: 5% auto;
        padding: 20px;
        border: 1px solid #888;
        width: 80%;
        max-height: 80vh;
        overflow-y: auto;
    }

    .chat8bot-modal-close {
        color: #aaa;
        float: right;
        font-size: 28px;
        font-weight: bold;
        cursor: pointer;
    }

    .chat-message {
        margin: 10px 0;
        padding: 10px;
        border-radius: 5px;
    }

    .chat-message.visitor {
        background-color: #f0f0f0;
        margin-right: 20%;
    }

    .chat-message.server {
        background-color: #e3f2fd;
        margin-left: 20%;
    }

    .chat-meta {
        font-size: 0.8em;
        color: #666;
        margin-top: 5px;
    }
    </style>

    <script>
    jQuery(document).ready(function($) {
        // View Chat Button Click
        $('.view-chat').click(function() {
            var chat = $(this).data('chat');
            var content = '';
            
            chat.forEach(function(msg) {
                content += '<div class="chat-message ' + msg.role + '">' +
                          '<div class="chat-content">' + msg.msg + '</div>' +
                          '<div class="chat-meta">' + 
                          msg.timestamp + ' | IP: ' + msg.ip + 
                          '</div></div>';
            });
            
            $('#chat-content').html(content);
            $('#chat-modal').show();
        });

        // Close Modal
        $('.chat8bot-modal-close').click(function() {
            $('#chat-modal').hide();
        });

        // Close Modal on Outside Click
        $(window).click(function(e) {
            if ($(e.target).is('#chat-modal')) {
                $('#chat-modal').hide();
            }
        });
    });
    </script>
    <?php
}