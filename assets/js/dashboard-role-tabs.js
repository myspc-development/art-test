(function(){
  document.addEventListener('DOMContentLoaded', function(){
    var tabs = document.querySelectorAll('.ap-role-tab');
    if(!tabs.length) return;
    var layouts = document.querySelectorAll('.ap-role-layout');
    function show(role){
      layouts.forEach(function(el){
        el.style.display = el.dataset.role === role ? '' : 'none';
      });
      tabs.forEach(function(t){
        t.classList.toggle('active', t.dataset.role === role);
      });
    }
    tabs.forEach(function(tab){
      tab.addEventListener('click', function(){
        show(tab.dataset.role);
      });
    });
    show(tabs[0].dataset.role);
  });
})();
