$.fn.ready(function() {

  var $listmode = $('#listmode'),
      $listentries = $("#list-entries");

  /* ==========================================================================
     Menu
     ========================================================================== */

  $("#menu").click(function(){
    $("#links").toggle();
  });

  /* ==========================================================================
     List mode or Table Mode
     ========================================================================== */

  $listmode.click(function(){
    if ( $.cookie("listmode") == 1 ) {
      // Cookie
      $.removeCookie("listmode");

      $listentries.removeClass("listmode");
      $listmode.removeClass("tablemode");
      $listmode.addClass("listmode");
    }
    else {
      // Cookie
      $.cookie("listmode", 1, {expires: 365});

      $listentries.addClass("listmode");
      $listmode.removeClass("listmode");
      $listmode.addClass("tablemode");
    }

  });

  /* ==========================================================================
     Cookie listmode
     ========================================================================== */

  if ( $.cookie("listmode") == 1 ) {
    $listentries.addClass("listmode");
    $listmode.removeClass("listmode");
    $listmode.addClass("tablemode");
  }

});
