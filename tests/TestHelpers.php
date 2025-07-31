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

function get_transient(string $key) {
    return Stub::$transients[$key] ?? false;
}

function set_transient(string $key, $value, int $expire = 0) {
    Stub::$transients[$key] = $value;
    return true;
}

function apply_filters(string $tag, $value) {
    return $value;
}

function get_option(string $key, $default = false) {
    return Stub::get_option($key);
}

function wc_get_orders(array $args = []) {
    return Stub::get_orders($args);
}

function current_time(string $type = 'timestamp') {
    return Stub::$current_time;
}

function date_i18n(string $format, int $timestamp) {
    return date($format, $timestamp);
}

namespace ArtPulse\Core;
use ArtPulse\Admin\Tests\Stub;

function wp_upload_dir(): array {
    return ['path' => Stub::$upload_path, 'basedir' => Stub::$upload_path];
}

function wp_generate_password(int $length = 12, bool $special_chars = false): string {
    return Stub::$password;
}

