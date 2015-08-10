$(document).ready(function(){
    // sideNav
    $('.button-collapse').sideNav();
    $('select').material_select();
    $('.collapsible').collapsible({
        accordion : false
    });

    $('#nav-btn-add').click(function(){
       $(".nav-panel-buttom").hide(100);
       $(".nav-panel-add").show(100);
       $(".nav-panel-menu").hide(100);
       return false;
    });
    $('#nav-btn-search').click(function(){
        $(".nav-panel-buttom").hide(100);
        $(".nav-panel-search").show(100);
        $(".nav-panel-menu").addClass('hidden');
        return false;
    });
    $('.mdi-navigation-close').click(function(){
        $(".nav-panel-add").hide(100);
        $(".nav-panel-search").hide(100);
        $(".nav-panel-buttom").show(100);
        $(".nav-panel-menu").removeClass('hidden');
        return false;
    });
});
