<?php
namespace ArtPulse;

function ap_user_membership_level(int $user_id = 0): string
{
    $user_id = $user_id ?: get_current_user_id();
    $level   = get_user_meta($user_id, 'ap_membership_level', true);
    return $level ?: 'basic';
}

function ap_has_membership(string $level, int $user_id = 0): bool
{
    $levels = ['basic' => 1, 'pro' => 2, 'premium' => 3];
    $current = ap_user_membership_level($user_id);
    return ($levels[$current] ?? 0) >= ($levels[$level] ?? 0);
}
