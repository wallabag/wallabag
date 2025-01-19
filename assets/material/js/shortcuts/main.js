import Mousetrap from 'mousetrap';
import $ from 'jquery';

/* Go to */
Mousetrap.bind('g u', () => { window.location.href = Routing.generate('homepage'); });
Mousetrap.bind('g s', () => { window.location.href = Routing.generate('starred'); });
Mousetrap.bind('g r', () => { window.location.href = Routing.generate('archive'); });
Mousetrap.bind('g a', () => { window.location.href = Routing.generate('all'); });
Mousetrap.bind('g t', () => { window.location.href = Routing.generate('tag'); });
Mousetrap.bind('g c', () => { window.location.href = Routing.generate('config'); });
Mousetrap.bind('g i', () => { window.location.href = Routing.generate('import'); });
Mousetrap.bind('g d', () => { window.location.href = Routing.generate('developer'); });
Mousetrap.bind('?', () => { window.location.href = Routing.generate('howto'); });
Mousetrap.bind('g l', () => { window.location.href = Routing.generate('fos_user_security_logout'); });

function toggleFocus(cardToToogleFocus) {
  if (cardToToogleFocus) {
    $(cardToToogleFocus).toggleClass('z-depth-4');
  }
}

$(document).ready(() => {
  const cards = $('#content').find('.card');
  const cardNumber = cards.length;
  let cardIndex = 0;
  /* If we come from next page */
  if (window.location.hash === '#prev') {
    cardIndex = cardNumber - 1;
  }
  let card = cards[cardIndex];
  const pagination = $('.pagination');

  /* Show nothing on quickstart */
  if ($('#content > div.quickstart').length > 0) {
    return;
  }

  /* Show nothing on login/register page */
  if ($('#username').length > 0 || $('#fos_user_registration_form_username').length > 0) {
    return;
  }

  /* Show nothing on login/register page */
  if ($('#username').length > 0 || $('#fos_user_registration_form_username').length > 0) {
    return;
  }

  /* Focus current card */
  toggleFocus(card);

  /* Actions */
  Mousetrap.bind('g n', () => {
    $('#nav-btn-add').trigger('click');
    return false;
  });

  Mousetrap.bind('s', () => {
    $('#nav-btn-search').trigger('click');
    return false;
  });

  Mousetrap.bind('esc', () => {
    $('.close').trigger('click');
  });

  /* Select right card. If there's a next page, go to next page */
  Mousetrap.bind('right', () => {
    if (cardIndex >= 0 && cardIndex < cardNumber - 1) {
      toggleFocus(card);
      cardIndex += 1;
      card = cards[cardIndex];
      toggleFocus(card);
      return;
    }
    if (pagination.length > 0 && pagination.find('li.next:not(.disabled)').length > 0 && cardIndex === cardNumber - 1) {
      window.location.href = window.location.origin + $(pagination).find('li.next a').attr('href');
    }
  });

  /* Select previous card. If there's a previous page, go to next page */
  Mousetrap.bind('left', () => {
    if (cardIndex > 0 && cardIndex < cardNumber) {
      toggleFocus(card);
      cardIndex -= 1;
      card = cards[cardIndex];
      toggleFocus(card);
      return;
    }
    if (pagination.length > 0 && $(pagination).find('li.prev:not(.disabled)').length > 0 && cardIndex === 0) {
      window.location.href = `${window.location.origin + $(pagination).find('li.prev a').attr('href')}#prev`;
    }
  });

  Mousetrap.bind('enter', () => {
    if (typeof card !== 'object') {
      return;
    }

    const url = $(card).find('.card-title a').attr('href');
    if (typeof url === 'string' && url.length > 0) {
      window.location.href = window.location.origin + url;
    }
  });
});
