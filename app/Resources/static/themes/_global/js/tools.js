import $ from 'jquery';
import './shortcuts/main';
import './shortcuts/entry';

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

function retrievePercent(id, resized) {
  if (!supportsLocalStorage()) { return false; }

  const bheight = $(document).height();
  const percent = localStorage[`wallabag.article.${id}.percent`];
  const scroll = bheight * percent;

  if (!resized) {
    $('html,body').animate({ scrollTop: scroll }, 'fast');
  }

  return true;
}

export { savePercent, retrievePercent };
