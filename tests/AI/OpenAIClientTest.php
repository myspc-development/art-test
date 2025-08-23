<?php

namespace {
    if (!class_exists('WP_Error')) {
        class WP_Error
        {
            private string $code;
            private string $message;
            public function __construct($code = '', $message = '')
            {
                $this->code    = (string) $code;
                $this->message = (string) $message;
            }
            public function get_error_code(): string
            {
                return $this->code;
            }
            public function get_error_message(): string
            {
                return $this->message;
            }
        }
    }
}

namespace ArtPulse\AI {

class OpenAIClientTest_Stubs
{
    public static $response;
    public static $lastRequest;
    public static $options = [];
}

if (!function_exists(__NAMESPACE__ . '\\wp_remote_post')) {
    function wp_remote_post($url, $args)
    {
        OpenAIClientTest_Stubs::$lastRequest = ['url' => $url, 'args' => $args];
        return OpenAIClientTest_Stubs::$response;
    }
}
if (!function_exists(__NAMESPACE__ . '\\wp_remote_retrieve_response_code')) {
    function wp_remote_retrieve_response_code($response)
    {
        if ($response instanceof \WP_Error) {
            return 0;
        }
        return $response['code'] ?? 0;
    }
}
if (!function_exists(__NAMESPACE__ . '\\wp_remote_retrieve_body')) {
    function wp_remote_retrieve_body($response)
    {
        if ($response instanceof \WP_Error) {
            return '';
        }
        return $response['body'] ?? '';
    }
}
if (!function_exists(__NAMESPACE__ . '\\get_option')) {
    function get_option($key, $default = false)
    {
        return OpenAIClientTest_Stubs::$options[$key] ?? $default;
    }
}
if (!function_exists(__NAMESPACE__ . '\\wp_upload_dir')) {
    function wp_upload_dir()
    {
        return ['basedir' => sys_get_temp_dir()];
    }
}
if (!function_exists(__NAMESPACE__ . '\\trailingslashit')) {
    function trailingslashit($path)
    {
        return rtrim($path, '/\\') . '/';
    }
}
if (!function_exists(__NAMESPACE__ . '\\__')) {
    function __($text, $domain = null)
    {
        return $text;
    }
}
if (!function_exists(__NAMESPACE__ . '\\is_wp_error')) {
    function is_wp_error($thing)
    {
        return $thing instanceof \WP_Error;
    }
}
if (!function_exists(__NAMESPACE__ . '\\wp_json_encode')) {
    function wp_json_encode($value)
    {
        return json_encode($value);
    }
}

use WP_Error;
use PHPUnit\Framework\TestCase;

class OpenAIClientTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        OpenAIClientTest_Stubs::$response = null;
        OpenAIClientTest_Stubs::$lastRequest = null;
        OpenAIClientTest_Stubs::$options = [];
    }

    public function test_generateTags_and_generateSummary_success(): void
    {
        OpenAIClientTest_Stubs::$options['openai_api_key'] = 'abc123';

        OpenAIClientTest_Stubs::$response = [
            'code' => 200,
            'body' => json_encode([
                'choices' => [
                    ['message' => ['content' => 'alpha, beta']],
                ],
            ]),
        ];
        $tags = OpenAIClient::generateTags('text');
        $this->assertSame(['alpha', 'beta'], $tags);

        OpenAIClientTest_Stubs::$response = [
            'code' => 200,
            'body' => json_encode([
                'choices' => [
                    ['message' => ['content' => 'short summary']],
                ],
            ]),
        ];
        $summary = OpenAIClient::generateSummary('bio');
        $this->assertSame('short summary', $summary);
    }

    public function test_generateTags_surfaces_wp_error_on_network_failure(): void
    {
        OpenAIClientTest_Stubs::$options['openai_api_key'] = 'abc123';
        OpenAIClientTest_Stubs::$response = new WP_Error('http_request_failed', 'Network down');
        $res = OpenAIClient::generateTags('text');
        $this->assertInstanceOf(WP_Error::class, $res);
        $this->assertSame('openai_request_failed', $res->get_error_code());
    }

    public function test_generateSummary_handles_rate_limit_and_http_error(): void
    {
        OpenAIClientTest_Stubs::$options['openai_api_key'] = 'abc123';

        OpenAIClientTest_Stubs::$response = ['code' => 429, 'body' => ''];
        $res = OpenAIClient::generateSummary('bio');
        $this->assertInstanceOf(WP_Error::class, $res);
        $this->assertSame('openai_rate_limited', $res->get_error_code());

        OpenAIClientTest_Stubs::$response = ['code' => 400, 'body' => ''];
        $res = OpenAIClient::generateSummary('bio');
        $this->assertInstanceOf(WP_Error::class, $res);
        $this->assertSame('openai_http_error', $res->get_error_code());
    }

    public function test_api_key_falls_back_to_constant(): void
    {
        OpenAIClientTest_Stubs::$options = [];
        if (!defined('OPENAI_API_KEY')) {
            define('OPENAI_API_KEY', 'const-key');
        }
        OpenAIClientTest_Stubs::$response = [
            'code' => 200,
            'body' => json_encode(['choices' => [['message' => ['content' => 'ok']]]]),
        ];
        OpenAIClient::generateSummary('bio');
        $this->assertSame(
            'Bearer const-key',
            OpenAIClientTest_Stubs::$lastRequest['args']['headers']['Authorization']
        );
    }
}

}
