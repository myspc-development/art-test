<?php
namespace ArtPulse\Admin\Tests;

class Stub
{
    public static array $transients = [];
    public static array $orders = [];
    public static array $charges = [];
    public static array $subs = [];
    public static array $created_orders = [];
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
        self::$created_orders = [];
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

class WC_Order_Item
{
    public int $product_id;
    public int $qty;
    public function __construct(int $product_id, int $qty)
    {
        $this->product_id = $product_id;
        $this->qty        = $qty;
    }
    public function get_product_id(): int { return $this->product_id; }
    public function get_quantity(): int { return $this->qty; }
}

class WC_Order
{
    private int $ts;
    private float $total;
    private string $status;
    private int $id;
    private int $user_id = 0;
    private array $items = [];
    public function __construct(int $ts = 0, float $total = 0.0, string $status = '')
    {
        $this->ts     = $ts;
        $this->total  = $total;
        $this->status = $status;
        $this->id     = count(Stub::$created_orders) + 1;
    }
    public function get_id(): int { return $this->id; }
    public function get_date_created(): OrderDate { return new OrderDate($this->ts); }
    public function get_total(): float { return $this->total; }
    public function get_status(): string { return $this->status; }
    public function get_user_id(): int { return $this->user_id; }
    public function add_product($product, int $qty): void { $this->items[] = new WC_Order_Item($product->get_id(), $qty); }
    public function calculate_totals(): void {}
    public function save(): void { Stub::$created_orders[$this->id] = $this; }
    public function get_items(): array { return $this->items; }
    public function set_customer_id(int $id): void { $this->user_id = $id; }
}

class WC_Product_Simple
{
    private int $id;
    public function __construct(int $id) { $this->id = $id; }
    public function get_id(): int { return $this->id; }
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

class StripeClient {
    public Charges $charges;
    public Subscriptions $subscriptions;
    public function __construct(string $secret) {
        $this->charges = new Charges();
        $this->subscriptions = new Subscriptions();
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

namespace ArtPulse\Monetization;
use ArtPulse\Admin\Tests\Stub;
use ArtPulse\Admin\WC_Order;
use ArtPulse\Admin\WC_Product_Simple;

function wc_create_order(array $args = []) {
    $order = new WC_Order();
    if (isset($args['customer_id'])) {
        $order->set_customer_id($args['customer_id']);
    }
    $order->save();
    return $order;
}

function wc_get_order($order_id) {
    return Stub::$created_orders[$order_id] ?? null;
}

function wc_get_product($product_id) {
    return new WC_Product_Simple($product_id);
}

namespace ArtPulse\Core;
use ArtPulse\Admin\Tests\Stub;

function wp_upload_dir(): array {
    return ['path' => Stub::$upload_path, 'basedir' => Stub::$upload_path];
}

function wp_generate_password(int $length = 12, bool $special_chars = false): string {
    return Stub::$password;
}

