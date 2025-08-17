<?php
declare(strict_types=1);

namespace ArtPulse\Rest;

use WP_REST_Controller;
use WP_REST_Server;
use WP_REST_Request;
use WP_REST_Response;
use WP_Error;

class EventChatController extends WP_REST_Controller
{
    /** @var string */
    protected $namespace;

    public function __construct()
    {
        // Safe fallback if constant isn't defined yet.
        $this->namespace = \defined('ARTPULSE_API_NAMESPACE') ? \ARTPULSE_API_NAMESPACE : 'artpulse/v1';
    }

    public static function register(): void
    {
        $controller = new self();
        add_action('rest_api_init', [$controller, 'register_routes']);
    }

    public function register_routes(): void
    {
        // Ensure DB helpers are available (correct path: plugin root, not /src).
        if (!\function_exists('\\ArtPulse\\DB\\Chat\\maybe_install_tables')) {
            // Requires that ARTPULSE_PLUGIN_FILE points to the plugin bootstrap file.
            $db_file = \plugin_dir_path(\defined('ARTPULSE_PLUGIN_FILE') ? \ARTPULSE_PLUGIN_FILE : __FILE__) . 'includes/chat-db.php';
            if (is_readable($db_file)) {
                require_once $db_file;
            }
        }

        register_rest_route($this->namespace, '/event/(?P<id>\d+)/chat', [
            [
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => [$this, 'get_messages'],
                'permission_callback' => [$this, 'can_read'],
                'args'                => [
                    'id' => [
                        'validate_callback' => static fn($v) => is_numeric($v) && (int)$v > 0,
                    ],
                ],
            ],
            [
                'methods'             => WP_REST_Server::CREATABLE,
                'callback'            => [$this, 'create_item'],
                'permission_callback' => [$this, 'can_write'],
                'args'                => [
                    'id'      => ['validate_callback' => static fn($v) => is_numeric($v) && (int)$v > 0],
                    'content' => ['type' => 'string', 'required' => true],
                ],
            ],
        ]);

        register_rest_route($this->namespace, '/chat/(?P<id>\d+)/reaction', [
            'methods'             => WP_REST_Server::CREATABLE,
            'callback'            => [$this, 'add_reaction'],
            'permission_callback' => [$this, 'can_write'],
            'args'                => [
                'id'    => ['validate_callback' => static fn($v) => is_numeric($v) && (int)$v > 0],
                'emoji' => ['type' => 'string', 'required' => true],
            ],
        ]);

        register_rest_route($this->namespace, '/chat/(?P<id>\d+)', [
            'methods'             => WP_REST_Server::DELETABLE,
            'callback'            => [$this, 'delete_item'],
            'permission_callback' => [$this, 'moderator_check'],
            'args'                => [
                'id' => ['validate_callback' => static fn($v) => is_numeric($v) && (int)$v > 0],
            ],
        ]);

        register_rest_route($this->namespace, '/chat/(?P<id>\d+)/flag', [
            'methods'             => WP_REST_Server::EDITABLE,
            'callback'            => [$this, 'flag_item'],
            'permission_callback' => [$this, 'moderator_check'],
            'args'                => [
                'id' => ['validate_callback' => static fn($v) => is_numeric($v) && (int)$v > 0],
            ],
        ]);
    }

    /** Read permission for chat (logged-in + event exists) */
    public function can_read(WP_REST_Request $request)
    {
        if (!is_user_logged_in()) {
            return new WP_Error('rest_forbidden', __('Authentication required.', 'artpulse'), ['status' => 401]);
        }
        $event_id = (int) $request['id'];
        $post     = get_post($event_id);
        if (!$post || $post->post_type !== 'artpulse_event') {
            return new WP_Error('ap_event_not_found', __('Event not found.', 'artpulse'), ['status' => 404]);
        }
        // Adjust according to your policy (participants-only, etc.)
        if (!current_user_can('read_post', $event_id) && !current_user_can('read')) {
            return new WP_Error('rest_forbidden', __('You cannot view this chat.', 'artpulse'), ['status' => 403]);
        }
        return true;
    }

    /** Write permission (nonce + read access) */
    public function can_write(WP_REST_Request $request)
    {
        $auth = $this->can_read($request);
        if ($auth !== true) {
            return $auth;
        }
        $nonce = $request->get_header('X-WP-Nonce');
        if (!$nonce || !wp_verify_nonce($nonce, 'wp_rest')) {
            return new WP_Error('rest_forbidden', __('Invalid or missing nonce.', 'artpulse'), ['status' => 401]);
        }
        return true;
    }

    public function get_messages(WP_REST_Request $request): WP_REST_Response|WP_Error
    {
        $event_id = (int) $request['id'];

        try {
            if (!\function_exists('\\ArtPulse\\DB\\Chat\\get_messages')) {
                throw new \RuntimeException('Chat DB helpers not loaded');
            }

            \ArtPulse\DB\Chat\maybe_install_tables();
            $rows = \ArtPulse\DB\Chat\get_messages($event_id);

            $messages = array_map(static function (array $row) {
                $uid    = (int) ($row['user_id'] ?? 0);
                $user   = $uid ? get_userdata($uid) : null;
                $avatar = $uid ? get_avatar_url($uid, ['size' => 48]) : '';
                if ($avatar) {
                    $avatar = set_url_scheme($avatar, is_ssl() ? 'https' : 'http'); // don’t force https on LAN
                }

                $reactions = $row['reactions'] ?? [];
                if (\is_string($reactions)) {
                    $decoded = json_decode($reactions, true);
                    $reactions = \is_array($decoded) ? $decoded : [];
                }

                return [
                    'id'         => (int) ($row['id'] ?? 0),
                    'user_id'    => $uid,
                    'author'     => $user ? (string) $user->display_name : '',
                    'avatar'     => $avatar ?: '',
                    'content'    => (string) ($row['content'] ?? ''),
                    'created_at' => (string) ($row['created_at'] ?? ''),
                    'flagged'    => (int) ($row['flagged'] ?? 0),
                    'reactions'  => $reactions,
                ];
            }, is_array($rows) ? $rows : []);

            return rest_ensure_response($messages);
        } catch (\Throwable $e) {
            error_log('[artpulse] chat GET error for event '.$event_id.': '.$e->getMessage());
            return new WP_Error('ap_chat_error', __('Chat load failed.', 'artpulse'), ['status' => 500]);
        }
    }

    public function create_item(WP_REST_Request $request): WP_REST_Response|WP_Error
    {
        $event_id = (int) $request['id'];
        $content  = (string) $request['content'];

        if (!$event_id || get_post_type($event_id) !== 'artpulse_event') {
            return new WP_Error('invalid_event', __('Invalid event.', 'artpulse'), ['status' => 404]);
        }

        // Basic sanitation/limits (adjust if you allow markup)
        $content = sanitize_text_field($content);
        if ($content === '') {
            return new WP_Error('empty_content', __('Message content required.', 'artpulse'), ['status' => 400]);
        }
        if (mb_strlen($content) > 1000) {
            return new WP_Error('content_too_long', __('Message too long.', 'artpulse'), ['status' => 413]);
        }

        try {
            if (!\function_exists('\\ArtPulse\\DB\\Chat\\insert_message')) {
                throw new \RuntimeException('Chat DB helpers not loaded');
            }
            \ArtPulse\DB\Chat\maybe_install_tables();
            $msg = \ArtPulse\DB\Chat\insert_message($event_id, get_current_user_id(), $content);
            return rest_ensure_response($msg);
        } catch (\Throwable $e) {
            error_log('[artpulse] chat POST error for event '.$event_id.': '.$e->getMessage());
            return new WP_Error('ap_chat_error', __('Could not post message.', 'artpulse'), ['status' => 500]);
        }
    }

    public function add_reaction(WP_REST_Request $request): WP_REST_Response|WP_Error
    {
        $msg_id = (int) $request['id'];
        $emoji  = sanitize_text_field((string) $request['emoji']);
        if (!$msg_id || $emoji === '') {
            return new WP_Error('invalid_request', __('Invalid reaction.', 'artpulse'), ['status' => 400]);
        }

        try {
            if (!\function_exists('\\ArtPulse\\DB\\Chat\\add_reaction')) {
                throw new \RuntimeException('Chat DB helpers not loaded');
            }
            \ArtPulse\DB\Chat\add_reaction($msg_id, get_current_user_id(), $emoji);
            return rest_ensure_response(['status' => 'ok']);
        } catch (\Throwable $e) {
            error_log('[artpulse] chat REACTION error for message '.$msg_id.': '.$e->getMessage());
            return new WP_Error('ap_chat_error', __('Could not add reaction.', 'artpulse'), ['status' => 500]);
        }
    }

    public function delete_item(WP_REST_Request $request): WP_REST_Response|WP_Error
    {
        $id = (int) $request['id'];
        if (!$id) {
            return new WP_Error('invalid_request', __('Invalid message id.', 'artpulse'), ['status' => 400]);
        }

        try {
            if (!\function_exists('\\ArtPulse\\DB\\Chat\\delete_message')) {
                throw new \RuntimeException('Chat DB helpers not loaded');
            }
            \ArtPulse\DB\Chat\delete_message($id);
            return rest_ensure_response(['status' => 'deleted']);
        } catch (\Throwable $e) {
            error_log('[artpulse] chat DELETE error for message '.$id.': '.$e->getMessage());
            return new WP_Error('ap_chat_error', __('Could not delete message.', 'artpulse'), ['status' => 500]);
        }
    }

    public function flag_item(WP_REST_Request $request): WP_REST_Response|WP_Error
    {
        $id = (int) $request['id'];
        if (!$id) {
            return new WP_Error('invalid_request', __('Invalid message id.', 'artpulse'), ['status' => 400]);
        }

        try {
            if (!\function_exists('\\ArtPulse\\DB\\Chat\\flag_message')) {
                throw new \RuntimeException('Chat DB helpers not loaded');
            }
            \ArtPulse\DB\Chat\flag_message($id);
            return rest_ensure_response(['status' => 'flagged']);
        } catch (\Throwable $e) {
            error_log('[artpulse] chat FLAG error for message '.$id.': '.$e->getMessage());
            return new WP_Error('ap_chat_error', __('Could not flag message.', 'artpulse'), ['status' => 500]);
        }
    }

    public function moderator_check(WP_REST_Request $request)
    {
        if (!current_user_can('moderate_comments')) {
            return new WP_Error('rest_forbidden', __('Moderator permission required.', 'artpulse'), ['status' => 403]);
        }
        return true;
    }
}
