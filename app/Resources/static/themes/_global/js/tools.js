import $ from 'jquery';
import './shortcuts/main';
import './shortcuts/entry';

/* Allows inline call qr-code call */
import jrQrcode from 'jr-qrcode'; // eslint-disable-line

function savePercent(id, percent) {
  fetch(`/progress/${id}/${percent * 100}`, { credentials: 'include' });
}

function retrievePercent() {
  const percent = $('#article').attr('data-progress');
  console.log(percent);
  const scroll = $(document).height() * percent;

  $('html,body').animate({ scrollTop: scroll }, 'fast');
}

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

function throttle(callback, delay) {
  let last;
  let timer;
  return function () {
    const context = this;
    const now = new Date();
    const args = arguments;
    if (last && now < last + delay) {
      // le délai n'est pas écoulé on reset le timer
      clearTimeout(timer);
      timer = setTimeout(function () {
        last = now;
        callback.apply(context, args);
      }, delay);
    } else {
      last = now;
      callback.apply(context, args);
    }
  };
}

export { savePercent, retrievePercent, initFilters, initExport, throttle };
