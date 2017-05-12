import $ from 'jquery';

function initFilters() {
  // no display if filters not available
  if ($('div').is('#filters')) {
    $('#button_filters').show();
    $('.js-filters-action').sideNav({ edge: 'right' });
    $('#clear_form_filters').on('click', () => {
      $('#filters input').val('');
      $('#filters :checked').removeAttr('checked');
      return false;
    });
  }
}

function initExport() {
  // no display if export not available
  if ($('div').is('#export')) {
    $('#button_export').show();
    $('.js-export-action').sideNav({ edge: 'right' });
  }
}

function initNotifications() {
  if ($('div').is('#notifications')) {
    $('#button_notifications').show();
    $('.js-notifications-action').sideNav({ edge: 'right' });
  }
}

export { initExport, initFilters, initNotifications };
