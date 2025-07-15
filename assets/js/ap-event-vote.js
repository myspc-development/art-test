(function(){
  document.addEventListener('DOMContentLoaded', function(){
    document.querySelectorAll('.ap-event-vote').forEach(function(btn){
      var eventId = btn.dataset.eventId;
      var countEl = btn.nextElementSibling;
      function refresh(){
        fetch(APEventVote.apiRoot + 'artpulse/v1/event/' + eventId + '/votes')
          .then(r=>r.json())
          .then(d=>{
            if(countEl) countEl.textContent = d.votes;
            if(d.voted) btn.disabled = true; else btn.disabled = false;
          });
      }
      btn.addEventListener('click', function(){
        fetch(APEventVote.apiRoot + 'artpulse/v1/event/' + eventId + '/vote',{
          method:'POST',
          headers:{'X-WP-Nonce':APEventVote.nonce}
        }).then(refresh);
      });
      refresh();
    });
  });
})();
