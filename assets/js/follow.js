(function(){
  document.addEventListener('click', function(e){
    if(e.target.classList.contains('ap-follow-btn')){
      e.preventDefault();
      const btn = e.target;
      if(!window.APFollow){ return; }
      const following = btn.classList.contains('ap-following');
      const data = new FormData();
      data.append('action', 'ap_follow_toggle');
      data.append('object_id', btn.dataset.id);
      data.append('object_type', btn.dataset.type);
      data.append('following', following ? '1' : '0');
      fetch(APFollow.ajaxurl, {
        method: 'POST',
        credentials: 'same-origin',
        headers: { 'X-WP-Nonce': APFollow.nonce },
        body: data
      }).then(r => r.json()).then(res => {
        if(res.success){
          const isFollowing = res.state === 'following';
          btn.classList.toggle('ap-following', isFollowing);
          btn.textContent = isFollowing ? APFollow.unfollowText : APFollow.followText;
        }
      });
    }
  });
})();
