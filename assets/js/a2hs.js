(function(){
  let deferred;
  window.addEventListener('beforeinstallprompt', e => {
    e.preventDefault();
    deferred = e;
    const btn = document.querySelector('#ap-install-btn');
    if (btn) {
      btn.style.display = 'block';
      btn.addEventListener('click', () => {
        btn.style.display = 'none';
        deferred.prompt();
        deferred = null;
      });
    }
  });
})();
