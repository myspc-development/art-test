jQuery(document).ready(function($) {
    $('.ap-fav-btn').on('click', function(e){
        e.preventDefault();
        var $btn = $(this);
        var postId = $btn.data('post');
        $.post(apFav.ajaxurl, {action: 'ap_toggle_favorite', post_id: postId, nonce: apFav.nonce}, function(resp){
            if (resp.success) {
                $btn.toggleClass('ap-favorited', resp.data.added);
                $btn.text(resp.data.added ? '★' : '☆');
            }
        });
    });
});

