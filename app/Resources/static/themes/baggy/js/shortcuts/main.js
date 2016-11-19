$(document).ready(() => {
  Mousetrap.bind('s', () => {
    $('#search').trigger('click');
    $('#search_entry_term').focus();
    return false;
  });
});
