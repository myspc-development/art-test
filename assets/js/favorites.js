(function(){
  document.addEventListener('DOMContentLoaded', function(){
    document.querySelectorAll('.ap-favorites-feed').forEach(initFeed);
  });

  function initFeed(container){
    if(!window.ArtPulseFavoritesFeed) return;
    const { apiRoot, nonce } = window.ArtPulseFavoritesFeed;
    fetch(apiRoot + 'artpulse/v1/follow/feed', { headers: { 'X-WP-Nonce': nonce } })
      .then(r => r.json())
      .then(items => {
        container.innerHTML = '';
        if(!items.length){
          container.textContent = 'No recent activity.';
          return;
        }
        const ul = document.createElement('ul');
        items.forEach(it => {
          const li = document.createElement('li');
          const a = document.createElement('a');
          a.href = it.link || '#';
          a.textContent = it.title || it.type;
          li.appendChild(a);
          const time = document.createElement('time');
          time.textContent = new Date(it.date).toLocaleDateString();
          li.appendChild(document.createTextNode(' '));
          li.appendChild(time);
          ul.appendChild(li);
        });
        container.appendChild(ul);
      })
      .catch(() => {
        container.textContent = 'Failed to load activity.';
      });
  }
})();
