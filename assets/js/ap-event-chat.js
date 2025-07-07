(function(){
  document.addEventListener('DOMContentLoaded', function(){
    document.querySelectorAll('.ap-event-chat').forEach(function(container){
      var eventId = container.dataset.eventId;
      var listEl = container.querySelector('.ap-chat-list');
      var form = container.querySelector('.ap-chat-form');

      function load(){
        fetch(APChat.apiRoot + 'artpulse/v1/event/' + eventId + '/chat')
          .then(r => r.json())
          .then(function(data){
            listEl.innerHTML = '';
            data.forEach(function(m){
              var li = document.createElement('li');
              li.textContent = m.author + ': ' + m.content;
              listEl.appendChild(li);
            });
          });
      }

      load();
      if(APChat.poll){
        setInterval(load, 5000);
      }

      if(form){
        form.addEventListener('submit', function(e){
          e.preventDefault();
          var txt = form.querySelector('[name="content"]').value.trim();
          if(!txt) return;
          fetch(APChat.apiRoot + 'artpulse/v1/event/' + eventId + '/chat', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'X-WP-Nonce': APChat.nonce
            },
            body: JSON.stringify({content: txt})
          }).then(function(){
            form.reset();
            load();
          });
        });
      }
    });
  });
})();
