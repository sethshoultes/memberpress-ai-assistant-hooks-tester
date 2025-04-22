<?php
/**
 * Admin Menu Integration
 * 
 * Adds a menu item for the Hooks Tester to the MemberPress AI Assistant menu
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

/**
 * Process admin actions
 */
function mpai_hooks_tester_process_admin_actions() {
    if (!isset($_GET['page']) || $_GET['page'] !== 'memberpress-ai-assistant-hooks-tester') {
        return;
    }
    
    if (isset($_GET['action']) && isset($_GET['nonce'])) {
        $action = sanitize_text_field($_GET['action']);
        $nonce = sanitize_text_field($_GET['nonce']);
        
        if ($action === 'clear_log' && wp_verify_nonce($nonce, 'mpai_hooks_tester_clear_log')) {
            // Get upload directory
            $upload_dir = wp_upload_dir();
            $log_file = $upload_dir['basedir'] . '/mpai-hooks-tester.log';
            
            // Clear log file
            file_put_contents($log_file, '');
            
            // Log the action
            if (function_exists('mpai_log_debug')) {
                mpai_log_debug('Hooks Tester log cleared');
            }
            
            // Redirect back to the page
            wp_redirect(admin_url('admin.php?page=memberpress-ai-assistant-hooks-tester&cleared=1'));
            exit;
        }
        
        if ($action === 'test_process_message' && wp_verify_nonce($nonce, 'mpai_hooks_tester_test_process_message')) {
            // Trigger the hooks directly
            if (class_exists('MPAI_Chat')) {
                $chat = new MPAI_Chat();
                $chat->process_message('This is a test message from MPAI Hooks Tester');
                
                if (function_exists('mpai_log_debug')) {
                    mpai_log_debug('Manually triggered process_message');
                }
            }
            
            // Redirect back to the page
            wp_redirect(admin_url('admin.php?page=memberpress-ai-assistant-hooks-tester&tested=process_message'));
            exit;
        }
        
        if ($action === 'test_clear_history' && wp_verify_nonce($nonce, 'mpai_hooks_tester_test_clear_history')) {
            // Trigger the hooks directly
            if (class_exists('MPAI_Chat')) {
                $chat = new MPAI_Chat();
                if (method_exists($chat, 'reset_conversation')) {
                    $chat->reset_conversation();
                    
                    if (function_exists('mpai_log_debug')) {
                        mpai_log_debug('Manually triggered reset_conversation');
                    }
                }
            }
            
            // Redirect back to the page
            wp_redirect(admin_url('admin.php?page=memberpress-ai-assistant-hooks-tester&tested=clear_history'));
            exit;
        }
    }
}
add_action('admin_init', 'mpai_hooks_tester_process_admin_actions');

/**
 * Add menu item to MemberPress AI Assistant
 */
function mpai_hooks_tester_add_menu_item($menu_items, $has_memberpress) {
    // Add our submenu item
    $menu_items[] = [
        'type' => 'submenu',
        'parent' => $has_memberpress ? 'memberpress' : 'memberpress-ai-assistant',
        'page_title' => __('Hooks Tester', 'mpai-hooks-tester'),
        'menu_title' => __('Hooks Tester', 'mpai-hooks-tester'),
        'capability' => 'manage_options',
        'menu_slug' => 'memberpress-ai-assistant-hooks-tester',
        'callback' => 'mpai_hooks_tester_display_admin_page'
    ];
    
    return $menu_items;
}
add_filter('MPAI_HOOK_FILTER_admin_menu_items', 'mpai_hooks_tester_add_menu_item', 10, 2);

/**
 * Display admin page
 */
function mpai_hooks_tester_display_admin_page() {
    ?>
    <div class="wrap">
        <h1>MemberPress AI Assistant - Hooks Tester</h1>
        
        <?php
        // Display information notice about hooks being triggered on page load
        echo '<div class="notice notice-info is-dismissible"><p>' .
            __('Note: Hooks are registered when the plugin loads, so they\'re triggered during WordPress initialization. This is why you see test results already.', 'mpai-hooks-tester') .
            '</p></div>';
            
        // Display success messages
        if (isset($_GET['cleared'])) {
            echo '<div class="notice notice-success is-dismissible"><p>' . __('Log cleared successfully.', 'mpai-hooks-tester') . '</p></div>';
        }
        
        if (isset($_GET['tested'])) {
            $test_type = sanitize_text_field($_GET['tested']);
            if ($test_type === 'process_message') {
                echo '<div class="notice notice-success is-dismissible"><p>' . __('Process message hooks tested successfully.', 'mpai-hooks-tester') . '</p></div>';
            } elseif ($test_type === 'clear_history') {
                echo '<div class="notice notice-success is-dismissible"><p>' . __('Clear history hooks tested successfully.', 'mpai-hooks-tester') . '</p></div>';
            }
        }
        ?>
        
        <div class="card">
            <h2>Hook Test Log</h2>
            <p>This page displays the log of hook executions.</p>
            
            <?php
            // Get upload directory
            $upload_dir = wp_upload_dir();
            $log_file = $upload_dir['basedir'] . '/mpai-hooks-tester.log';
            
            // Display log file content
            if (file_exists($log_file)) {
                $log_content = file_get_contents($log_file);
                ?>
                <div class="log-content" style="background: #f5f5f5; padding: 15px; border: 1px solid #ddd; max-height: 500px; overflow: auto;">
                    <pre><?php echo esc_html($log_content); ?></pre>
                </div>
                
                <p>
                    <a href="<?php echo esc_url(admin_url('admin.php?page=memberpress-ai-assistant-hooks-tester&action=clear_log&nonce=' . wp_create_nonce('mpai_hooks_tester_clear_log'))); ?>" class="button button-secondary">Clear Log</a>
                </p>
                <?php
            } else {
                echo '<p>No log file found. Use the MemberPress AI Assistant to trigger hooks.</p>';
            }
            ?>
            
            <h2>Test Hooks</h2>
            <p>Click the buttons below to manually trigger specific hooks for testing:</p>
            
            <p>
                <a href="<?php echo esc_url(admin_url('admin.php?page=memberpress-ai-assistant-hooks-tester&action=test_process_message&nonce=' . wp_create_nonce('mpai_hooks_tester_test_process_message'))); ?>" class="button button-primary">Test Process Message Hooks</a>
                <a href="<?php echo esc_url(admin_url('admin.php?page=memberpress-ai-assistant-hooks-tester&action=test_clear_history&nonce=' . wp_create_nonce('mpai_hooks_tester_test_clear_history'))); ?>" class="button button-primary">Test Clear History Hooks</a>
            </p>
            
            <h2>Registered Hooks</h2>
            <p>The following hooks are registered and being tested:</p>
            
            <div style="display: flex; flex-wrap: wrap; gap: 20px;">
                <div style="flex: 1; min-width: 300px;">
                    <h3>Actions</h3>
                    <ul>
                        <li><code>MPAI_HOOK_ACTION_before_plugin_init</code></li>
                        <li><code>MPAI_HOOK_ACTION_loaded_dependencies</code></li>
                        <li><code>MPAI_HOOK_ACTION_after_plugin_init</code></li>
                        <li><code>MPAI_HOOK_ACTION_before_process_message</code></li>
                        <li><code>MPAI_HOOK_ACTION_after_process_message</code></li>
                        <li><code>MPAI_HOOK_ACTION_before_save_history</code></li>
                        <li><code>MPAI_HOOK_ACTION_after_save_history</code></li>
                        <li><code>MPAI_HOOK_ACTION_before_clear_history</code></li>
                        <li><code>MPAI_HOOK_ACTION_after_clear_history</code></li>
                        <li><code>MPAI_HOOK_ACTION_tool_registry_init</code></li>
                        <li><code>MPAI_HOOK_ACTION_register_tool</code></li>
                        <li><code>MPAI_HOOK_ACTION_before_tool_execution</code></li>
                        <li><code>MPAI_HOOK_ACTION_after_tool_execution</code></li>
                        <li><code>MPAI_HOOK_ACTION_register_agent</code></li>
                        <li><code>MPAI_HOOK_ACTION_before_agent_process</code></li>
                        <li><code>MPAI_HOOK_ACTION_after_agent_process</code></li>
                        <li><code>MPAI_HOOK_ACTION_before_display_settings</code></li>
                        <li><code>MPAI_HOOK_ACTION_after_display_settings</code></li>
                        <li><code>MPAI_HOOK_ACTION_before_api_request</code></li>
                        <li><code>MPAI_HOOK_ACTION_after_api_request</code></li>
                        <li><code>MPAI_HOOK_ACTION_before_error_recovery</code></li>
                        <li><code>MPAI_HOOK_ACTION_after_error_recovery</code></li>
                    </ul>
                </div>
                
                <div style="flex: 1; min-width: 300px;">
                    <h3>Filters</h3>
                    <ul>
                        <li><code>MPAI_HOOK_FILTER_default_options</code></li>
                        <li><code>MPAI_HOOK_FILTER_plugin_capabilities</code></li>
                        <li><code>MPAI_HOOK_FILTER_system_prompt</code></li>
                        <li><code>MPAI_HOOK_FILTER_chat_conversation_history</code></li>
                        <li><code>MPAI_HOOK_FILTER_message_content</code></li>
                        <li><code>MPAI_HOOK_FILTER_response_content</code></li>
                        <li><code>MPAI_HOOK_FILTER_user_context</code></li>
                        <li><code>MPAI_HOOK_FILTER_allowed_commands</code></li>
                        <li><code>MPAI_HOOK_FILTER_history_retention</code></li>
                        <li><code>MPAI_HOOK_FILTER_tool_parameters</code></li>
                        <li><code>MPAI_HOOK_FILTER_tool_execution_result</code></li>
                        <li><code>MPAI_HOOK_FILTER_available_tools</code></li>
                        <li><code>MPAI_HOOK_FILTER_tool_capability_check</code></li>
                        <li><code>MPAI_HOOK_FILTER_agent_capabilities</code></li>
                        <li><code>MPAI_HOOK_FILTER_agent_validation</code></li>
                        <li><code>MPAI_HOOK_FILTER_agent_scoring</code></li>
                        <li><code>MPAI_HOOK_FILTER_agent_handoff</code></li>
                        <li><code>MPAI_HOOK_FILTER_generated_content</code></li>
                        <li><code>MPAI_HOOK_FILTER_content_formatting</code></li>
                        <li><code>MPAI_HOOK_FILTER_blog_post_content</code></li>
                        <li><code>MPAI_HOOK_FILTER_content_type</code></li>
                        <li><code>MPAI_HOOK_FILTER_content_marker</code></li>
                        <li><code>MPAI_HOOK_FILTER_chat_interface_render</code></li>
                        <li><code>MPAI_HOOK_FILTER_api_request_params</code></li>
                        <li><code>MPAI_HOOK_FILTER_api_response</code></li>
                        <li><code>MPAI_HOOK_FILTER_api_provider</code></li>
                        <li><code>MPAI_HOOK_FILTER_cache_ttl</code></li>
                        <li><code>MPAI_HOOK_FILTER_api_error_handling</code></li>
                        <li><code>MPAI_HOOK_FILTER_error_message</code></li>
                        <li><code>MPAI_HOOK_FILTER_error_should_retry</code></li>
                        <li><code>MPAI_HOOK_FILTER_log_entry</code></li>
                        <li><code>MPAI_HOOK_FILTER_should_log</code></li>
                        <li><code>MPAI_HOOK_FILTER_log_level</code></li>
                        <li><code>MPAI_HOOK_FILTER_log_retention</code></li>
                        <li><code>MPAI_HOOK_FILTER_sanitize_log_data</code></li>
                    </ul>
                </div>
            </div>
        </div>
        
        <!-- Second "Registered Hooks" section removed to avoid duplication -->
    </div>
    <?php
}