<?php
/**
 * Content Hooks Tester Class
 *
 * Demonstrates how to use the content and UI hooks in MemberPress AI Assistant
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

class MPAI_Content_Hooks_Tester {
    /**
     * Constructor
     */
    public function __construct() {
        // Register content hooks
        add_filter('MPAI_HOOK_FILTER_generated_content', array($this, 'filter_generated_content'), 10, 2);
        add_filter('MPAI_HOOK_FILTER_content_formatting', array($this, 'filter_content_formatting'), 10, 1);
        add_filter('MPAI_HOOK_FILTER_blog_post_content', array($this, 'filter_blog_post_content'), 10, 2);
        add_filter('MPAI_HOOK_FILTER_content_type', array($this, 'filter_content_type'), 10, 3);
        add_filter('MPAI_HOOK_FILTER_content_marker', array($this, 'filter_content_marker'), 10, 1);
        
        // Register admin interface hooks
        add_filter('MPAI_HOOK_FILTER_settings_fields', array($this, 'filter_settings_fields'), 10, 1);
        add_filter('MPAI_HOOK_FILTER_settings_tabs', array($this, 'filter_settings_tabs'), 10, 1);
        add_action('MPAI_HOOK_ACTION_before_display_settings', array($this, 'before_display_settings'));
        add_action('MPAI_HOOK_ACTION_after_display_settings', array($this, 'after_display_settings'));
        add_filter('MPAI_HOOK_FILTER_chat_interface_render', array($this, 'filter_chat_interface_render'), 10, 3);
    }
    
    /**
     * Filter generated content
     *
     * @param string $content The generated content
     * @param string $content_type The type of content being generated
     * @return string Modified content
     */
    public function filter_generated_content($content, $content_type) {
        // Add a disclaimer to blog posts
        if ($content_type === 'blog_post') {
            $content .= "\n\n<small>This content was enhanced by MPAI Hooks Tester plugin.</small>";
        }
        
        return $content;
    }
    
    /**
     * Filter content formatting rules
     *
     * @param array $rules The formatting rules
     * @return array Modified formatting rules
     */
    public function filter_content_formatting($rules) {
        // Add a custom formatting rule for callouts
        $rules['callout'] = [
            'tag' => 'div',
            'wrapper' => '<!-- wp:custom-block {"className":"mpai-callout"} --><div class="mpai-callout">%s</div><!-- /wp:custom-block -->'
        ];
        
        return $rules;
    }
    
    /**
     * Filter blog post content before creation
     *
     * @param array $post_data The post data
     * @param string $xml_content The original XML content
     * @return array Modified post data
     */
    public function filter_blog_post_content($post_data, $xml_content) {
        // Add a category to all AI-generated blog posts
        if (!isset($post_data['categories'])) {
            $post_data['categories'] = ['AI Content'];
        } else {
            $post_data['categories'][] = 'AI Content';
        }
        
        // Add a prefix to the title
        if (isset($post_data['title'])) {
            $post_data['title'] = '[AI] ' . $post_data['title'];
        }
        
        return $post_data;
    }
    
    /**
     * Filter the detected content type
     *
     * @param string $detected_type The detected content type
     * @param string $block_type The original block type
     * @param string $content The content
     * @return string Modified content type
     */
    public function filter_content_type($detected_type, $block_type, $content) {
        // Detect a custom content type based on content patterns
        if (strpos($content, 'CALLOUT:') === 0) {
            return 'callout';
        }
        
        return $detected_type;
    }
    
    /**
     * Filter content markers used in XML parsing
     *
     * @param array $markers The content markers
     * @return array Modified content markers
     */
    public function filter_content_marker($markers) {
        // Add a new marker for subtitle
        $markers['subtitle'] = 'post-subtitle';
        
        return $markers;
    }
    
    // Admin menu items are now handled by admin-menu.php
    
    /**
     * Filter settings fields
     *
     * @param array $fields The settings fields
     * @return array Modified settings fields
     */
    public function filter_settings_fields($fields) {
        // We need to be careful not to add settings that will cause errors
        // Let's just log that we're filtering the settings instead of adding new ones
        error_log('MPAI Hooks Tester: Filtering settings fields');
        
        return $fields;
    }
    
    /**
     * Filter settings tabs
     *
     * @param array $tabs The settings tabs
     * @return array Modified settings tabs
     */
    public function filter_settings_tabs($tabs) {
        // We need to be careful not to add settings tabs that will cause errors
        // Let's just log that we're filtering the settings tabs instead of adding new ones
        error_log('MPAI Hooks Tester: Filtering settings tabs');
        
        return $tabs;
    }
    
    /**
     * Action before displaying settings
     */
    public function before_display_settings() {
        echo '<div class="notice notice-info is-dismissible"><p>This notice was added by the MPAI Hooks Tester plugin using the MPAI_HOOK_ACTION_before_display_settings hook.</p></div>';
    }
    
    /**
     * Action after displaying settings
     */
    public function after_display_settings() {
        echo '<div class="mpai-hooks-tester-footer" style="margin-top: 20px; padding: 10px; background: #f8f8f8; border-top: 1px solid #ddd;">';
        echo '<p>This footer was added by the MPAI Hooks Tester plugin using the MPAI_HOOK_ACTION_after_display_settings hook.</p>';
        echo '</div>';
    }
    
    /**
     * Filter chat interface rendering
     *
     * @param string $content The chat interface HTML
     * @param string $position The chat position
     * @param string $welcome_message The welcome message
     * @return string Modified chat interface HTML
     */
    public function filter_chat_interface_render($content, $position, $welcome_message) {
        // Add a custom class to the chat container
        $content = str_replace('class="mpai-chat-container', 'class="mpai-chat-container mpai-hooks-tester-theme', $content);
        
        // Add custom CSS for the theme
        $custom_css = '<style>
            .mpai-hooks-tester-theme .mpai-chat-header {
                background: linear-gradient(135deg, #6e48aa, #9d50bb);
            }
            .mpai-hooks-tester-theme .mpai-chat-submit {
                background-color: #9d50bb;
            }
        </style>';
        
        return $custom_css . $content;
    }
}