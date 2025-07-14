<?php
defined('ABSPATH') || exit;

function ap_message_sent_hook(int $message_id) {
    do_action('ap_message_sent', $message_id);
}

function ap_message_read_hook(int $message_id) {
    do_action('ap_message_read', $message_id);
}
