var $ = global.jquery = require('jquery');
require('jquery.cookie');
require('jquery-ui');
var annotator = require('annotator');


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

  /* ==========================================================================
     Annotations & Remember position
     ========================================================================== */

    if ($("article").length) {
        var app = new annotator.App();

        app.include(annotator.ui.main, {
            element: document.querySelector('article')
        });

        var x = JSON.parse($('#annotationroutes').html());
        app.include(annotator.storage.http, x);

        app.start().then(function () {
             app.annotations.load({entry: x.entryId});
        });

        $(window).scroll(function(e){
            var scrollTop = $(window).scrollTop();
            var docHeight = $(document).height();
            var scrollPercent = (scrollTop) / (docHeight);
            var scrollPercentRounded = Math.round(scrollPercent*100)/100;
            savePercent(x.entryId, scrollPercentRounded);
        });

        retrievePercent(x.entryId);

        $(window).resize(function(){
            retrievePercent(x.entryId);
        });
    }
});
