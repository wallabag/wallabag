import Mousetrap from 'mousetrap';
import $ from 'jquery';

$(document).ready(() => {
  if ($('#article').length > 0) {
    /* open original article */
    Mousetrap.bind('o', () => {
      $('ul.sidenav a.original i')[0].click();
    });

    /* mark as favorite */
    Mousetrap.bind('f', () => {
      $('ul.sidenav a.favorite i')[0].click();
    });

    /* mark as read */
    Mousetrap.bind('a', () => {
      $('ul.sidenav a.markasread i')[0].click();
    });

    /* delete */
    Mousetrap.bind('del', () => {
      $('ul.sidenav a.delete i')[0].click();
    });
  }
});
