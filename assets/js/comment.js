(function(){
  document.addEventListener('DOMContentLoaded', function(){
    const container = document.getElementById('ap-artist-comments');
    if(!container || !window.APArtistComments){ return; }

    const postId = container.dataset.postId;
    const listEl = container.querySelector('.ap-comments-list');
    const form = container.querySelector('form');
    const parentInput = form ? form.querySelector('input[name="comment_parent"]') : null;

    function buildTree(comments){
      const map = {};
      const roots = [];
      comments.forEach(c => { c.children = []; map[c.id] = c; });
      comments.forEach(c => {
        if(c.parent && map[c.parent]){
          map[c.parent].children.push(c);
        } else {
          roots.push(c);
        }
      });
      return roots;
    }

    function renderComment(c){
      const li = document.createElement('li');
      li.className = 'ap-comment';
      li.innerHTML = `<strong>${c.author_name}</strong> ${c.content.rendered}`;
      const reply = document.createElement('button');
      reply.type = 'button';
      reply.textContent = 'Reply';
      reply.addEventListener('click', () => {
        if(parentInput){ parentInput.value = c.id; }
        form.querySelector('#comment').focus();
      });
      li.appendChild(reply);
      if(c.children && c.children.length){
        const ul = document.createElement('ul');
        c.children.forEach(child => ul.appendChild(renderComment(child)));
        li.appendChild(ul);
      }
      return li;
    }

    function loadComments(){
      wp.apiFetch({ path: `/wp/v2/comments?post=${postId}&per_page=100` })
        .then(data => {
          listEl.innerHTML = '';
          const tree = buildTree(data);
          const ul = document.createElement('ul');
          tree.forEach(c => ul.appendChild(renderComment(c)));
          listEl.appendChild(ul);
        });
    }

    if(form){
      form.addEventListener('submit', function(e){
        e.preventDefault();
        const content = form.querySelector('#comment').value.trim();
        if(!content){ return; }
        const data = {
          post: postId,
          content: content,
          parent: parentInput ? parentInput.value : 0
        };
        wp.apiFetch({
          path: '/wp/v2/comments',
          method: 'POST',
          data,
          headers: { 'X-WP-Nonce': APArtistComments.nonce }
        }).then(() => {
          form.reset();
          if(parentInput){ parentInput.value = 0; }
          loadComments();
        });
      });
    }

    loadComments();
  });
})();
