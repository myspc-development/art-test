(function($){
  let layout = [];
  let widgets = [];
  let allowedMap = {};
  const restRoot = APDashboardBuilder.rest_root;
  const nonce = APDashboardBuilder.nonce;

  function getIncludeAll(){
    return $('#ap-db-show-all').prop('checked');
  }

  function fetchWidgets(role){
    const includeAll = getIncludeAll();
    console.log('selectedRole', role, 'includeAll', includeAll);
    $.ajax({
      url: restRoot + 'artpulse/v1/dashboard-widgets?role=' + encodeURIComponent(role) + (includeAll ? '&include_all=true' : ''),
      method: 'GET',
      beforeSend: xhr => xhr.setRequestHeader('X-WP-Nonce', nonce),
      success: res => {
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
        console.log('widgets', widgets);
        if(res.active && Array.isArray(res.active.layout)){
          layout = res.active.layout;
        } else if(res.active && Array.isArray(res.active.layoutOrder)){
          layout = res.active.layoutOrder.map(id => ({id:id, visible:true}));
        } else {
          layout = widgets.map(w => ({id:w.id, visible:true}));
        }
        console.log('widgetAllowedMap', allowedMap);
        console.log('layoutConfig', layout);
        render();
      }
    });
  }

  function render(){
    const list = $('#ap-db-layout').empty();
    layout.forEach(item => {
      const def = widgets.find(w => w.id === item.id) || {};
      const li = $('<li class="ap-widget"/>').attr('data-id', item.id);
      if(!allowedMap[item.id]){ li.addClass('ap-not-allowed'); }
      const chk = $('<input type="checkbox" class="ap-visible">').prop('checked', item.visible !== false);
      li.append($('<span class="ap-widget-title"/>').text(def.title || def.name || item.id));
      li.append(' ').append(chk).append(' Show');
      list.append(li);
    });
    list.sortable({update:updateLayout});

    const add = $('#ap-db-available').empty();
    widgets.forEach(w => {
      if(!layout.find(l => l.id === w.id)){
        const btn = $('<button type="button" class="ap-add"/>').text('Add '+(w.title||w.name||w.id)).data('id', w.id);
        if(w.notAllowed){ btn.addClass('ap-not-allowed').prop('disabled', true); }
        add.append(btn);
      }
    });

    if(!widgets.length){
      $('#ap-db-warning').text('No layout or widgets available for selected role.').show();
    } else {
      $('#ap-db-warning').hide();
    }
  }

  function updateLayout(){
    layout = $('#ap-db-layout li').map(function(){
      return { id: $(this).data('id'), visible: $(this).find('.ap-visible').prop('checked') };
    }).get();
  }

  $(function(){
    const roleSel = $('#ap-db-role');
    APDashboardBuilder.roles.forEach(r => roleSel.append($('<option/>').val(r).text(r)));
    roleSel.on('change', function(){ fetchWidgets(this.value); });
    $('#ap-db-show-all').on('change', function(){ fetchWidgets(roleSel.val()); });
    $(document).on('click','.ap-add',function(){
      layout.push({id: $(this).data('id'), visible:true});
      render();
    });
    $(document).on('change','.ap-visible',updateLayout);
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
    fetchWidgets(roleSel.val());
  });
})(jQuery);
