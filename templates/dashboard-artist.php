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

  <div class="dashboard-grid">
    <!-- Profile Summary -->
    <div class="dashboard-card">
      <h3 class="card-title">👤 Profile Summary</h3>
      <p>Name: <?php echo $user_name; ?></p>
      <p>Role: Artist</p>
      <a href="/edit-profile" class="card-link">Edit Profile</a>
    </div>

    <!-- Upload Artwork -->
    <div class="dashboard-card">
      <h3 class="card-title">🖼 Upload Artwork</h3>
      <p>Submit new pieces to your portfolio.</p>
      <button class="btn-primary">Upload</button>
    </div>

    <!-- Exhibitions -->
    <div class="dashboard-card">
      <h3 class="card-title">🏛 Upcoming Exhibitions</h3>
      <ul>
        <li>Urban Light – Aug 2025</li>
        <li>Local Visions – Sep 2025</li>
      </ul>
      <a href="/events" class="card-link">View All</a>
    </div>

    <!-- Stats -->
    <div class="dashboard-card">
      <h3 class="card-title">📈 Engagement Stats</h3>
      <p>Views: 4,203</p>
      <p>Likes: 327</p>
      <p>Inquiries: 22</p>
    </div>
  </div>
</div>
