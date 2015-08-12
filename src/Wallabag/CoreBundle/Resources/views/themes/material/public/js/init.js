function init_filters() {
    // no display if filters not aviable
    if ($("div").is("#filters")) {
        $('#button_filters').show();
        $('.button-collapse-right').sideNav({ edge: 'right' });
        $('#filters').css({ "left": "auto" });
        $('#clean_form_filters').click(function(){
            $('#filters input').val('');
            return false;
        });
    }
}

$(document).ready(function(){
    // sideNav
    $('.button-collapse').sideNav();
    $('select').material_select();
    $('.collapsible').collapsible({
        accordion : false
    });
    init_filters();

    $('#nav-btn-add').click(function(){
       $(".nav-panel-buttom").hide(100);
       $(".nav-panel-add").show(100);
       $(".nav-panel-menu").hide(100);
       $(".nav-panels .action").hide(100);
       $(".nav-panel-menu").addClass('hidden');
       $(".nav-panels").css('background', 'white');
       $("#entry_url").focus();
       return false;
    });
    $('#nav-btn-search').click(function(){
        $(".nav-panel-buttom").hide(100);
        $(".nav-panel-search").show(100);
        $(".nav-panels .action").hide(100);
        $(".nav-panel-menu").addClass('hidden');
        $(".nav-panels").css('background', 'white');
        $("#searchfield").focus();
        return false;
    });
    $('.mdi-navigation-close').click(function(){
        $(".nav-panel-add").hide(100);
        $(".nav-panel-search").hide(100);
        $(".nav-panel-buttom").show(100);
        $(".nav-panels .action").show(100);
        $(".nav-panel-menu").removeClass('hidden');
        $(".nav-panels").css('background', 'transparent');
        return false;
    });
});
