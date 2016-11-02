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
