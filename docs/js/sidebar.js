jQuery.expr[':'].Contains = function(a, i, m) {
    return jQuery(a).text().toUpperCase().indexOf(m[3].toUpperCase()) >= 0;
};

$(function() {
    $("#sidebar-nav").accordion({
        autoHeight: false,
        navigation: true,
        collapsible: true
    }).accordion("activate", false)
            .find('a.link').unbind('click').click(
            function(ev) {
                ev.cancelBubble = true; // IE
                if (ev.stopPropagation) {
                    ev.stopPropagation(); // the rest
                }

                return true;
            }).prev().prev().remove();

    $("#sidebar-nav>h3").click(function() {
        if ($(this).attr('initialized') == 'true') return;

        $(this).next().find(".sidebar-nav-tree").treeview({
            collapsed: true,
            persist: "cookie"
        });
        $(this).attr('initialized', true);
    });
});

function tree_search(input) {
    treeview = $(input).parent().parent().next();

    // Expand all items
    treeview.find('.expandable-hitarea').click();

    // make all items visible again
    treeview.find('li:hidden').show();

    // hide all items that do not match the given search criteria
    if ($(input).val()) {
        treeview.find('li').not(':has(a:Contains(' + $(input).val() + '))').hide();
    }
}
