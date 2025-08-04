(function($){
    function showNotice(msg){
        var n = document.getElementById('ap-widget-notice');
        if(!n) return;
        n.textContent = msg;
        n.classList.remove('hidden');
        setTimeout(function(){ n.classList.add('hidden'); }, 3000);
    }

    // TODO: migrate to a React component for consistency with other UIs.
    function renderWidget(data){
        var el = document.createElement('div');
        el.className = 'ap-widget-item';
        el.setAttribute('data-id', data.id);

        var title = document.createElement('span');
        title.className = 'title';
        title.textContent = 'Widget ' + data.id;
        el.appendChild(title);

        var btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'toggle';
        if (typeof APWidgetEditor !== 'undefined') {
            btn.textContent = APWidgetEditor.hide;
        }
        el.appendChild(btn);

        return el;
    }

    $(function(){
        var root = $('#artpulse-widget-editor-root');
        if(!root.length){ return; }

        var items = [{id:1, visible:true},{id:2, visible:true}];
        items.forEach(function(w){
            root.append(renderWidget(w));
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
                success:function(){ showNotice('Saved'); },
                error:function(){ showNotice('Failed to save'); }
            });
        }
    });

    if (typeof module !== 'undefined'){
        module.exports = { renderWidget };
    }
})(jQuery);
