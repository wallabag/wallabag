wallabag.Nav = (function() {

    // function scrollPageTo(item)
    // {
    //     var clientHeight = pageYOffset + document.documentElement.clientHeight;
    //     var itemPosition = item.offsetTop + item.offsetHeight;

    //     if (clientHeight - itemPosition < 0 || clientHeight - item.offsetTop > document.documentElement.clientHeight) {
    //         window.scrollTo(0, item.offsetTop - 10);
    //     }
    // }

    // function findNextItem()
    // {
    //     var items = document.getElementsByTagName("article");

    //     if (! document.getElementById("current-item")) {

    //         items[0].id = "current-item";
    //         scrollPageTo(items[0]);
    //     }
    //     else {

    //         for (var i = 0, ilen = items.length; i < ilen; i++) {

    //             if (items[i].id == "current-item") {

    //                 items[i].id = "item-" + items[i].getAttribute("data-item-id");

    //                 if (i + 1 < ilen) {
    //                     items[i + 1].id = "current-item";
    //                     scrollPageTo(items[i + 1]);
    //                 }

    //                 break;
    //             }
    //         }
    //     }
    // }

    // function findPreviousItem()
    // {
    //     var items = document.getElementsByTagName("article");

    //     if (! document.getElementById("current-item")) {

    //         items[items.length - 1].id = "current-item";
    //         scrollPageTo(items[items.length - 1]);
    //     }
    //     else {

    //         for (var i = items.length - 1; i >= 0; i--) {

    //             if (items[i].id == "current-item") {

    //                 items[i].id = "item-" + items[i].getAttribute("data-item-id");

    //                 if (i - 1 >= 0) {
    //                     items[i - 1].id = "current-item";
    //                     scrollPageTo(items[i - 1]);
    //                 }

    //                 break;
    //             }
    //         }
    //     }
    // }

    function isListing()
    {
        if (document.getElementById("listing")) return true;
        return false;
    }

    return {
        // GetCurrentItem: function() {
        //     return document.getElementById("current-item");
        // },
        // GetCurrentItemId: function() {
        //     var item = Miniflux.Nav.GetCurrentItem();
        //     if (item) return item.getAttribute("data-item-id");
        //     return null;
        // },
        // OpenNextPage: function() {
        //     var link = document.getElementById("next-page");
        //     if (link) link.click();
        // },
        // OpenPreviousPage: function() {
        //     var link = document.getElementById("previous-page");
        //     if (link) link.click();
        // },
        // SelectNextItem: function() {
        //     var link = document.getElementById("next-item");

        //     if (link) {
        //         link.click();
        //     }
        //     else if (isListing()) {
        //         findNextItem();
        //     }
        // },
        // SelectPreviousItem: function() {
        //     var link = document.getElementById("previous-item");

        //     if (link) {
        //         link.click();
        //     }
        //     else if (isListing()) {
        //         findPreviousItem();
        //     }
        // },
        // ShowHelp: function() {
        //     open("?action=show-help", "Help", "width=320,height=450,location=no,scrollbars=no,status=no,toolbar=no");
        // },
        IsListing: isListing
    };

})();