jQuery(document).ready(function () {
  jQuery('#ap-widget-sortable').sortable({
    handle: '.ap-widget-header',
    update: function () {
      const layout = [];
      jQuery('#ap-widget-sortable .ap-widget-block').each(function () {
        layout.push({ id: jQuery(this).data('id') });
      });

      jQuery.post(ajaxurl, {
        action: 'save_dashboard_layout',
        layout,
        _ajax_nonce: apDashboard.nonce,
      });
    }
  });
});
