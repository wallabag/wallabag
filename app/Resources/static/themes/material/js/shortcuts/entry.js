/* open original article */
Mousetrap.bind('o', () => {
    $('ul.side-nav li:nth-child(2) a i')[0].click();
});

/* mark as favorite */
Mousetrap.bind('s', () => {
    $('ul.side-nav li:nth-child(5) a i')[0].click();
});

/* mark as read */
Mousetrap.bind('a', () => {
    $('ul.side-nav li:nth-child(4) a i')[0].click();
});

/* delete */
Mousetrap.bind('del', () => {
    $('ul.side-nav li:nth-child(6) a i')[0].click();
});
