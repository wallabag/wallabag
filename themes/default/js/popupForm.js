$(document).ready(function() {

    $("#search-form").hide();

    function closeSearch() {
        $("#search-form").toggle();
        $("#search").toggleClass("current");
        $("#search-arrow").toggleClass("arrow-down");
    }

    $("#search").click(function(){
        closeSearch();
    });

    $("#search-form-close").click(function(){
        closeSearch();
    });


});