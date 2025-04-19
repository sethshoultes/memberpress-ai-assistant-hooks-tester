<?php
/**
 * API and Error Hooks Tester Class
 *
 * Demonstrates how to use the API Integration and Error Handling hooks in MemberPress AI Assistant
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

class MPAI_API_Hooks_Tester {
    /**
     * Constructor
     */
    public function __construct() {
        // Register API Integration hooks
        add_action('MPAI_HOOK_ACTION_before_api_request', array($this, 'before_api_request'), 10, 3);
        add_action('MPAI_HOOK_ACTION_after_api_request', array($this, 'after_api_request'), 10, 4);
        add_filter('MPAI_HOOK_FILTER_api_request_params', array($this, 'filter_api_request_params'), 10, 2);
        add_filter('MPAI_HOOK_FILTER_api_response', array($this, 'filter_api_response'), 10, 3);
        add_filter('MPAI_HOOK_FILTER_api_provider', array($this, 'filter_api_provider'), 10, 2);
        add_filter('MPAI_HOOK_FILTER_cache_ttl', array($this, 'filter_cache_ttl'), 10, 3);
        
        // Register Error Handling hooks
        add_filter('MPAI_HOOK_FILTER_api_error_handling', array($this, 'filter_api_error_handling'), 10, 3);
        add_action('MPAI_HOOK_ACTION_before_error_recovery', array($this, 'before_error_recovery'), 10, 3);
        add_action('MPAI_HOOK_ACTION_after_error_recovery', array($this, 'after_error_recovery'), 10, 4);
        add_filter('MPAI_HOOK_FILTER_error_message', array($this, 'filter_error_message'), 10, 3);
        add_filter('MPAI_HOOK_FILTER_error_should_retry', array($this, 'filter_error_should_retry'), 10, 4);
        
        // Register Logging hooks
        add_filter('MPAI_HOOK_FILTER_log_entry', array($this, 'filter_log_entry'), 10, 3);
        add_filter('MPAI_HOOK_FILTER_should_log', array($this, 'filter_should_log'), 10, 4);
        add_filter('MPAI_HOOK_FILTER_log_level', array($this, 'filter_log_level'), 10, 3);
        add_filter('MPAI_HOOK_FILTER_log_retention', array($this, 'filter_log_retention'), 10, 1);
        add_filter('MPAI_HOOK_FILTER_sanitize_log_data', array($this, 'filter_sanitize_log_data'), 10, 3);
    }
    
    /**
     * Action before API request
     *
     * @param string $api_name The name of the API provider
     * @param array $request_params The request parameters
     * @param array $context Additional context for the request
     */
    public function before_api_request($api_name, $request_params, $context) {
        // Log API request
        mpai_log_debug("MPAI Hooks Tester: Before API request to {$api_name}", 'api-hooks-tester', [
            'request_type' => isset($context['request_type']) ? $context['request_type'] : 'unknown',
            'message_count' => isset($context['message_count']) ? $context['message_count'] : 0
        ]);
    }
    
    /**
     * Action after API request
     *
     * @param string $api_name The name of the API provider
     * @param array $request_params The request parameters
     * @param mixed $response The API response
     * @param float $duration The request duration in seconds
     */
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
    
    /**
     * Filter API request parameters
     *
     * @param array $params The request parameters
     * @param string $api_name The name of the API provider
     * @return array Modified parameters
     */
    public function filter_api_request_params($params, $api_name) {
        // Example: Adjust temperature based on API provider
        if ($api_name === 'openai' && isset($params['temperature'])) {
            // Make OpenAI slightly more creative
            $params['temperature'] = min(1.0, $params['temperature'] * 1.1);
        } elseif ($api_name === 'anthropic' && isset($params['temperature'])) {
            // Make Anthropic slightly more precise
            $params['temperature'] = max(0.1, $params['temperature'] * 0.9);
        }
        
        return $params;
    }
    
    /**
     * Filter API response
     *
     * @param mixed $response The API response
     * @param string $api_name The name of the API provider
     * @param array $request_params The request parameters
     * @return mixed Modified response
     */
    public function filter_api_response($response, $api_name, $request_params) {
        // Don't modify error responses
        if (is_wp_error($response)) {
            return $response;
        }
        
        // Example: Add a watermark to responses
        if ($api_name === 'openai' && isset($response['choices'][0]['message']['content'])) {
            // Add a subtle watermark to OpenAI responses
            $content = $response['choices'][0]['message']['content'];
            if (strpos($content, 'MPAI Hooks Tester') === false) {
                $response['choices'][0]['message']['content'] = $content . "\n\n<!-- Enhanced by MPAI Hooks Tester -->";
            }
        } elseif ($api_name === 'anthropic' && isset($response['content'][0]['text'])) {
            // Add a subtle watermark to Anthropic responses
            $content = $response['content'][0]['text'];
            if (strpos($content, 'MPAI Hooks Tester') === false) {
                $response['content'][0]['text'] = $content . "\n\n<!-- Enhanced by MPAI Hooks Tester -->";
            }
        }
        
        return $response;
    }
    
    /**
     * Filter API provider
     *
     * @param string $provider The API provider to use
     * @param array $context Additional context for the request
     * @return string Modified provider
     */
    public function filter_api_provider($provider, $context) {
        // Example: Use specific providers for different types of content
        if (isset($context['request_type'])) {
            if ($context['request_type'] === 'content_creation') {
                // Use Anthropic for content creation
                return 'anthropic';
            } elseif ($context['request_type'] === 'code_generation') {
                // Use OpenAI for code generation
                return 'openai';
            }
        }
        
        return $provider;
    }
    
    /**
     * Filter cache TTL
     *
     * @param int $ttl The cache TTL in seconds
     * @param string $request_type The type of request
     * @param string $api_name The name of the API provider
     * @return int Modified TTL
     */
    public function filter_cache_ttl($ttl, $request_type, $api_name) {
        // Example: Customize cache TTL based on request type
        if ($request_type === 'chat_completion') {
            // Cache chat completions for 30 minutes
            return 1800;
        } elseif ($request_type === 'content_creation') {
            // Don't cache content creation requests
            return 0;
        }
        
        return $ttl;
    }
    
    /**
     * Filter API error handling
     *
     * @param array $handling The error handling configuration
     * @param WP_Error $error The error object
     * @param string $api_name The name of the API provider
     * @return array Modified handling
     */
    public function filter_api_error_handling($handling, $error, $api_name) {
        // Example: Customize error handling based on API provider
        if ($api_name === 'openai') {
            // Increase max retries for OpenAI
            $handling['max_retries'] = 3;
            $handling['retry_delay'] = 2; // 2 seconds
        } elseif ($api_name === 'anthropic') {
            // Increase max retries for Anthropic
            $handling['max_retries'] = 2;
            $handling['retry_delay'] = 1; // 1 second
        }
        
        return $handling;
    }
    
    /**
     * Action before error recovery
     *
     * @param WP_Error $error The error object
     * @param string $component The component that failed
     * @param array $recovery_strategy The recovery strategy
     */
    public function before_error_recovery($error, $component, $recovery_strategy) {
        // Log error recovery attempt
        mpai_log_info("MPAI Hooks Tester: Attempting to recover from error in {$component}", 'api-hooks-tester', [
            'error_code' => $error->get_error_code(),
            'error_message' => $error->get_error_message(),
            'max_retries' => isset($recovery_strategy['max_retries']) ? $recovery_strategy['max_retries'] : 0
        ]);
    }
    
    /**
     * Action after error recovery
     *
     * @param WP_Error $error The original error object
     * @param string $component The component that failed
     * @param mixed $recovery_result The result of recovery attempt
     * @param bool $success Whether recovery was successful
     */
    public function after_error_recovery($error, $component, $recovery_result, $success) {
        // Log error recovery result
        $log_method = $success ? 'mpai_log_info' : 'mpai_log_warning';
        
        $log_method("MPAI Hooks Tester: Error recovery for {$component} " . ($success ? 'succeeded' : 'failed'), 'api-hooks-tester', [
            'original_error' => $error->get_error_message(),
            'success' => $success
        ]);
    }
    
    /**
     * Filter error message
     *
     * @param string $message The error message
     * @param WP_Error $error The error object
     * @param array $context Additional context
     * @return string Modified message
     */
    public function filter_error_message($message, $error, $context) {
        // Example: Make error messages more user-friendly
        if (strpos($message, 'API key') !== false) {
            return 'The AI service is currently unavailable. Please try again later or contact support.';
        } elseif (strpos($message, 'rate limit') !== false) {
            return 'The AI service is experiencing high demand. Please try again in a few minutes.';
        } elseif (strpos($message, 'timeout') !== false) {
            return 'The AI service is taking longer than expected to respond. Please try again.';
        }
        
        return $message;
    }
    
    /**
     * Filter whether an error should trigger a retry
     *
     * @param bool $should_retry Whether to retry
     * @param WP_Error $error The error object
     * @param int $retry_count The current retry count
     * @param int $max_retries The maximum number of retries
     * @return bool Modified decision
     */
    public function filter_error_should_retry($should_retry, $error, $retry_count, $max_retries) {
        // Example: Don't retry certain types of errors
        $error_data = $error->get_error_data();
        
        // Don't retry if it's a rate limit error and we've already tried once
        if (isset($error_data['status']) && $error_data['status'] === 'rate_limit_exceeded' && $retry_count > 1) {
            return false;
        }
        
        // Don't retry if it's an authentication error
        if (strpos($error->get_error_message(), 'authentication') !== false) {
            return false;
        }
        
        return $should_retry;
    }
    
    /**
     * Filter log entry before writing
     *
     * @param array $entry The log entry
     * @param string $level The log level
     * @param string $component The component generating the log
     * @return array Modified entry
     */
    public function filter_log_entry($entry, $level, $component) {
        // Example: Add additional context to log entries
        $entry['user_id'] = get_current_user_id();
        $entry['plugin'] = 'mpai-hooks-tester';
        
        return $entry;
    }
    
    /**
     * Filter whether to log a specific event
     *
     * @param bool $should_log Whether to log the event
     * @param string $level The log level
     * @param string $message The log message
     * @param string $component The component generating the log
     * @return bool Modified decision
     */
    public function filter_should_log($should_log, $level, $message, $component) {
        // Example: Don't log certain types of messages
        if ($level === 'debug' && strpos($message, 'cache') !== false) {
            // Don't log cache-related debug messages
            return false;
        }
        
        return $should_log;
    }
    
    /**
     * Filter log level for a specific event
     *
     * @param string $level The log level
     * @param string $message The log message
     * @param string $component The component generating the log
     * @return string Modified level
     */
    public function filter_log_level($level, $message, $component) {
        // Example: Upgrade certain warnings to errors
        if ($level === 'warning' && strpos($message, 'API failure') !== false) {
            return 'error';
        }
        
        return $level;
    }
    
    /**
     * Filter log retention period
     *
     * @param int $days Number of days to retain logs
     * @return int Modified period
     */
    public function filter_log_retention($days) {
        // Example: Keep logs for 60 days
        return 60;
    }
    
    /**
     * Filter to sanitize sensitive data in logs
     *
     * @param array $data The data to sanitize
     * @param string $level The log level
     * @param string $component The component generating the log
     * @return array Sanitized data
     */
    public function filter_sanitize_log_data($data, $level, $component) {
        // Example: Sanitize sensitive data
        if (isset($data['api_key'])) {
            $data['api_key'] = '***REDACTED***';
        }
        
        if (isset($data['auth_token'])) {
            $data['auth_token'] = '***REDACTED***';
        }
        
        if (isset($data['headers']) && isset($data['headers']['Authorization'])) {
            $data['headers']['Authorization'] = '***REDACTED***';
        }
        
        return $data;
    }
    
    /**
     * Track API usage metrics
     *
     * @param string $api_name The name of the API provider
     * @param float $duration The request duration in seconds
     * @param bool $success Whether the request was successful
     */
    private function track_api_usage($api_name, $duration, $success) {
        // Get existing metrics
        $metrics = get_option('mpai_api_usage_metrics', []);
        
        // Initialize if not exists
        if (!isset($metrics[$api_name])) {
            $metrics[$api_name] = [
                'total_requests' => 0,
                'successful_requests' => 0,
                'failed_requests' => 0,
                'total_duration' => 0,
                'average_duration' => 0
            ];
        }
        
        // Update metrics
        $metrics[$api_name]['total_requests']++;
        if ($success) {
            $metrics[$api_name]['successful_requests']++;
        } else {
            $metrics[$api_name]['failed_requests']++;
        }
        
        $metrics[$api_name]['total_duration'] += $duration;
        $metrics[$api_name]['average_duration'] = 
            $metrics[$api_name]['total_duration'] / $metrics[$api_name]['total_requests'];
        
        // Save updated metrics
        update_option('mpai_api_usage_metrics', $metrics);
    }
}