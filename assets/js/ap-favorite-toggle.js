jQuery(document).ready(function($) {
    $('.ap-fav-btn').on('click', function(e){
        e.preventDefault();
        var $btn = $(this);
        var objectId = $btn.data('object-id');
        var objectType = $btn.data('object-type');
        $.post(apFav.ajaxurl, {action: 'ap_toggle_favorite', post_id: objectId, nonce: apFav.nonce, object_type: objectType}, function(resp){
            if (resp.success) {
                $btn.toggleClass('ap-favorited', resp.data.added);
                $btn.text(resp.data.added ? '★' : '☆');
            }
        });
    });
});

