<?php
/**
 * Plugin Name: MemberPress AI Assistant - Hooks Tester
 * Description: A simple plugin to test and verify the hooks in MemberPress AI Assistant
 * Version: 1.0.0
 * Author: MemberPress
 * Author URI: https://memberpress.com
 * Text Domain: mpai-hooks-tester
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

class MPAI_Hooks_Tester {
    
    /**
     * Log file path
     */
    private $log_file;
    
    /**
     * Initialize the plugin
     */
    public function __construct() {
        // Set up log file
        $upload_dir = wp_upload_dir();
        $this->log_file = $upload_dir['basedir'] . '/mpai-hooks-tester.log';
        
        // Initialize log file
        $this->log('MPAI Hooks Tester initialized');
        
        // Register hooks for testing
        $this->register_hooks();
        
        // Initialize content hooks tester
        $this->init_content_hooks_tester();
    }
    
    /**
     * Register hooks for testing
     */
    private function register_hooks() {
        // Plugin Initialization Hooks
        add_action('MPAI_HOOK_ACTION_before_plugin_init', [$this, 'test_before_plugin_init']);
        add_action('MPAI_HOOK_ACTION_loaded_dependencies', [$this, 'test_loaded_dependencies']);
        add_action('MPAI_HOOK_ACTION_after_plugin_init', [$this, 'test_after_plugin_init']);

        // Plugin Filters
        add_filter('MPAI_HOOK_FILTER_default_options', [$this, 'test_default_options']);
        add_filter('MPAI_HOOK_FILTER_plugin_capabilities', [$this, 'test_plugin_capabilities']);

        // Chat Processing Hooks
        add_action('MPAI_HOOK_ACTION_before_process_message', [$this, 'test_before_process_message']);
        add_action('MPAI_HOOK_ACTION_after_process_message', [$this, 'test_after_process_message'], 10, 2);

        // Chat Processing Filters
        add_filter('MPAI_HOOK_FILTER_system_prompt', [$this, 'test_system_prompt']);
        add_filter('MPAI_HOOK_FILTER_chat_conversation_history', [$this, 'test_chat_conversation_history']);
        add_filter('MPAI_HOOK_FILTER_message_content', [$this, 'test_message_content']);
        add_filter('MPAI_HOOK_FILTER_response_content', [$this, 'test_response_content'], 10, 2);
        add_filter('MPAI_HOOK_FILTER_user_context', [$this, 'test_user_context']);
        add_filter('MPAI_HOOK_FILTER_allowed_commands', [$this, 'test_allowed_commands']);

        // History Management Hooks
        add_action('MPAI_HOOK_ACTION_before_save_history', [$this, 'test_before_save_history'], 10, 2);
        add_action('MPAI_HOOK_ACTION_after_save_history', [$this, 'test_after_save_history'], 10, 2);
        add_action('MPAI_HOOK_ACTION_before_clear_history', [$this, 'test_before_clear_history']);
        add_action('MPAI_HOOK_ACTION_after_clear_history', [$this, 'test_after_clear_history']);
        add_filter('MPAI_HOOK_FILTER_history_retention', [$this, 'test_history_retention']);
        
        // Tool Execution Hooks
        add_action('MPAI_HOOK_ACTION_tool_registry_init', [$this, 'test_tool_registry_init']);
        add_action('MPAI_HOOK_ACTION_register_tool', [$this, 'test_register_tool'], 10, 3);
        add_action('MPAI_HOOK_ACTION_before_tool_execution', [$this, 'test_before_tool_execution'], 10, 3);
        add_action('MPAI_HOOK_ACTION_after_tool_execution', [$this, 'test_after_tool_execution'], 10, 4);
        add_filter('MPAI_HOOK_FILTER_tool_parameters', [$this, 'test_tool_parameters'], 10, 3);
        add_filter('MPAI_HOOK_FILTER_tool_execution_result', [$this, 'test_tool_execution_result'], 10, 4);
        add_filter('MPAI_HOOK_FILTER_available_tools', [$this, 'test_available_tools'], 10, 2);
        add_filter('MPAI_HOOK_FILTER_tool_capability_check', [$this, 'test_tool_capability_check'], 10, 4);
        
        // Agent System Hooks
        add_action('MPAI_HOOK_ACTION_register_agent', [$this, 'test_register_agent'], 10, 3);
        add_action('MPAI_HOOK_ACTION_before_agent_process', [$this, 'test_before_agent_process'], 10, 5);
        add_action('MPAI_HOOK_ACTION_after_agent_process', [$this, 'test_after_agent_process'], 10, 5);
        add_filter('MPAI_HOOK_FILTER_agent_capabilities', [$this, 'test_agent_capabilities'], 10, 4);
        add_filter('MPAI_HOOK_FILTER_agent_validation', [$this, 'test_agent_validation'], 10, 4);
        add_filter('MPAI_HOOK_FILTER_agent_scoring', [$this, 'test_agent_scoring'], 10, 4);
        add_filter('MPAI_HOOK_FILTER_agent_handoff', [$this, 'test_agent_handoff'], 10, 4);
        
        // Admin menu removed to avoid conflicts
    }
    
    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        add_submenu_page(
            'tools.php',
            'MPAI Hooks Tester',
            'MPAI Hooks Tester',
            'manage_options',
            'mpai-hooks-tester',
            [$this, 'display_admin_page']
        );
    }
    
    /**
     * Display admin page
     */
    public function display_admin_page() {
        ?>
        <div class="wrap">
            <h1>MemberPress AI Assistant - Hooks Tester</h1>
            
            <div class="card">
                <h2>Hook Test Log</h2>
                <p>This page displays the log of hook executions.</p>
                
                <?php
                // Display log file content
                if (file_exists($this->log_file)) {
                    $log_content = file_get_contents($this->log_file);
                    ?>
                    <div class="log-content" style="background: #f5f5f5; padding: 15px; border: 1px solid #ddd; max-height: 500px; overflow: auto;">
                        <pre><?php echo esc_html($log_content); ?></pre>
                    </div>
                    
                    <p>
                        <a href="<?php echo esc_url(admin_url('admin.php?page=mpai-hooks-tester&action=clear_log&nonce=' . wp_create_nonce('mpai_hooks_tester_clear_log'))); ?>" class="button button-secondary">Clear Log</a>
                    </p>
                    <?php
                } else {
                    echo '<p>No log file found. Use the MemberPress AI Assistant to trigger hooks.</p>';
                }
                ?>
                
                <h2>Test Hooks</h2>
                <p>Click the buttons below to manually trigger specific hooks for testing:</p>
                
                <p>
                    <a href="<?php echo esc_url(admin_url('admin.php?page=mpai-hooks-tester&action=test_process_message&nonce=' . wp_create_nonce('mpai_hooks_tester_test_process_message'))); ?>" class="button button-primary">Test Process Message Hooks</a>
                    <a href="<?php echo esc_url(admin_url('admin.php?page=mpai-hooks-tester&action=test_clear_history&nonce=' . wp_create_nonce('mpai_hooks_tester_test_clear_history'))); ?>" class="button button-primary">Test Clear History Hooks</a>
                </p>
            </div>
        </div>
        <?php
    }
    
    /**
     * Process admin actions
     */
    public function process_admin_actions() {
        if (!isset($_GET['page']) || $_GET['page'] !== 'mpai-hooks-tester') {
            return;
        }
        
        if (isset($_GET['action']) && isset($_GET['nonce'])) {
            $action = sanitize_text_field($_GET['action']);
            $nonce = sanitize_text_field($_GET['nonce']);
            
            switch ($action) {
                case 'clear_log':
                    if (wp_verify_nonce($nonce, 'mpai_hooks_tester_clear_log')) {
                        $this->clear_log();
                        wp_redirect(admin_url('admin.php?page=mpai-hooks-tester&cleared=1'));
                        exit;
                    }
                    break;
                    
                case 'test_process_message':
                    if (wp_verify_nonce($nonce, 'mpai_hooks_tester_test_process_message')) {
                        $this->trigger_process_message();
                        wp_redirect(admin_url('admin.php?page=mpai-hooks-tester&tested=process_message'));
                        exit;
                    }
                    break;
                    
                case 'test_clear_history':
                    if (wp_verify_nonce($nonce, 'mpai_hooks_tester_test_clear_history')) {
                        $this->trigger_clear_history();
                        wp_redirect(admin_url('admin.php?page=mpai-hooks-tester&tested=clear_history'));
                        exit;
                    }
                    break;
            }
        }
    }
    
    /**
     * Clear log file
     */
    private function clear_log() {
        file_put_contents($this->log_file, '');
        $this->log('Log cleared');
    }
    
    /**
     * Trigger process message hooks
     */
    private function trigger_process_message() {
        if (class_exists('MPAI_Chat')) {
            $chat = new MPAI_Chat();
            $chat->process_message('This is a test message from MPAI Hooks Tester');
            $this->log('Manually triggered process_message');
        } else {
            $this->log('ERROR: MPAI_Chat class not found');
        }
    }
    
    /**
     * Trigger clear history hooks
     */
    private function trigger_clear_history() {
        if (class_exists('MPAI_Chat')) {
            $chat = new MPAI_Chat();
            if (method_exists($chat, 'reset_conversation')) {
                $chat->reset_conversation();
                $this->log('Manually triggered reset_conversation');
            } else {
                $this->log('ERROR: reset_conversation method not found');
            }
        } else {
            $this->log('ERROR: MPAI_Chat class not found');
        }
    }
    
    /**
     * Log message to file
     */
    private function log($message) {
        $timestamp = date('Y-m-d H:i:s');
        $log_message = "[{$timestamp}] {$message}\n";
        
        file_put_contents($this->log_file, $log_message, FILE_APPEND);
    }
    
    /**
     * Test mpai_before_plugin_init hook
     */
    public function test_before_plugin_init() {
        $this->log('HOOK FIRED: MPAI_HOOK_ACTION_before_plugin_init');
    }
    
    /**
     * Test MPAI_HOOK_ACTION_loaded_dependencies hook
     */
    public function test_loaded_dependencies() {
        $this->log('HOOK FIRED: MPAI_HOOK_ACTION_loaded_dependencies');
    }
    
    /**
     * Test MPAI_HOOK_ACTION_after_plugin_init hook
     */
    public function test_after_plugin_init() {
        $this->log('HOOK FIRED: MPAI_HOOK_ACTION_after_plugin_init');
    }
    
    /**
     * Test MPAI_HOOK_FILTER_default_options filter
     */
    public function test_default_options($options) {
        $this->log('FILTER APPLIED: MPAI_HOOK_FILTER_default_options');
        return $options;
    }
    
    /**
     * Test MPAI_HOOK_FILTER_plugin_capabilities filter
     */
    public function test_plugin_capabilities($capabilities) {
        $this->log('FILTER APPLIED: MPAI_HOOK_FILTER_plugin_capabilities');
        return $capabilities;
    }
    
    /**
     * Test MPAI_HOOK_ACTION_before_process_message hook
     */
    public function test_before_process_message($message) {
        $this->log('HOOK FIRED: MPAI_HOOK_ACTION_before_process_message - Message: ' . substr($message, 0, 30) . '...');
    }
    
    /**
     * Test MPAI_HOOK_ACTION_after_process_message hook
     */
    public function test_after_process_message($message, $result) {
        $this->log('HOOK FIRED: MPAI_HOOK_ACTION_after_process_message - Message: ' . substr($message, 0, 30) . '...');
    }
    
    /**
     * Test MPAI_HOOK_FILTER_system_prompt filter
     */
    public function test_system_prompt($system_prompt) {
        $this->log('FILTER APPLIED: MPAI_HOOK_FILTER_system_prompt - Length: ' . strlen($system_prompt) . ' chars');
        return $system_prompt;
    }
    
    /**
     * Test MPAI_HOOK_FILTER_chat_conversation_history filter
     */
    public function test_chat_conversation_history($conversation) {
        $this->log('FILTER APPLIED: MPAI_HOOK_FILTER_chat_conversation_history - Messages: ' . count($conversation));
        return $conversation;
    }
    
    /**
     * Test MPAI_HOOK_FILTER_message_content filter
     */
    public function test_message_content($message) {
        $this->log('FILTER APPLIED: MPAI_HOOK_FILTER_message_content - Message: ' . substr($message, 0, 30) . '...');
        return $message;
    }
    
    /**
     * Test MPAI_HOOK_FILTER_response_content filter
     */
    public function test_response_content($response, $message) {
        $this->log('FILTER APPLIED: MPAI_HOOK_FILTER_response_content - Response length: ' . strlen($response) . ' chars');
        return $response;
    }
    
    /**
     * Test MPAI_HOOK_FILTER_user_context filter
     */
    public function test_user_context($user_context) {
        $this->log('FILTER APPLIED: MPAI_HOOK_FILTER_user_context - Keys: ' . implode(', ', array_keys($user_context)));
        return $user_context;
    }
    
    /**
     * Test MPAI_HOOK_FILTER_allowed_commands filter
     */
    public function test_allowed_commands($allowed_commands) {
        $this->log('FILTER APPLIED: MPAI_HOOK_FILTER_allowed_commands - Commands: ' . count($allowed_commands));
        return $allowed_commands;
    }
    
    /**
     * Test MPAI_HOOK_ACTION_before_save_history hook
     */
    public function test_before_save_history($message, $response) {
        $this->log('HOOK FIRED: MPAI_HOOK_ACTION_before_save_history - Message: ' . substr($message, 0, 30) . '...');
    }
    
    /**
     * Test MPAI_HOOK_ACTION_after_save_history hook
     */
    public function test_after_save_history($message, $response) {
        $this->log('HOOK FIRED: MPAI_HOOK_ACTION_after_save_history - Message: ' . substr($message, 0, 30) . '...');
    }
    
    /**
     * Test MPAI_HOOK_ACTION_before_clear_history hook
     */
    public function test_before_clear_history() {
        $this->log('HOOK FIRED: MPAI_HOOK_ACTION_before_clear_history');
    }
    
    /**
     * Test MPAI_HOOK_ACTION_after_clear_history hook
     */
    public function test_after_clear_history() {
        $this->log('HOOK FIRED: MPAI_HOOK_ACTION_after_clear_history');
    }
    
    /**
     * Test MPAI_HOOK_FILTER_history_retention filter
     */
    public function test_history_retention($days) {
        $this->log('FILTER APPLIED: MPAI_HOOK_FILTER_history_retention - Days: ' . $days);
        return $days;
    }
    
    /**
     * Test MPAI_HOOK_ACTION_tool_registry_init hook
     */
    public function test_tool_registry_init($registry) {
        $this->log('HOOK FIRED: MPAI_HOOK_ACTION_tool_registry_init');
    }
    
    /**
     * Test MPAI_HOOK_ACTION_register_tool hook
     */
    public function test_register_tool($tool_id, $tool, $registry) {
        $this->log('HOOK FIRED: MPAI_HOOK_ACTION_register_tool - Tool ID: ' . $tool_id);
    }
    
    /**
     * Test MPAI_HOOK_ACTION_before_tool_execution hook
     */
    public function test_before_tool_execution($tool_name, $parameters, $tool) {
        $this->log('HOOK FIRED: MPAI_HOOK_ACTION_before_tool_execution - Tool: ' . $tool_name);
    }
    
    /**
     * Test MPAI_HOOK_ACTION_after_tool_execution hook
     */
    public function test_after_tool_execution($tool_name, $parameters, $result, $tool) {
        $this->log('HOOK FIRED: MPAI_HOOK_ACTION_after_tool_execution - Tool: ' . $tool_name);
    }
    
    /**
     * Test MPAI_HOOK_FILTER_tool_parameters filter
     */
    public function test_tool_parameters($parameters, $tool_name, $tool) {
        $this->log('FILTER APPLIED: MPAI_HOOK_FILTER_tool_parameters - Tool: ' . $tool_name);
        return $parameters;
    }
    
    /**
     * Test MPAI_HOOK_FILTER_tool_execution_result filter
     */
    public function test_tool_execution_result($result, $tool_name, $parameters, $tool) {
        $this->log('FILTER APPLIED: MPAI_HOOK_FILTER_tool_execution_result - Tool: ' . $tool_name);
        return $result;
    }
    
    /**
     * Test MPAI_HOOK_FILTER_available_tools filter
     */
    public function test_available_tools($tools, $registry) {
        $this->log('FILTER APPLIED: MPAI_HOOK_FILTER_available_tools - Tools: ' . count($tools));
        return $tools;
    }
    
    /**
     * Test MPAI_HOOK_FILTER_tool_capability_check filter
     */
    public function test_tool_capability_check($can_use, $tool_name, $parameters, $tool) {
        $this->log('FILTER APPLIED: MPAI_HOOK_FILTER_tool_capability_check - Tool: ' . $tool_name);
        return $can_use;
    }
    
    /**
     * Test MPAI_HOOK_ACTION_register_agent hook
     */
    public function test_register_agent($agent_id, $agent_instance, $orchestrator) {
        $this->log('HOOK FIRED: MPAI_HOOK_ACTION_register_agent - Agent ID: ' . $agent_id);
    }
    
    /**
     * Test MPAI_HOOK_ACTION_before_agent_process hook
     */
    public function test_before_agent_process($agent_id, $params, $user_id, $context, $orchestrator) {
        $this->log('HOOK FIRED: MPAI_HOOK_ACTION_before_agent_process - Agent ID: ' . $agent_id);
    }
    
    /**
     * Test MPAI_HOOK_ACTION_after_agent_process hook
     */
    public function test_after_agent_process($agent_id, $params, $user_id, $result, $orchestrator) {
        $this->log('HOOK FIRED: MPAI_HOOK_ACTION_after_agent_process - Agent ID: ' . $agent_id);
    }
    
    /**
     * Test MPAI_HOOK_FILTER_agent_capabilities filter
     */
    public function test_agent_capabilities($capabilities, $agent_id, $agent, $orchestrator) {
        $this->log('FILTER APPLIED: MPAI_HOOK_FILTER_agent_capabilities - Agent ID: ' . $agent_id);
        return $capabilities;
    }
    
    /**
     * Test MPAI_HOOK_FILTER_agent_validation filter
     */
    public function test_agent_validation($is_valid, $agent_id, $agent, $orchestrator) {
        $this->log('FILTER APPLIED: MPAI_HOOK_FILTER_agent_validation - Agent ID: ' . $agent_id);
        return $is_valid;
    }
    
    /**
     * Test MPAI_HOOK_FILTER_agent_scoring filter
     */
    public function test_agent_scoring($scores, $message, $context, $orchestrator) {
        $this->log('FILTER APPLIED: MPAI_HOOK_FILTER_agent_scoring - Scores: ' . count($scores));
        return $scores;
    }
    
    /**
     * Test MPAI_HOOK_FILTER_agent_handoff filter
     */
    public function test_agent_handoff($selected_agent_id, $agent_scores, $message, $orchestrator) {
        $this->log('FILTER APPLIED: MPAI_HOOK_FILTER_agent_handoff - Selected Agent: ' . $selected_agent_id);
        return $selected_agent_id;
    }
    
    /**
     * Initialize content hooks tester
     */
    private function init_content_hooks_tester() {
        // Include the content hooks tester class
        require_once plugin_dir_path(__FILE__) . 'includes/class-mpai-content-hooks-tester.php';
        
        // Initialize the content hooks tester
        new MPAI_Content_Hooks_Tester();
        
        $this->log('Content Hooks Tester initialized');
    }
}

// Initialize the plugin
$mpai_hooks_tester = new MPAI_Hooks_Tester();

// Process admin actions
add_action('admin_init', [$mpai_hooks_tester, 'process_admin_actions']);

// Include the content hooks tester initialization
require_once plugin_dir_path(__FILE__) . 'init.php';