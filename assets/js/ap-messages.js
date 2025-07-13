(function($){
  var pollInterval = null;
  function showError(msg){
    var box = $('#ap-messages-error');
    if(!box.length){
      box = $('<div id="ap-messages-error" class="ap-error"/>').insertBefore('#ap-message-list');
    }
    box.text(msg).show();
  }
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
          var li = $('<li>').attr('data-id', item.user_id);
          if(item.avatar){
            li.append($('<img>').attr('src', item.avatar).attr('alt', '').addClass('ap-avatar'));
          }
          var name = item.display_name ? item.display_name : 'User ' + item.user_id;
          var label = $('<span>').text(name);
          li.append(label);
          if(item.unread){
            li.append(' (' + item.unread + ')');
          }
          li.on('click', function(){
            $(document).trigger('ap-show-messages', item.user_id);
          });
          $list.append(li);
        });
      },
      error: function(jqXHR){
        if(jqXHR.status === 401 || jqXHR.status === 403){
          showError("You must be logged in to use messaging.");
          var $list = $('#ap-conversation-list');
          if($list.length){
            $list.empty().append('<li>Please log in to view messages.</li>');
          }
          if(pollInterval){
            clearInterval(pollInterval);
            pollInterval = null;
          }
        }
      }
    });
  }

  function loadMessages(id, cb){
    $.ajax({
      url: APMessages.apiRoot + 'artpulse/v1/messages',
      method: 'GET',
      data: { with: id },
      beforeSend: function(xhr){ xhr.setRequestHeader('X-WP-Nonce', APMessages.nonce); },
      success: function(data){
        if (cb) cb(data);
      },
      error: function(jqXHR){
        if(jqXHR.status === 401 || jqXHR.status === 403){
          showError("You must be logged in to use messaging.");
          var $box = $('#ap-message-list');
          if($box.length){
            $box.empty().append('<li>Please log in to view messages.</li>');
          }
          if(pollInterval){
            clearInterval(pollInterval);
            pollInterval = null;
          }
        }
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
    const recipientId = window.currentRecipientId || 0;
    if(recipientId){
      APMessages.pollId = recipientId;
      $(document).trigger('ap-show-messages', recipientId);
    }
  });

  if(APMessages.pollId){
    pollInterval = setInterval(function(){
      loadMessages(APMessages.pollId);
    }, 5000);
  }
})(jQuery);
