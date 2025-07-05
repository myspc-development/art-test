(function($){
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

  $(document).on('ap-show-messages', function(e, id){
    loadMessages(id, function(list){
      console.log(list);
    });
  });

  if(APMessages.pollId){
    setInterval(function(){
      loadMessages(APMessages.pollId);
    }, 5000);
  }
})(jQuery);
