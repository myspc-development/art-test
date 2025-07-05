(function($){
  function listConversations(){
    $.ajax({
      url: APMessages.apiRoot + 'artpulse/v1/conversations',
      method: 'GET',
      beforeSend: function(xhr){ xhr.setRequestHeader('X-WP-Nonce', APMessages.nonce); },
      success: function(data){
        var $list = $('#ap-conversation-list');
        if(!$list.length) return;
        $list.empty();
        if(!data || !data.length){
          $list.append('<li>No conversations.</li>');
          return;
        }
        data.forEach(function(item){
          var text = 'User ' + item.user_id;
          if(item.unread){ text += ' (' + item.unread + ')'; }
          var li = $('<li>').text(text).attr('data-id', item.user_id);
          li.on('click', function(){
            $(document).trigger('ap-show-messages', item.user_id);
          });
          $list.append(li);
        });
      }
    });
  }

  function loadMessages(id, cb){
    $.ajax({
      url: APMessages.apiRoot + 'artpulse/v1/messages?with=' + id,
      method: 'GET',
      beforeSend: function(xhr){ xhr.setRequestHeader('X-WP-Nonce', APMessages.nonce); },
      success: function(data){
        if (cb) cb(data);
      }
    });
  }

  function markRead(ids){
    $.ajax({
      url: APMessages.apiRoot + 'artpulse/v1/message/read',
      method: 'POST',
      data: { ids: ids },
      beforeSend: function(xhr){ xhr.setRequestHeader('X-WP-Nonce', APMessages.nonce); }
    });
  }

  $(document).on('ap-show-messages', function(e, id){
    APMessages.pollId = id;
    var $form = $('#ap-message-form');
    if($form.length){
      $form.show();
      $form.find('input[name="recipient_id"]').val(id);
    }
    loadMessages(id, function(list){
      var $box = $('#ap-message-list');
      var ids = [];
      if($box.length) $box.empty();
      list.forEach(function(m){
        ids.push(m.id);
        if($box.length){
          var li = $('<li>').text(m.content);
          $box.append(li);
        }
      });
      if(ids.length){
        markRead(ids);
        listConversations();
      }
    });
  });

  $('#ap-message-form').on('submit', function(e){
    e.preventDefault();
    var id = $(this).find('input[name="recipient_id"]').val();
    var content = $(this).find('textarea[name="content"]').val().trim();
    if(!id || !content) return;
    $.ajax({
      url: APMessages.apiRoot + 'artpulse/v1/messages',
      method: 'POST',
      contentType: 'application/json',
      data: JSON.stringify({ recipient_id: parseInt(id,10), content: content }),
      beforeSend: function(xhr){ xhr.setRequestHeader('X-WP-Nonce', APMessages.nonce); },
      success: function(){
        $('#ap-message-form textarea[name="content"]').val('');
        $(document).trigger('ap-show-messages', id);
        listConversations();
      }
    });
  });

  $(document).ready(function(){
    listConversations();
  });

  if(APMessages.pollId){
    setInterval(function(){
      loadMessages(APMessages.pollId);
    }, 5000);
  }
})(jQuery);
