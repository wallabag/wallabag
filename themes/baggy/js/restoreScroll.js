function supportsLocalStorage() {
    try {
        return 'localStorage' in window && window['localStorage'] !== null;
    } catch (e) {
        return false;
    }
}

function savePercent(id, percent) {
    if (!supportsLocalStorage()) { return false; }
    localStorage["poche.article." + id + ".percent"] = percent;
    return true;
}

function retrievePercent(id) {
    if (!supportsLocalStorage()) { return false; }

    var bheight = $(document).height();
    var percent = localStorage["poche.article." + id + ".percent"];
    var scroll = bheight * percent;

    $('html,body').animate({scrollTop: scroll}, 'fast');

    return true;
}