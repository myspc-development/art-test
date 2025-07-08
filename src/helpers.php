<?php
/**
 * Helper function for dashboard layout permissions.
 */
function ap_user_can_edit_layout(string $role): bool
{
    return current_user_can($role) || current_user_can('manage_options');
}
