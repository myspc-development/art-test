<?php
/**
 * Seed users and content for dashboard testing.
 * Usage: wp eval-file tools/seed-dashboard-users.php
 */
if (php_sapi_name() !== 'cli') {
    exit("Run via WP-CLI\n");
}

foreach (['member','artist','organization'] as $role) {
    if (!get_role($role)) {
        add_role($role, ucfirst($role), ['read'=>true]);
    }
}

$org_id = wp_insert_user([
    'user_login' => 'org_demo',
    'user_pass'  => 'password',
    'user_email' => 'org_demo@example.com',
    'role'       => 'organization',
]);

update_user_meta($org_id, 'ap_org_name', 'Demo Org');
update_user_meta($org_id, 'ap_org_type', 'gallery');

for ($i=1; $i<=3; $i++) {
    $uid = wp_insert_user([
        'user_login' => "member{$i}",
        'user_pass'  => 'password',
        'user_email' => "member{$i}@example.com",
        'role'       => 'member',
    ]);
    add_user_meta($uid, 'favorite_color', 'blue');

    // demo events per member
    for ($e=1; $e<=2; $e++) {
        wp_insert_post([
            'post_type'   => 'artpulse_event',
            'post_title'  => "Member {$i} Event {$e}",
            'post_status' => 'publish',
            'post_author' => $uid,
        ]);
    }

    // demo donations to the org
    if (class_exists('ArtPulse\\Crm\\DonationModel')) {
        ArtPulse\Crm\DonationModel::add($org_id, $uid, 5.0 * $i);
    }
}

for ($i=1; $i<=2; $i++) {
    $post_id = wp_insert_post([
        'post_type'   => 'artpulse_event',
        'post_title'  => "Demo Event {$i}",
        'post_status' => 'publish',
        'post_author' => $org_id,
    ]);
    update_post_meta($post_id, 'event_lat', '0');
    update_post_meta($post_id, 'event_lng', '0');
}

// sample donations
if (class_exists('ArtPulse\\Crm\\DonationModel')) {
    foreach (range(1,3) as $d) {
        ArtPulse\Crm\DonationModel::add($org_id, 1, 10.0*$d);
    }
}

echo "Dashboard seed complete\n";
