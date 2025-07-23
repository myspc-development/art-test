(function($){
  let layout = [];
  let widgets = [];
  const restRoot = APDashboardBuilder.rest_root;
  const nonce = APDashboardBuilder.nonce;

  function fetchWidgets(role){
    console.log('selectedRole', role);
    $.ajax({
      url: restRoot + 'artpulse/v1/dashboard-widgets?role=' + encodeURIComponent(role),
      method: 'GET',
      beforeSend: xhr => xhr.setRequestHeader('X-WP-Nonce', nonce),
      success: res => {
        widgets = res.available || [];
        console.log('widgets', widgets);
        if(res.active && Array.isArray(res.active.layout)){
          layout = res.active.layout;
        } else if(res.active && Array.isArray(res.active.layoutOrder)){
          layout = res.active.layoutOrder.map(id => ({id:id, visible:true}));
        } else {
          layout = widgets.map(w => ({id:w.id, visible:true}));
        }
        const map = {};
        widgets.forEach(w => { map[w.id] = true; });
        console.log('widgetComponentMap', map);
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
      const chk = $('<input type="checkbox" class="ap-visible">').prop('checked', item.visible !== false);
      li.append($('<span class="ap-widget-title"/>').text(def.title || def.name || item.id));
      li.append(' ').append(chk).append(' Show');
      list.append(li);
    });
    list.sortable({update:updateLayout});

    const add = $('#ap-db-available').empty();
    widgets.forEach(w => {
      if(!layout.find(l => l.id === w.id)){
        add.append(
          $('<button type="button" class="ap-add"/>').text('Add '+(w.title||w.name||w.id)).data('id', w.id)
        );
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
