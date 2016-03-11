var $ = global.jquery = require('jquery');
require('jquery.cookie');
require('jquery-ui');

$.fn.ready(function() {

  var $listmode = $('#listmode'),
      $listentries = $("#list-entries");

  /* ==========================================================================
     Menu
     ========================================================================== */

  $("#menu").click(function(){
    $("#links").toggleClass('menu--open');
    if ($('#content').hasClass('opacity03')) {
        $('#content').removeClass('opacity03');
    }
  });

  /* ==========================================================================
     List mode or Table Mode
     ========================================================================== */

  $listmode.click(function(){
    if ( jquery.cookie("listmode") == 1 ) {
      // Cookie
      $.removeCookie("listmode");

      $listentries.removeClass("listmode");
      $listmode.removeClass("tablemode");
      $listmode.addClass("listmode");
    }
    else {
      // Cookie
      jquery.cookie("listmode", 1, {expires: 365});

      $listentries.addClass("listmode");
      $listmode.removeClass("listmode");
      $listmode.addClass("tablemode");
    }

  });

  /* ==========================================================================
     Cookie listmode
     ========================================================================== */

  if ( jquery.cookie("listmode") == 1 ) {
    $listentries.addClass("listmode");
    $listmode.removeClass("listmode");
    $listmode.addClass("tablemode");
  }

  /* ==========================================================================
     Add tag panel
     ========================================================================== */


  $('#nav-btn-add-tag').on('click', function(){
       $(".nav-panel-add-tag").toggle(100);
       $(".nav-panel-menu").addClass('hidden');
       $("#tag_label").focus();
       return false;
    });

});
