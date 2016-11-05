import Mousetrap from 'mousetrap';
import $ from 'jquery';

function toggleFocus(cardToToogleFocus) {
  if (cardToToogleFocus) {
    $(cardToToogleFocus).toggleClass('z-depth-4');
  }
}

$(document).ready(() => {
  let cardIndex = 0;
  const cardNumber = $('#content ul.data > li').length;
  let card = $('#content ul.data > li')[cardIndex];
  const pagination = $('.pagination');

  /* If we come from next page */
  if (window.location.hash === '#prev') {
    cardIndex = cardNumber - 1;
    card = $('ul.data > li')[cardIndex];
  }

  /* Focus current card */
  toggleFocus(card);

  /* Actions */
  Mousetrap.bind('g n', () => {
    $('#nav-btn-add').trigger('click');
  });

  Mousetrap.bind('esc', () => {
    $('.close').trigger('click');
  });

  /* Select right card. If there's a next page, go to next page */
  Mousetrap.bind('right', () => {
    if (cardIndex >= 0 && cardIndex < cardNumber - 1) {
      toggleFocus(card);
      cardIndex += 1;
      card = $('ul.data > li')[cardIndex];
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
      card = $('ul.data > li')[cardIndex];
      toggleFocus(card);
      return;
    }
    if (pagination.length > 0 && $(pagination).find('li.prev:not(.disabled)').length > 0 && cardIndex === 0) {
      window.location.href = `${window.location.origin + $(pagination).find('li.prev a').attr('href')}#prev`;
    }
  });

  Mousetrap.bind('enter', () => {
    window.location.href = window.location.origin + $(card).find('span.card-title a').attr('href');
  });
});
