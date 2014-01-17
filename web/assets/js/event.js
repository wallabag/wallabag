poche.Event = (function() {

    var queue = [];

    return {
        ListenMouseEvents: function() {

            document.onclick = function(e) {

                var action = e.target.getAttribute("data-action");

                if (action) {

                    switch (action) {
                        case 'mark_entry_read':
                            e.preventDefault();
                            poche.Item.MarkAsRead(e.target.getAttribute("data-entry-id"));
                            break;
                        case 'mark_entry_unread':
                            e.preventDefault();
                            poche.Item.MarkAsUnread(e.target.getAttribute("data-entry-id"));
                            break;
                        case 'unstar_entry':
                            e.preventDefault();
                            poche.Item.MarkAsUnstarred(e.target.getAttribute("data-entry-id"));
                            break;
                        case 'star_entry':
                            e.preventDefault();
                            poche.Item.MarkAsStarred(e.target.getAttribute("data-entry-id"));
                            break;
                        // case 'original-link':
                        //     poche.Item.OpenOriginal(e.target.getAttribute("data-entry-id"));
                        //     break;
                        // case 'mark-all-read':
                        //     e.preventDefault();
                        //     poche.Item.MarkListingAsRead("?action=unread");
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
                            poche.Item.DownloadContent(poche.Nav.GetCurrentItemId());
                            break;
                        case 112: // p
                        case 107: // k
                            poche.Nav.SelectPreviousItem();
                            break;
                        case 110: // n
                        case 106: // j
                            poche.Nav.SelectNextItem();
                            break;
                        case 118: // v
                            poche.Item.OpenOriginal(poche.Nav.GetCurrentItemId());
                            break;
                        case 111: // o
                            poche.Item.Show(poche.Nav.GetCurrentItemId());
                            break;
                        case 109: // m
                            poche.Item.SwitchStatus(poche.Nav.GetCurrentItem());
                            break;
                        case 102: // f
                            poche.Item.SwitchBookmark(poche.Nav.GetCurrentItem());
                            break;
                        case 104: // h
                            poche.Nav.OpenPreviousPage();
                            break
                        case 108: // l
                            poche.Nav.OpenNextPage();
                            break;
                        case 63: // ?
                            poche.Nav.ShowHelp();
                            break;
                    }
                }
            }
        }*/
    };

})();