(function($){
    function showNotice(msg){
        var n = document.getElementById('ap-widget-notice');
        if(!n) return;
        n.textContent = msg;
        n.classList.remove('hidden');
        setTimeout(function(){ n.classList.add('hidden'); }, 3000);
    }

    function renderWidget(data){
        var el = document.createElement('div');
        el.className = 'ap-widget-item';
        el.textContent = 'Widget ' + data.id;
        return el;
    }

    $(function(){
        var root = $('#artpulse-widget-editor-root');
        if(!root.length){ return; }

        var items = [{id:1, visible:true},{id:2, visible:true}];
        items.forEach(function(w){
            var li = $('<div class="ap-widget-item" data-id="'+w.id+'"></div>')
                .append('<span class="title">Widget '+w.id+'</span> ')
                .append('<button type="button" class="toggle">'+APWidgetEditor.hide+'</button>');
            root.append(li);
        });

        root.sortable({ stop: save });
        root.on('click','.toggle',function(){
            showNotice('Widget saved successfully');
            save();
        });

        function save(){
            wp.ajax.send('ap_save_role_layout',{
                data:{
                    nonce: APWidgetEditor.nonce,
                    role: APWidgetEditor.role,
                    layout: JSON.stringify(items)
                },
                success:function(){ showNotice('Saved'); }
            });
        }
    });

    if (typeof module !== 'undefined'){
        module.exports = { renderWidget };
    }
})(jQuery);
