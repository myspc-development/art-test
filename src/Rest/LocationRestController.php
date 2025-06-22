<?php
namespace ArtPulse\Rest;

use WP_Error;
use WP_REST_Request;
use WP_REST_Response;

class LocationRestController {
    public static function register(): void {
        add_action('rest_api_init', function () {
            register_rest_route('artpulse/v1', '/location/geonames', [
                'methods'             => 'GET',
                'callback'            => [self::class, 'geonames'],
                'permission_callback' => '__return_true',
            ]);
            register_rest_route('artpulse/v1', '/location/google', [
                'methods'             => 'GET',
                'callback'            => [self::class, 'google'],
                'permission_callback' => '__return_true',
            ]);
        });
    }

    public static function geonames(WP_REST_Request $req): WP_REST_Response|WP_Error {
        $opts = get_option('artpulse_settings', []);
        $user = $opts['geonames_username'] ?? '';
        if (!$user) {
            return new WP_Error('missing_key', 'Geonames username not set.', ['status' => 400]);
        }
        $type = sanitize_key($req->get_param('type'));
        $country = sanitize_text_field($req->get_param('country'));
        $state = sanitize_text_field($req->get_param('state'));

        if ($type === 'states' && $country) {
            $url = "http://api.geonames.org/searchJSON?featureCode=ADM1&country={$country}&maxRows=1000&username={$user}";
            $resp = wp_remote_get($url);
            if (is_wp_error($resp)) return $resp;
            $data = json_decode(wp_remote_retrieve_body($resp), true);
            $states = [];
            foreach ($data['geonames'] ?? [] as $s) {
                $states[] = [
                    'code' => $s['adminCode1'] ?? '',
                    'name' => $s['name'] ?? '',
                    'country' => $country,
                ];
            }
            self::merge_into_dataset('states', $states);
            return rest_ensure_response($states);
        }

        if ($type === 'cities' && $country && $state) {
            $url = "http://api.geonames.org/searchJSON?featureClass=P&country={$country}&adminCode1={$state}&maxRows=1000&username={$user}";
            $resp = wp_remote_get($url);
            if (is_wp_error($resp)) return $resp;
            $data = json_decode(wp_remote_retrieve_body($resp), true);
            $cities = [];
            foreach ($data['geonames'] ?? [] as $c) {
                $cities[] = [
                    'name' => $c['name'] ?? '',
                    'state' => $state,
                    'country' => $country,
                ];
            }
            self::merge_into_dataset('cities', $cities);
            return rest_ensure_response($cities);
        }
        return new WP_Error('invalid_params', 'Invalid parameters', ['status'=>400]);
    }

    public static function google(WP_REST_Request $req): WP_REST_Response|WP_Error {
        $opts = get_option('artpulse_settings', []);
        $key = $opts['google_places_key'] ?? '';
        if (!$key) {
            return new WP_Error('missing_key', 'Google Places key not set.', ['status' => 400]);
        }
        $query = urlencode($req->get_param('query'));
        $url = "https://maps.googleapis.com/maps/api/place/autocomplete/json?input={$query}&key={$key}";
        $resp = wp_remote_get($url);
        if (is_wp_error($resp)) return $resp;
        $data = json_decode(wp_remote_retrieve_body($resp), true);
        return rest_ensure_response($data['predictions'] ?? []);
    }

    private static function merge_into_dataset(string $key, array $items): void {
        switch ($key) {
            case 'countries':
                $file = ARTPULSE_PLUGIN_DIR . '/data/countries.json';
                break;
            case 'states':
                $file = ARTPULSE_PLUGIN_DIR . '/data/states.json';
                break;
            case 'cities':
                $file = ARTPULSE_PLUGIN_DIR . '/data/cities.json';
                break;
            default:
                return;
        }

        if (!file_exists($file)) {
            return;
        }

        $current = json_decode(file_get_contents($file), true);
        if (!is_array($current)) {
            $current = [];
        }

        foreach ($items as $item) {
            $exists = false;
            foreach ($current as $existing) {
                if (
                    $key === 'countries' && isset($existing['code']) && $existing['code'] === $item['code']
                ) {
                    $exists = true;
                    break;
                }
                if (
                    $key === 'states' &&
                    isset($existing['code'], $existing['country']) &&
                    $existing['code'] === $item['code'] &&
                    $existing['country'] === $item['country']
                ) {
                    $exists = true;
                    break;
                }
                if (
                    $key === 'cities' &&
                    isset($existing['name'], $existing['state'], $existing['country']) &&
                    $existing['name'] === $item['name'] &&
                    $existing['state'] === $item['state'] &&
                    $existing['country'] === $item['country']
                ) {
                    $exists = true;
                    break;
                }
            }

            if (!$exists) {
                $current[] = $item;
            }
        }

        file_put_contents($file, json_encode($current, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }
}
