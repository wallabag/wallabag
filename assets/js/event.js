poche.Event = (function() {

    var queue = [];

    return {
        ListenMouseEvents: function() {

            document.onclick = function(e) {

                var action = e.target.getAttribute("data-action");

                if (action) {

                    switch (action) {
                        case 'switch-status':
                            e.preventDefault;
                            poche.Item.SwitchStatus(poche.Item.Get(e.target.getAttribute("data-item-id")));
                        case 'mark-read':
                            e.preventDefault();
                            poche.Item.MarkAsRead(e.target.getAttribute("data-item-id"));
                            break;
                        case 'mark-unread':
                            e.preventDefault();
                            poche.Item.MarkAsUnread(e.target.getAttribute("data-item-id"));
                            break;
                        case 'bookmark':
                            e.preventDefault();
                            poche.Item.SwitchBookmark(poche.Item.Get(e.target.getAttribute("data-item-id")));
                            break;
                        case 'original-link':
                            poche.Item.OpenOriginal(e.target.getAttribute("data-item-id"));
                            break;
                        case 'mark-all-read':
                            e.preventDefault();
                            poche.Item.MarkListingAsRead("?action=unread");
                            break;
                        case 'mozilla-login':
                            e.preventDefault();
                            poche.App.MozillaAuth("mozilla-auth");
                            break;
                        case 'mozilla-link':
                            e.preventDefault();
                            poche.App.MozillaAuth("mozilla-link");
                            break;
                    }
                }
            };
        },
        ListenKeyboardEvents: function() {

            document.onkeypress = function(e) {

                queue.push(e.keyCode || e.which);

                if (queue[0] == 103) { // g

                    switch (queue[1]) {
                        case undefined:
                            break;
                        case 115: // s
                            window.location.href = "?action=search";
                            queue = [];
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
                        case 97: // a
                            window.location.href = "?action=add";
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
        }
    };

})();