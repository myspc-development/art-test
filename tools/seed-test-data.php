<?php
/**
 * Seed basic test data for QA.
 *
 * Usage:
 *   wp eval-file tools/seed-test-data.php
 */

if (php_sapi_name() !== 'cli') {
    exit("Run via WP-CLI\n");
}

// Ensure custom roles exist.
foreach (['member', 'artist', 'organization'] as $role) {
    if (!get_role($role)) {
        add_role($role, ucfirst($role), ['read' => true]);
    }
}

$members = [];
for ($i = 1; $i <= 2; $i++) {
    $id = wp_insert_user([
        'user_login' => "member{$i}",
        'user_pass'  => 'password',
        'user_email' => "member{$i}@example.com",
        'role'       => 'member',
    ]);
    $members[] = $id;
}

$artists = [];
for ($i = 1; $i <= 2; $i++) {
    $id = wp_insert_user([
        'user_login' => "artist{$i}",
        'user_pass'  => 'password',
        'user_email' => "artist{$i}@example.com",
        'role'       => 'artist',
    ]);
    $artists[] = $id;
    wp_insert_post([
        'post_type'   => 'artpulse_artwork',
        'post_title'  => "Sample Artwork {$i}",
        'post_status' => 'publish',
        'post_author' => $id,
    ]);
}

$org = wp_insert_user([
    'user_login' => 'org1',
    'user_pass'  => 'password',
    'user_email' => 'org1@example.com',
    'role'       => 'organization',
]);

for ($i = 1; $i <= 2; $i++) {
    wp_insert_post([
        'post_type'   => 'artpulse_event',
        'post_title'  => "Sample Event {$i}",
        'post_status' => 'publish',
        'post_author' => $org,
    ]);
}

foreach (range(1, 3) as $i) {
    ArtPulse\Crm\DonationModel::add($org, $members[0], 5.0 * $i);
}

update_user_meta($org, 'ap_crm_seed_audience', [
    ['user' => $members[0], 'tag' => 'donor'],
    ['user' => $members[1], 'tag' => 'subscriber'],
]);

echo "Seed data created.\n";

