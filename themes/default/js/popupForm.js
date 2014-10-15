$(document).ready(function() {

    $("#search-form").hide();

    function closeSearch() {
        $("#search-form").toggle();
        $("#search").toggleClass("current");
        $("#search-arrow").toggleClass("arrow-down");
    }

    $("#search").click(function(){
        closeSearch();
        // if other popup is already shown
        if ($("#bagit-form").length != 0) {
            $("#bagit").removeClass("active-current");
            $('#content').removeClass("opacity03");
            $("#bagit").removeClass("current");
            $("#bagit-arrow").removeClass("arrow-down");
            $("#bagit-form").hide();
        }
        $('#searchfield').focus();
    });

    $("#search-form-close").click(function(){
        closeSearch();
    });


});