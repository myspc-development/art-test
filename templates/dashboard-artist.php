<?php
// Ensure WordPress is loaded
if (!defined('ABSPATH')) {
    exit;
}

$current_user = wp_get_current_user();
$user_name = esc_html($current_user->display_name);
?>

<div class="ap-dashboard-wrap artist-dashboard">
  <header class="mb-6">
    <h2 class="text-3xl font-semibold">🎨 Artist Dashboard</h2>
    <p class="text-gray-600 mt-1">Welcome back, <?php echo $user_name; ?>!</p>
  </header>

  <div class="ap-dashboard-grid">
    <!-- Profile Summary -->
    <div class="ap-card" role="region" aria-labelledby="profile-summary-title">
      <h2 id="profile-summary-title" class="ap-card__title">👤 Profile Summary</h2>
      <p>Name: <?php echo $user_name; ?></p>
      <p>Role: Artist</p>
      <a href="/edit-profile" class="card-link">Edit Profile</a>
    </div>

    <!-- Upload Artwork -->
    <div class="ap-card" role="region" aria-labelledby="upload-artwork-title">
      <h2 id="upload-artwork-title" class="ap-card__title">🖼 Upload Artwork</h2>
      <p>Submit new pieces to your portfolio.</p>
      <button class="btn-primary">Upload</button>
    </div>

    <!-- Exhibitions -->
    <div class="ap-card" role="region" aria-labelledby="exhibitions-title">
      <h2 id="exhibitions-title" class="ap-card__title">🏛 Upcoming Exhibitions</h2>
      <ul>
        <li>Urban Light – Aug 2025</li>
        <li>Local Visions – Sep 2025</li>
      </ul>
      <a href="/events" class="card-link">View All</a>
    </div>

    <!-- Stats -->
    <div class="ap-card" role="region" aria-labelledby="engagement-stats-title">
      <h2 id="engagement-stats-title" class="ap-card__title">📈 Engagement Stats</h2>
      <p>Views: 4,203</p>
      <p>Likes: 327</p>
      <p>Inquiries: 22</p>
    </div>
  </div>
</div>
