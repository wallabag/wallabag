function init_filters() {
    // no display if filters not aviable
    if ($("div").is("#filters")) {
        $('#button_filters').show();
        $('.button-collapse-right').sideNav({ edge: 'right' });
        $('#clean_form_filters').on('click', function(){
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

    $('#nav-btn-add').on('click', function(){
       $(".nav-panel-buttom").hide(100);
       $(".nav-panel-add").show(100);
       $(".nav-panels .action").hide(100);
       $(".nav-panel-menu").addClass('hidden');
       $(".nav-panels").css('background', 'white');
       $("#entry_url").focus();
       return false;
    });
    $('#nav-btn-search').on('click', function(){
        $(".nav-panel-buttom").hide(100);
        $(".nav-panel-search").show(100);
        $(".nav-panels .action").hide(100);
        $(".nav-panel-menu").addClass('hidden');
        $(".nav-panels").css('background', 'white');
        $("#searchfield").focus();
        return false;
    });
    $('.mdi-navigation-close').on('click', function(){
        $(".nav-panel-add").hide(100);
        $(".nav-panel-search").hide(100);
        $(".nav-panel-buttom").show(100);
        $(".nav-panels .action").show(100);
        $(".nav-panel-menu").removeClass('hidden');
        $(".nav-panels").css('background', 'transparent');
        return false;
    });
    $(window).scroll(function () {
        var s = $(window).scrollTop(),
        d = $(document).height(),
        c = $(window).height();
        var scrollPercent = (s / (d-c)) * 100;
        $(".progress .determinate").css('width', scrollPercent+'%');
    });
});
