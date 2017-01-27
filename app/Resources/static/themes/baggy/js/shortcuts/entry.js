import Mousetrap from 'mousetrap';
import $ from 'jquery';

$(document).ready(() => {
  if ($('#article').length > 0) {
    /* Article view */
    Mousetrap.bind('o', () => {
      $('div#article_toolbar ul.links a.original')[0].click();
    });

    /* mark as favorite */
    Mousetrap.bind('f', () => {
      $('div#article_toolbar ul.links a.favorite')[0].click();
    });

    /* mark as read */
    Mousetrap.bind('a', () => {
      $('div#article_toolbar ul.links a.markasread')[0].click();
    });

    /* delete */
    Mousetrap.bind('del', () => {
      $('div#article_toolbar ul.links a.delete')[0].click();
    });
  }
});
