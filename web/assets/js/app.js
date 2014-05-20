var wallabag = {};

wallabag.App = (function() {

    return {
        Run: function() {
            // wallabag.Event.ListenKeyboardEvents();
            wallabag.Event.ListenMouseEvents();
        },
    }

})();
