const $ = require('jquery');

/* Allows inline call qr-code call */
import jrQrcode from 'jr-qrcode'; // eslint-disable-line

function supportsLocalStorage() {
  try {
    return 'localStorage' in window && window.localStorage !== null;
  } catch (e) {
    return false;
  }
}

function savePercent(id, percent) {
  if (!supportsLocalStorage()) { return false; }
  localStorage[`wallabag.article.${id}.percent`] = percent;
  return true;
}

function retrievePercent(id) {
  if (!supportsLocalStorage()) { return false; }

  const bheight = $(document).height();
  const percent = localStorage[`wallabag.article.${id}.percent`];
  const scroll = bheight * percent;

  $('html,body').animate({ scrollTop: scroll }, 'fast');

  return true;
}

function initFilters() {
  // no display if filters not available
  if ($('div').is('#filters')) {
    $('#button_filters').show();
    $('.button-collapse-right').sideNav({ edge: 'right' });
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
    $('.button-collapse-right').sideNav({ edge: 'right' });
  }
}

export { savePercent, retrievePercent, initFilters, initExport };
