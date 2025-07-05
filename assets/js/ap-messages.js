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
        data.forEach(function(id){
          var li = $('<li>').text('User ' + id).attr('data-id', id);
          li.on('click', function(){
            $(document).trigger('ap-show-messages', id);
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
    loadMessages(id, function(list){
      var $box = $('#ap-message-list');
      var ids = [];
      if($box.length) $box.empty();
      list.forEach(function(m){
        ids.push(m.id);
        if($box.length){
          $box.append('<li>' + m.content + '</li>');
        }
      });
      if(ids.length){
        markRead(ids);
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
