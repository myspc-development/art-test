(function(){
  document.addEventListener('DOMContentLoaded', function(){
    var tablist = document.querySelector('.ap-role-tabs[role="tablist"]');
    var panels = Array.prototype.slice.call(document.querySelectorAll('.ap-role-layout[role="tabpanel"]'));
    if (!tablist || !panels.length) return;
    var tabs = Array.prototype.slice.call(tablist.querySelectorAll('.ap-role-tab[role="tab"]'));
    if (!tabs.length) return;
    var root = document.querySelector('.dashboard-widgets-wrap') || document.getElementById('ap-dashboard-root');
    if (root && root.dataset.apV2 !== '1') return;
    var url = new URL(window.location.href);
    var initial = url.searchParams.get('role') || localStorage.getItem('ap:lastRole') || tabs[0].dataset.role;

    function setActive(role, focusTab){
      tabs.forEach(function(tab){
        var active = tab.dataset.role === role;
        tab.setAttribute('aria-selected', active ? 'true' : 'false');
        tab.tabIndex = active ? 0 : -1;
        tab.classList.toggle('active', active);
        if (active && focusTab) { tab.focus(); }
      });
      panels.forEach(function(panel){
        var active = panel.dataset.role === role;
        if (active) { panel.removeAttribute('hidden'); }
        else { panel.setAttribute('hidden', ''); }
      });
      if (root) { root.setAttribute('data-role-theme', role); }
      localStorage.setItem('ap:lastRole', role);
      url.searchParams.set('role', role);
      history.replaceState(null, '', url.toString());
    }

    tabs.forEach(function(tab){
      tab.addEventListener('click', function(){
        setActive(tab.dataset.role, false);
      });
    });

    tablist.addEventListener('keydown', function(e){
        var index = tabs.findIndex(function(t){ return t.getAttribute('aria-selected') === 'true'; });
        if (index === -1) index = 0;
        var next;
        switch(e.key){
          case 'ArrowLeft':
          case 'ArrowUp':
            next = (index - 1 + tabs.length) % tabs.length;
            e.preventDefault();
            setActive(tabs[next].dataset.role, true);
            break;
          case 'ArrowRight':
          case 'ArrowDown':
            next = (index + 1) % tabs.length;
            e.preventDefault();
            setActive(tabs[next].dataset.role, true);
            break;
          case 'Home':
            e.preventDefault();
            setActive(tabs[0].dataset.role, true);
            break;
          case 'End':
            e.preventDefault();
            setActive(tabs[tabs.length - 1].dataset.role, true);
            break;
        }
      });

    setActive(initial, false);
  });
})();
