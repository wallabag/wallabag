wallabag.Event = (function() {

    var queue = [];

    return {
        ListenMouseEvents: function() {

            document.onclick = function(e) {

                var action = e.target.getAttribute("data-action");

                if (action) {

                    switch (action) {
                        case 'mark_entry_read':
                            e.preventDefault();
                            wallabag.Item.MarkAsRead(e.target.getAttribute("data-entry-id"));
                            break;
                        case 'mark_entry_unread':
                            e.preventDefault();
                            wallabag.Item.MarkAsUnread(e.target.getAttribute("data-entry-id"));
                            break;
                        case 'unstar_entry':
                            e.preventDefault();
                            wallabag.Item.MarkAsUnstarred(e.target.getAttribute("data-entry-id"));
                            break;
                        case 'star_entry':
                            e.preventDefault();
                            wallabag.Item.MarkAsStarred(e.target.getAttribute("data-entry-id"));
                            break;
                        // case 'original-link':
                        //     wallabag.Item.OpenOriginal(e.target.getAttribute("data-entry-id"));
                        //     break;
                        // case 'mark-all-read':
                        //     e.preventDefault();
                        //     wallabag.Item.MarkListingAsRead("?action=unread");
                        //     break;
                    }
                }
            };
        }/*,
        ListenKeyboardEvents: function() {

            document.onkeypress = function(e) {

                queue.push(e.keyCode || e.which);

                if (queue[0] == 103) { // g

                    switch (queue[1]) {
                        case undefined:
                            break;
                        case 117: // u
                            window.location.href = "?action=unread";
                            queue = [];
                            break;
                        case 98: // b
                            window.location.href = "?action=bookmarks";
                            queue = [];
                            break;
                        case 104: // h
                            window.location.href = "?action=history";
                            queue = [];
                            break;
                        case 115: // s
                            window.location.href = "?action=feeds";
                            queue = [];
                            break;
                        case 112: // p
                            window.location.href = "?action=config";
                            queue = [];
                            break;
                        default:
                            queue = [];
                            break;
                    }
                }
                else {

                    queue = [];

                    switch (e.keyCode || e.which) {
                        case 100: // d
                            wallabag.Item.DownloadContent(wallabag.Nav.GetCurrentItemId());
                            break;
                        case 112: // p
                        case 107: // k
                            wallabag.Nav.SelectPreviousItem();
                            break;
                        case 110: // n
                        case 106: // j
                            wallabag.Nav.SelectNextItem();
                            break;
                        case 118: // v
                            wallabag.Item.OpenOriginal(wallabag.Nav.GetCurrentItemId());
                            break;
                        case 111: // o
                            wallabag.Item.Show(wallabag.Nav.GetCurrentItemId());
                            break;
                        case 109: // m
                            wallabag.Item.SwitchStatus(wallabag.Nav.GetCurrentItem());
                            break;
                        case 102: // f
                            wallabag.Item.SwitchBookmark(wallabag.Nav.GetCurrentItem());
                            break;
                        case 104: // h
                            wallabag.Nav.OpenPreviousPage();
                            break
                        case 108: // l
                            wallabag.Nav.OpenNextPage();
                            break;
                        case 63: // ?
                            wallabag.Nav.ShowHelp();
                            break;
                    }
                }
            }
        }*/
    };

})();