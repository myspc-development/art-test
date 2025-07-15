(function(){
  document.addEventListener('DOMContentLoaded', function(){
    document.querySelectorAll('.ap-qa-thread').forEach(function(container){
      var eventId = container.dataset.eventId;
      var list = container.querySelector('.ap-qa-list');
      var form = container.querySelector('.ap-qa-form');
      function load(){
        fetch(APQa.apiRoot + 'artpulse/v1/qa-thread/' + eventId)
          .then(r=>r.json())
          .then(d=>{
            list.innerHTML='';
            d.comments.forEach(function(c){
              var li=document.createElement('li');
              li.textContent = c.author + ': ' + c.content;
              list.appendChild(li);
            });
          });
      }
      load();
      if(form){
        form.addEventListener('submit', function(e){
          e.preventDefault();
          var txt=form.querySelector('textarea').value.trim();
          if(!txt) return;
          fetch(APQa.apiRoot + 'artpulse/v1/qa-thread/' + eventId + '/post',{
            method:'POST',
            headers:{'Content-Type':'application/json','X-WP-Nonce':APQa.nonce},
            body:JSON.stringify({content:txt})
          }).then(()=>{form.reset();load();});
        });
      }
    });
  });
})();
