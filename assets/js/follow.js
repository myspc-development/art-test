(function(){
  document.addEventListener('click', function(e){
    if(e.target.classList.contains('ap-follow-btn')){
      e.preventDefault();
      const btn = e.target;
      if(!window.APFollow){ return; }
      const data = new FormData();
      data.append('action', btn.classList.contains('following') ? 'ap_unfollow_post' : 'ap_follow_post');
      data.append('post_id', btn.dataset.postId);
      data.append('post_type', btn.dataset.postType);
      fetch(APFollow.ajaxurl, {
        method: 'POST',
        credentials: 'same-origin',
        headers: { 'X-WP-Nonce': APFollow.nonce },
        body: data
      }).then(r => r.json()).then(res => {
        if(res.success){
          btn.classList.toggle('following');
          btn.textContent = btn.classList.contains('following') ? APFollow.unfollowText : APFollow.followText;
        }
      });
    }
  });
})();
