(function(){
  document.addEventListener('click', function(e){
    if(e.target.classList.contains('ap-support-btn')){
      e.preventDefault();
      const id = e.target.dataset.id;
      const amount = e.target.dataset.amount || 5;
      fetch(APDonations.root + 'artpulse/v1/artist/' + id + '/tip', {
        method: 'POST',
        headers: {
          'X-WP-Nonce': APDonations.nonce,
          'Content-Type': 'application/json'
        },
        body: JSON.stringify({amount: amount})
      }).then(r=>r.json()).then(()=>{
        alert(APDonations.thanks);
      });
    }
  });
})();
