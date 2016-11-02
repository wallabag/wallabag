/* Article view */
Mousetrap.bind('o', () => {
    $('div#article_toolbar ul.links li:nth-child(2) a')[0].click();
});

/* mark as favorite */
Mousetrap.bind('s', () => {
    $('div#article_toolbar ul.links li:nth-child(5) a')[0].click();
});

/* mark as read */
Mousetrap.bind('a', () => {
    $('div#article_toolbar ul.links li:nth-child(4) a')[0].click();
});

/* delete */
Mousetrap.bind('del', () => {
    $('div#article_toolbar ul.links li:nth-child(7) a')[0].click();
});
