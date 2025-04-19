# MemberPress AI Assistant - Hooks Tester

This plugin demonstrates how to use the hooks and filters in MemberPress AI Assistant. It provides a testing framework for verifying that hooks are firing correctly and allows developers to see how to implement their own extensions.

## Features

- Tests all hooks and filters in MemberPress AI Assistant
- Logs hook executions to a log file
- Provides a UI for viewing the log and manually triggering hooks
- Demonstrates how to implement content, UI, API, and error handling hooks

## Content and UI Hooks Implementation

The plugin includes a dedicated class (`MPAI_Content_Hooks_Tester`) that demonstrates how to use the content and UI hooks introduced in Phase 3 of the MemberPress AI Assistant hooks implementation plan.

### Content Generation Hooks

- `MPAI_HOOK_FILTER_generated_content`: Adds a disclaimer to blog posts
- `MPAI_HOOK_FILTER_content_formatting`: Adds a custom formatting rule for callouts
- `MPAI_HOOK_FILTER_blog_post_content`: Adds a category and prefix to blog post titles
- `MPAI_HOOK_FILTER_content_type`: Detects custom content types based on content patterns
- `MPAI_HOOK_FILTER_content_marker`: Adds a new marker for subtitle

### Admin Interface Hooks

- `MPAI_HOOK_FILTER_admin_menu_items`: Adds a new submenu item for the hooks tester
- `MPAI_HOOK_FILTER_settings_fields`: Adds a custom setting
- `MPAI_HOOK_FILTER_settings_tabs`: Adds a custom settings tab
- `MPAI_HOOK_ACTION_before_display_settings`: Adds a notice at the top of the settings page
- `MPAI_HOOK_ACTION_after_display_settings`: Adds a footer to the settings page
- `MPAI_HOOK_FILTER_chat_interface_render`: Customizes the chat interface with a custom theme

## API Integration and Error Handling Hooks

The plugin includes a dedicated class (`MPAI_API_Hooks_Tester`) that demonstrates how to use the API integration and error handling hooks introduced in Phase 4 of the MemberPress AI Assistant hooks implementation plan.

### API Integration Hooks

- `MPAI_HOOK_ACTION_before_api_request`: Logs API requests before they are sent
- `MPAI_HOOK_ACTION_after_api_request`: Logs API responses and tracks usage metrics
- `MPAI_HOOK_FILTER_api_request_params`: Adjusts temperature based on API provider
- `MPAI_HOOK_FILTER_api_response`: Adds a watermark to responses
- `MPAI_HOOK_FILTER_api_provider`: Selects specific providers for different content types
- `MPAI_HOOK_FILTER_cache_ttl`: Customizes cache TTL based on request type

### Error Handling Hooks

- `MPAI_HOOK_FILTER_api_error_handling`: Customizes error handling based on API provider
- `MPAI_HOOK_ACTION_before_error_recovery`: Logs error recovery attempts
- `MPAI_HOOK_ACTION_after_error_recovery`: Logs error recovery results
- `MPAI_HOOK_FILTER_error_message`: Makes error messages more user-friendly
- `MPAI_HOOK_FILTER_error_should_retry`: Determines if certain errors should be retried

### Logging Hooks

- `MPAI_HOOK_FILTER_log_entry`: Adds additional context to log entries
- `MPAI_HOOK_FILTER_should_log`: Filters out certain types of log messages
- `MPAI_HOOK_FILTER_log_level`: Upgrades certain warnings to errors
- `MPAI_HOOK_FILTER_log_retention`: Sets log retention to 60 days
- `MPAI_HOOK_FILTER_sanitize_log_data`: Redacts sensitive data in logs

## Usage

1. Install and activate the plugin
2. Go to Tools > MPAI Hooks Tester to view the log of hook executions
3. Use the buttons on the page to manually trigger specific hooks
4. Interact with MemberPress AI Assistant to trigger hooks automatically
5. Check the log to see which hooks were fired and what data was passed

## Implementation Details

### Content Hooks Tester

The `MPAI_Content_Hooks_Tester` class demonstrates how to:

1. Register hooks and filters for content generation and admin interface
2. Modify generated content before use
3. Add custom formatting rules
4. Customize blog post content
5. Detect custom content types
6. Add custom settings and tabs
7. Customize the chat interface

### API Hooks Tester

The `MPAI_API_Hooks_Tester` class demonstrates how to:

1. Register hooks and filters for API integration and error handling
2. Log API requests and responses
3. Track API usage metrics
4. Customize API provider selection
5. Enhance error handling and recovery
6. Sanitize sensitive data in logs

### Example: Adding a Custom Formatting Rule

```php
public function filter_content_formatting($rules) {
    // Add a custom formatting rule for callouts
    $rules['callout'] = [
        'tag' => 'div',
        'wrapper' => '<!-- wp:custom-block {"className":"mpai-callout"} --><div class="mpai-callout">%s</div><!-- /wp:custom-block -->'
    ];
    
    return $rules;
}
```

### Example: Customizing the Chat Interface

```php
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
```

### Example: Tracking API Usage Metrics

```php
public function after_api_request($api_name, $request_params, $response, $duration) {
    // Log API response
    $is_error = is_wp_error($response);
    $log_method = $is_error ? 'mpai_log_warning' : 'mpai_log_debug';
    
    $log_method("MPAI Hooks Tester: After API request to {$api_name} (took {$duration}s)", 'api-hooks-tester', [
        'success' => !$is_error,
        'error' => $is_error ? $response->get_error_message() : null
    ]);
    
    // Track API usage metrics
    $this->track_api_usage($api_name, $duration, !$is_error);
}
```

### Example: Customizing Error Messages

```php
public function filter_error_message($message, $error, $context) {
    // Make error messages more user-friendly
    if (strpos($message, 'API key') !== false) {
        return 'The AI service is currently unavailable. Please try again later or contact support.';
    } elseif (strpos($message, 'rate limit') !== false) {
        return 'The AI service is experiencing high demand. Please try again in a few minutes.';
    }
    
    return $message;
}
```

## Development

This plugin is intended as a reference implementation for developers who want to extend MemberPress AI Assistant. Feel free to use it as a starting point for your own extensions.

## License

GPL v2 or later