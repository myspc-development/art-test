jQuery(function ($) {
  let imageUrl = '';

  // Launch media uploader
  $(document).on('click', '#ap-upload-image', function (e) {
    e.preventDefault();
    const frame = wp.media({
      title: 'Select or Upload Image',
      button: { text: 'Use this image' },
      multiple: false
    });

    frame.on('select', function () {
      const attachment = frame.state().get('selection').first().toJSON();
      imageUrl = attachment.url;
      $('#ap-preview').attr('src', imageUrl).show();
    });

    frame.open();
  });

  $('#ap-portfolio-form').on('submit', function (e) {
    e.preventDefault();

    const data = {
      action: 'ap_save_portfolio',
      nonce: APPortfolio.nonce,
      title: $('input[name="title"]').val(),
      category: $('select[name="category"]').val(),
      description: $('textarea[name="description"]').val(),
      link: $('input[name="link"]').val(),
      visibility: $('select[name="visibility"]').val(),
      image: imageUrl,
      post_id: $('input[name="post_id"]').val() || ''
    };

    $.post(APPortfolio.ajaxUrl, data, function (res) {
      $('#ap-portfolio-message').text(res.data.message);
      if (res.success && res.data.id) {
        const item = $('#ap-saved-items').find(`[data-id="${res.data.id}"]`);
        if (item.length) {
          item.find('strong').text(res.data.title);
        } else {
          // Reload list if new item
          window.location.reload();
        }
      }
      $('#ap-portfolio-form')[0].reset();
      imageUrl = '';
      $('#ap-preview').hide();
    }).fail(() => {
      $('#ap-portfolio-message').text('Error saving item.');
    });
  });

  const savedList = document.getElementById('ap-saved-items');
  if (savedList && window.Sortable) {
    new Sortable(savedList, {
      animation: 150,
      onEnd: function () {
        const ids = [];
        savedList.querySelectorAll('.ap-saved-item').forEach(el => ids.push(el.dataset.id));

        $.post(APPortfolio.ajaxUrl, {
          action: 'ap_save_portfolio_order',
          nonce: APPortfolio.nonce,
          order: ids
        }, function (res) {
          if (res.data && res.data.message) {
            $('.ap-form-messages').text(res.data.message);
          }
        });
      }
    });
  }

  $(savedList).on('click', '.edit-item', function (e) {
    e.preventDefault();
    const wrap = $(this).closest('.ap-saved-item');
    const id = wrap.data('id');
    $.get(APPortfolio.ajaxUrl, {
      action: 'ap_get_portfolio_item',
      nonce: APPortfolio.nonce,
      post_id: id
    }, function (res) {
      if (!res || !res.success) return;
      const d = res.data;
      $('input[name="title"]').val(d.title);
      $('select[name="category"]').val(d.category);
      $('textarea[name="description"]').val(d.description);
      $('input[name="link"]').val(d.link);
      $('select[name="visibility"]').val(d.visibility);
      $('input[name="post_id"]').val(d.id);
      imageUrl = d.image || '';
      if (imageUrl) {
        $('#ap-preview').attr('src', imageUrl).show();
      }
      document.getElementById('ap-portfolio-form').scrollIntoView({ behavior: 'smooth' });
    });
  });

  $(savedList).on('click', '.toggle-visibility', function (e) {
    e.preventDefault();
    const btn = $(this);
    const wrap = btn.closest('.ap-saved-item');
    const id = wrap.data('id');
    const newVis = btn.data('new');
    $.post(APPortfolio.ajaxUrl, {
      action: 'ap_toggle_visibility',
      nonce: APPortfolio.nonce,
      post_id: id,
      visibility: newVis
    }, function (res) {
      if (res && res.success) {
        const next = newVis === 'public' ? 'private' : 'public';
        btn.data('new', next);
        btn.text(newVis.charAt(0).toUpperCase() + newVis.slice(1));
      }
    });
  });

  $(savedList).on('click', '.delete-item', function (e) {
    e.preventDefault();
    if (!confirm('Delete this item?')) return;
    const wrap = $(this).closest('.ap-saved-item');
    const id = wrap.data('id');
    $.post(APPortfolio.ajaxUrl, {
      action: 'ap_delete_portfolio_item',
      nonce: APPortfolio.nonce,
      post_id: id
    }, function (res) {
      if (res && res.success) {
        wrap.remove();
      }
    });
  });
});
