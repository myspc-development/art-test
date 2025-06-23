jQuery(function ($) {
    $('#ap-edit-event-form').on('submit', function (e) {
        e.preventDefault();
        const form = this;
        const data = new FormData(form);
        data.append('action', 'ap_save_event');
        data.append('nonce', APEditEvent.nonce);
        data.append('post_id', $(form).data('post-id'));
        if (!data.has('event_featured')) {
            data.append('event_featured', '0');
        }
        $.ajax({
            url: APEditEvent.ajax_url,
            method: 'POST',
            data,
            processData: false,
            contentType: false,
            success(res) {
                if (res.success) {
                    $(form).find('.ap-edit-event-error').text('Saved!').css('color', 'green');
                } else {
                    $(form).find('.ap-edit-event-error').text(res.data.message || 'Error saving.');
                }
            },
            error() {
                $(form).find('.ap-edit-event-error').text('Request failed.');
            }
        });
    });

    $('#ap-delete-event-btn').on('click', function (e) {
        e.preventDefault();
        if (!confirm('Are you sure you want to delete this event?')) return;

        $.post(APEditEvent.ajax_url, {
            action: 'ap_delete_event',
            nonce: APEditEvent.nonce,
            post_id: $(this).data('post-id')
        }, function (res) {
            if (res.success) {
                alert('Event deleted.');
                window.location.href = '/events';
            } else {
                alert(res.data.message || 'Failed to delete.');
            }
        });
    });
});
