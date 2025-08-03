(function($){
  let layout = [];
  let widgets = [];
  let allowedMap = {};
  let lastRole = null;
  let layoutReady = false;
  let fetching = false;
  const restRoot = APDashboardBuilder.rest_root;
  const nonce = APDashboardBuilder.nonce;
  const visibilityFilters = { public:true, internal:false, deprecated:false };
  const debug = !!APDashboardBuilder.debug;

  function getIncludeAll(){
    return $('#ap-db-show-all').prop('checked');
  }

  function fetchWidgets(role){
    if (fetching) {
      if (debug) console.log('[DashboardBuilder] fetch already in progress');
      return;
    }
    if (role === lastRole && layoutReady) {
      if (debug) console.log('[DashboardBuilder] Skip fetch, role unchanged:', role);
      return;
    }
    lastRole = role;
    layoutReady = false;
    fetching = true;
    const includeAll = getIncludeAll();
    if (debug) console.log('[DashboardBuilder] fetch widgets', role, 'includeAll', includeAll);
    $.ajax({
      url: restRoot + 'artpulse/v1/dashboard-widgets?role=' + encodeURIComponent(role) + (includeAll ? '&include_all=true' : ''),
      method: 'GET',
      beforeSend: xhr => xhr.setRequestHeader('X-WP-Nonce', nonce),
      success: res => {
        if (debug) console.log('[DashboardBuilder] response', res);
        const allowed = res.available || [];
        widgets = allowed.slice();
        allowedMap = {};
        allowed.forEach(w => { allowedMap[w.id] = true; });
        if(includeAll && Array.isArray(res.all)){
          res.all.forEach(w => {
            if(!allowedMap[w.id]){
              widgets.push(Object.assign({notAllowed:true}, w));
            }
          });
        }
        if (debug) console.log('widgets', widgets);
        if(res.active && Array.isArray(res.active.layout)){
          layout = res.active.layout;
        } else if(res.active && Array.isArray(res.active.layoutOrder)){
          layout = res.active.layoutOrder.map(id => ({id:id, visible:true}));
        } else {
          layout = widgets.map(w => ({id:w.id, visible:true}));
        }
        widgets.forEach(w => {
          if(!layout.find(l => l.id === w.id)){
            layout.push({id:w.id, visible:true});
          }
        });
        if (debug) console.log('widgetAllowedMap', allowedMap);
        if (debug) console.log('layoutConfig', layout);
        render();
        layoutReady = true;
        fetching = false;
      },
      error: err => {
        if (debug) console.warn('[DashboardBuilder] fetch failed', err);
        fetching = false;
      }
    });
  }

  function render(){
    const list = $('#ap-db-layout').empty();
    layout.forEach(item => {
      const def = widgets.find(w => w.id === item.id) || {};
      const vis = def.visibility || 'public';
      if(!visibilityFilters[vis]) return;
      const li = $('<li class="ap-widget"/>').attr('data-id', item.id);
      if(!allowedMap[item.id]){ li.addClass('ap-not-allowed'); }
      const chk = $('<input type="checkbox" class="ap-visible">').prop('checked', item.visible !== false);
      li.append($('<span class="ap-widget-title"/>').text(def.title || def.name || item.id));
      li.append(' ').append(chk).append(' Show');
      li.append(' ').append($('<span class="ap-visibility-badge ap-visibility-' + vis + '"/>').text(vis.charAt(0).toUpperCase() + vis.slice(1)));
      list.append(li);
    });
    if(list.hasClass('ui-sortable')){
      list.sortable('destroy');
    }
    list.sortable({
      connectWith:'#ap-db-available',
      update:updateLayout,
      receive:updateLayout
    });

    const add = $('#ap-db-available').empty();
    widgets.forEach(w => {
      const vis = w.visibility || 'public';
      if(!visibilityFilters[vis]) return;
      if(!layout.find(l => l.id === w.id)){
        const li = $('<li class="ap-widget"/>').attr('data-id', w.id).text(w.title || w.name || w.id);
        if(w.notAllowed){ li.addClass('ap-not-allowed'); }
        li.append(' ').append($('<span class="ap-visibility-badge ap-visibility-' + vis + '"/>').text(vis.charAt(0).toUpperCase() + vis.slice(1)));
        add.append(li);
      }
    });
    if(add.hasClass('ui-sortable')){
      add.sortable('destroy');
    }
    add.sortable({
      connectWith:'#ap-db-layout',
      receive:updateLayout,
      update:updateLayout
    });

    if(!widgets.length){
      $('#ap-db-warning').text('No layout or widgets available for selected role.').show();
    } else {
      $('#ap-db-warning').hide();
    }
  }

  function updateLayout(){
    $('#ap-db-layout li').each(function(){
      if($(this).find('.ap-visible').length === 0){
        $(this).append(' ').append(
          $('<input type="checkbox" class="ap-visible">').prop('checked', true)
        ).append(' Show');
      }
    });
    layout = $('#ap-db-layout li').map(function(){
      return { id: $(this).data('id'), visible: $(this).find('.ap-visible').prop('checked') };
    }).get();
  }

  function updateFilters(){
    visibilityFilters.public = $('#ap-db-filter-public').prop('checked');
    visibilityFilters.internal = $('#ap-db-filter-internal').prop('checked');
    visibilityFilters.deprecated = $('#ap-db-filter-deprecated').prop('checked');
    render();
  }

  $(function(){
    window.IS_DASHBOARD_BUILDER_PREVIEW = true;
    const roleSel = $('#ap-db-role');
    APDashboardBuilder.roles.forEach(r => roleSel.append($('<option/>').val(r).text(r)));
    roleSel.on('change', function(){ fetchWidgets(this.value); });
    $('#ap-db-show-all').on('change', function(){ fetchWidgets(roleSel.val()); });
    $(document).on('change','.ap-visible',updateLayout);
    $('.ap-db-filter').on('change', updateFilters);
    $('#ap-db-save').on('click', function(){
      updateLayout();
      $.ajax({
        url: restRoot + 'artpulse/v1/dashboard-widgets/save',
        method:'POST',
        contentType:'application/json',
        beforeSend: xhr => xhr.setRequestHeader('X-WP-Nonce', nonce),
        data: JSON.stringify({ role: roleSel.val(), layout: layout }),
        success: () => alert('Saved')
      });
    });
    $('#ap-db-layout').on('dblclick','li',function(){
      const id = $(this).data('id');
      layout = layout.filter(l => l.id !== id);
      render();
    });
    $('#ap-db-available').on('dblclick','li',function(){
      const id = $(this).data('id');
      if(!layout.find(l => l.id === id)){
        layout.push({id:id, visible:true});
        render();
      }
    });
    if (debug) console.log('[DashboardBuilder] initial role', roleSel.val());
    updateFilters();
    fetchWidgets(roleSel.val());
  });
})(jQuery);
