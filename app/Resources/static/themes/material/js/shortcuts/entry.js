import Mousetrap from 'mousetrap';
import $ from 'jquery';

$(document).ready(() => {
  if ($('#article').length > 0) {
    /* open original article */
    Mousetrap.bind('o', () => {
      $('ul.side-nav a.original i')[0].click();
    });

    /* mark as favorite */
    Mousetrap.bind('f', () => {
      $('ul.side-nav button.favorite i')[0].click();
    });

    /* mark as read */
    Mousetrap.bind('a', () => {
      $('ul.side-nav button.markasread i')[0].click();
    });

    /* delete */
    Mousetrap.bind('del', () => {
      $('ul.side-nav button.delete i')[0].click();
    });
  }
});
