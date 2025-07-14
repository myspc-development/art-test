document.addEventListener('DOMContentLoaded', function(){
  const forms = document.querySelectorAll('.ap-newsletter-optin');
  if(!forms.length || typeof APNewsletter === 'undefined') return;
  forms.forEach(form => {
    form.addEventListener('submit', function(e){
      e.preventDefault();
      const email = form.querySelector('input[type="email"]').value;
      if(!email) return;
      fetch(APNewsletter.endpoint, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-WP-Nonce': APNewsletter.nonce
        },
        body: JSON.stringify({ email })
      }).then(r => r.json()).then(() => {
        const msg = form.querySelector('.ap-optin-message');
        if(msg) msg.textContent = APNewsletter.successText;
      }).catch(() => {
        const msg = form.querySelector('.ap-optin-message');
        if(msg) msg.textContent = APNewsletter.errorText;
      });
    });
  });
});
