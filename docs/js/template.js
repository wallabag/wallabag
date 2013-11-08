function filterElements() {
    inherited = !$('#show-inherited').hasClass('deselected');
    public = !$('#show-public').hasClass('deselected');
    protected = !$('#show-protected').hasClass('deselected');
    private = !$('#show-private').hasClass('deselected');

    $('div.public').each(function(index, val) {
        $(val).toggle(public && !($(val).hasClass('inherited_from') && !inherited));
    });
    $('div.protected').each(function(index, val) {
        $(val).toggle(protected && !($(val).hasClass('inherited_from') && !inherited));
    });
    $('div.private').each(function(index, val) {
        $(val).toggle(private && !($(val).hasClass('inherited_from') && !inherited));
    });
}

$(document).ready(function() {
    $('#show-public, #show-protected, #show-private, #show-inherited')
            .css('cursor', 'pointer')
            .click(function() {
                $(this).toggleClass('deselected');
                if ($(this).hasClass('deselected')) {
                    $(this).fadeTo('fast', '0.4');
                } else {
                    $(this).fadeTo('fast', '1.0');
                }
                filterElements();
                return false;
            });
    $('#show-protected, #show-private').click();

    $('#file-nav-box').show();

    if ($(".filetree").treeview)
    {
        $(".filetree").treeview({
            collapsed:true,
            persist:"cookie"
        });
    }

    $("#accordion").accordion({
        collapsible:true,
        autoHeight:false,
        fillSpace:true
    });

    $("#marker-accordion").accordion({
        collapsible:true,
        autoHeight:false
    });

    $(".tabs").tabs();

    jQuery('.sidebar-nav-tree').before('<div class="search-bar"> \
    <a href="#"><img src="images/collapse_all.png" title="Collapse all" alt="Collapse all" /></a> \
    <a href="#"><img src="images/expand_all.png" title="Expand all" alt="Expand all" /></a> \
    <div><input type="search" /></div> \
</div>');

    jQuery('.search-bar').find('a:eq(0)').click(function() {
        jQuery(this).parent().next().find('.collapsable-hitarea').click();
        return false;
    });
    jQuery('.search-bar').find('a:eq(1)').click(function() {
        jQuery(this).parent().next().find('.expandable-hitarea').click();
        return false;
    });
    jQuery('.search-bar input').keyup(function() {
        tree_search(this);
    });

    $('div.code-tabs').hide();
    $('a.gripper').show();
    $('div.code-tabs:empty').prevAll('a.gripper').html('');

    $('a.gripper').click(function() {
        $(this).nextAll('div.code-tabs').slideToggle();
        $(this).children('img').toggle();
        return false;
    });

    $('code.title').click(function() {
        $(this).nextAll('div.code-tabs').slideToggle();
        $(this).prev().children('img').toggle();
    });

});