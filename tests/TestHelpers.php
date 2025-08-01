<?php
namespace ArtPulse\Admin\Tests;

class Stub
{
    public static array $transients = [];
    public static array $orders = [];
    public static array $charges = [];
    public static array $subs = [];
    public static array $options = [];
    public static int $wc_calls = 0;
    public static int $stripe_charge_calls = 0;
    public static int $stripe_subs_calls = 0;
    public static int $current_time = 0;
    public static string $upload_path = '';
    public static string $password = 'password';

    public static function reset(): void
    {
        self::$transients = [];
        self::$orders = [];
        self::$charges = [];
        self::$subs = [];
        self::$options = [];
        self::$wc_calls = 0;
        self::$stripe_charge_calls = 0;
        self::$stripe_subs_calls = 0;
        self::$current_time = time();
        self::$upload_path = sys_get_temp_dir();
        self::$password = 'password';
    }

    public static function get_orders(array $args): array
    {
        self::$wc_calls++;
        return self::$orders;
    }

    public static function get_option(string $key)
    {
        return self::$options[$key] ?? [];
    }
}

class OrderDate
{
    private int $ts;
    public function __construct(int $ts) { $this->ts = $ts; }
    public function getTimestamp(): int { return $this->ts; }
}

class WC_Order
{
    private int $ts;
    private float $total;
    private string $status;
    public function __construct(int $ts, float $total, string $status)
    {
        $this->ts = $ts;
        $this->total = $total;
        $this->status = $status;
    }
    public function get_date_created(): OrderDate { return new OrderDate($this->ts); }
    public function get_total(): float { return $this->total; }
    public function get_status(): string { return $this->status; }
}


namespace Stripe;
use ArtPulse\Admin\Tests\Stub;

class Charges {
    public function all(array $params = []) {
        Stub::$stripe_charge_calls++;
        return (object)['data' => Stub::$charges];
    }
}

class Subscriptions {
    public function all(array $params = []) {
        Stub::$stripe_subs_calls++;
        return (object)['data' => Stub::$subs];
    }
}

class Sessions {
    public function create(array $params = []) {
        return (object)['id' => 'sess_123', 'url' => 'https://example.com/session'];
    }
}

class Checkout {
    public Sessions $sessions;
    public function __construct() {
        $this->sessions = new Sessions();
    }
}

class PaymentIntents {
    public function create(array $params = []) {
        return (object)['client_secret' => 'pi_secret'];
    }
}

class StripeClient {
    public Charges $charges;
    public Subscriptions $subscriptions;
    public PaymentIntents $paymentIntents;
    public Checkout $checkout;
    public function __construct(string $secret) {
        $this->charges = new Charges();
        $this->subscriptions = new Subscriptions();
        $this->paymentIntents = new PaymentIntents();
        $this->checkout = new Checkout();
    }
}

namespace ArtPulse\Admin;
use ArtPulse\Admin\Tests\Stub;

if (!defined('MINUTE_IN_SECONDS')) {
    define('MINUTE_IN_SECONDS', 60);
}

if (!function_exists(__NAMESPACE__ . '\get_transient')) {
function get_transient(string $key) {
    return Stub::$transients[$key] ?? false;
}
}

if (!function_exists(__NAMESPACE__ . '\set_transient')) {
function set_transient(string $key, $value, int $expire = 0) {
    Stub::$transients[$key] = $value;
    return true;
}
}

if (!function_exists(__NAMESPACE__ . '\apply_filters')) {
function apply_filters(string $tag, $value) {
    return $value;
}
}

if (!function_exists(__NAMESPACE__ . '\get_option')) {
function get_option(string $key, $default = false) {
    return Stub::get_option($key);
}
}

if (!function_exists(__NAMESPACE__ . '\wc_get_orders')) {
function wc_get_orders(array $args = []) {
    return Stub::get_orders($args);
}
}

if (!function_exists(__NAMESPACE__ . '\current_time')) {
function current_time(string $type = 'timestamp') {
    return Stub::$current_time;
}
}

if (!function_exists(__NAMESPACE__ . '\date_i18n')) {
function date_i18n(string $format, int $timestamp) {
    return date($format, $timestamp);
}
}

namespace ArtPulse\Core;
use ArtPulse\Admin\Tests\Stub;

if (!function_exists(__NAMESPACE__ . '\wp_upload_dir')) {
function wp_upload_dir(): array {
    return ['path' => Stub::$upload_path, 'basedir' => Stub::$upload_path];
}
}

if (!function_exists(__NAMESPACE__ . '\wp_generate_password')) {
function wp_generate_password(int $length = 12, bool $special_chars = false): string {
    return Stub::$password;
}
}


namespace ArtPulse\Admin;
if (!function_exists('ArtPulse\\Admin\\__')) {
    function __($text, $domain = null) { return $text; }
}
if (!function_exists('ArtPulse\\Admin\\_e')) {
    function _e($text, $domain = null) { echo $text; }
}
if (!function_exists('ArtPulse\\Admin\\esc_html__')) {
    function esc_html__($text, $domain = null) { return $text; }
}
if (!function_exists('ArtPulse\\Admin\\esc_html_e')) {
    function esc_html_e($text, $domain = null) { echo $text; }
}

namespace ArtPulse\Frontend;
if (!function_exists('ArtPulse\\Frontend\\__')) {
    function __($text, $domain = null) { return $text; }
}
if (!function_exists('ArtPulse\\Frontend\\_e')) {
    function _e($text, $domain = null) { echo $text; }
}
if (!function_exists('ArtPulse\\Frontend\\esc_html__')) {
    function esc_html__($text, $domain = null) { return $text; }
}
if (!function_exists('ArtPulse\\Frontend\\esc_html_e')) {
    function esc_html_e($text, $domain = null) { echo $text; }
}

namespace ArtPulse\Core;
if (!function_exists('ArtPulse\\Core\\__')) {
    function __($text, $domain = null) { return $text; }
}
if (!function_exists('ArtPulse\\Core\\_e')) {
    function _e($text, $domain = null) { echo $text; }
}
if (!function_exists('ArtPulse\\Core\\esc_html__')) {
    function esc_html__($text, $domain = null) { return $text; }
}
if (!function_exists('ArtPulse\\Core\\esc_html_e')) {
    function esc_html_e($text, $domain = null) { echo $text; }
}

namespace ArtPulse\Widgets\Placeholder;
if (!function_exists('ArtPulse\\Widgets\\Placeholder\\esc_html__')) {
    function esc_html__($text, $domain = null) { return $text; }
}
if (!function_exists('ArtPulse\\Widgets\\Placeholder\\esc_html_e')) {
    function esc_html_e($text, $domain = null) { echo $text; }
}
