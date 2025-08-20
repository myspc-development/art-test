document.addEventListener('DOMContentLoaded', function(){
  document.querySelectorAll('.ap-favorite-btn').forEach(function(btn) {
    btn.addEventListener('click', function(e){
      e.preventDefault();
      var objectId = this.dataset.objectId;
      var objectType = this.dataset.objectType;
      var isActive = this.classList.contains('active');

      fetch(window.ArtPulseApi.root + 'artpulse/v1/favorites', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-WP-Nonce': window.ArtPulseApi.nonce
        },
        body: JSON.stringify({
          object_id: objectId,
          object_type: objectType
        })
      })
      .then(r => r.json())
      .then(data => {
        if (data.success) {
          this.classList.toggle('active');
          this.textContent = this.classList.contains('active') ? '❤' : '♡';
        } else {
          alert(data.message || 'Error updating favorite');
        }
      }); // Removed .bind(this)
    });
  });
});