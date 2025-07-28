    </main>
  </div>
</div>
<?php if (is_user_logged_in() && \ArtPulse\Core\DashboardController::get_role(get_current_user_id()) === 'artist'): ?>
<script>
document.addEventListener('DOMContentLoaded', function(){
  const cb = document.getElementById('ap-profile-public');
  if(!cb) return;
  cb.addEventListener('change', function(){
    fetch('<?php echo esc_url_raw(rest_url('artpulse/v1/user/profile')); ?>', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-WP-Nonce': '<?php echo wp_create_nonce('wp_rest'); ?>'
      },
      body: JSON.stringify({ ap_profile_public: cb.checked ? 1 : 0 })
    });
  });
});
</script>
<?php endif; ?>
