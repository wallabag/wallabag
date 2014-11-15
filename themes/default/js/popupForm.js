$(document).ready(function() {

    $("#search-form").hide();
    $("#bagit-form").hide();

    //---------------------------------------------------------------------------
    // Toggle the "Search" popup in the sidebar
    //---------------------------------------------------------------------------
    function toggleSearch() {
        $("#search-form").toggle();
        $("#search").toggleClass("current");
        $("#search").toggleClass("active-current");
        $("#search-arrow").toggleClass("arrow-down");
        if ($("#search").hasClass("current")) {
            $("#content").addClass("opacity03");
        } else {
            $("#content").removeClass("opacity03");
        }
    }

    //---------------------------------------------------------------------------
    // Toggle the "Save a Link" popup in the sidebar
    //---------------------------------------------------------------------------
    function toggleBagit() {
        $("#bagit-form").toggle();
        $("#bagit").toggleClass("current");
        $("#bagit").toggleClass("active-current");
        $("#bagit-arrow").toggleClass("arrow-down");
        if ($("#bagit").hasClass("current")) {
            $("#content").addClass("opacity03");
        } else {
            $("#content").removeClass("opacity03");
        }
    }

    //---------------------------------------------------------------------------
    // Close all #links popups in the sidebar
    //---------------------------------------------------------------------------
    function closePopups() {
        $("#links .messages").hide();
        $("#links > li > a").removeClass("active-current");
        $("#links > li > a").removeClass("current");
        $("[id$=-arrow]").removeClass("arrow-down");
        $("#content").removeClass("opacity03");
    }

    $("#search").click(function(){
        closePopups();
        toggleSearch();
        $("#searchfield").focus();
    });

    $("#bagit").click(function(){
        closePopups();
        toggleBagit();
        $("#plainurl").focus();
    });

    $("#search-form-close").click(function(){
        toggleSearch();
    });

    $("#bagit-form-close").click(function(){
        toggleBagit();
    });

    // $("#").click(function(){
    //     toggleSearch();
    // });


});
