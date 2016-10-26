import $ from 'jquery';
import Mousetrap from 'mousetrap';

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

/** Shortcuts **/

/* Go to */
Mousetrap.bind('g u', () => { window.location.href = Routing.generate('homepage'); });
Mousetrap.bind('g s', () => { window.location.href = Routing.generate('starred'); });
Mousetrap.bind('g r', () => { window.location.href = Routing.generate('archive'); });
Mousetrap.bind('g a', () => { window.location.href = Routing.generate('all'); });
Mousetrap.bind('g t', () => { window.location.href = Routing.generate('tag'); });
Mousetrap.bind('g c', () => { window.location.href = Routing.generate('config'); });
Mousetrap.bind('g i', () => { window.location.href = Routing.generate('import'); });
Mousetrap.bind('g d', () => { window.location.href = Routing.generate('developer'); });
Mousetrap.bind('g h', () => { window.location.href = Routing.generate('howto'); });
Mousetrap.bind('g l', () => { window.location.href = Routing.generate('logout'); });


/* Actions */
Mousetrap.bind('g n', () => {
  $('#nav-btn-add').trigger('click');
});

Mousetrap.bind('esc', () => {
  $('.close').trigger('click');
});

// Display the first element of the current view
Mousetrap.bind('right', () => {
  $('ul.data li:first-child span.dot-ellipsis a')[0].click();
});

/* Article view */
Mousetrap.bind('o', () => {
  $('ul.side-nav li:nth-child(2) a i')[0].click();
});

Mousetrap.bind('s', () => {
  $('ul.side-nav li:nth-child(5) a i')[0].click();
});

Mousetrap.bind('a', () => {
  $('ul.side-nav li:nth-child(4) a i')[0].click();
});

Mousetrap.bind('del', () => {
  $('ul.side-nav li:nth-child(6) a i')[0].click();
});
