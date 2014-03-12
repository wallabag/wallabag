$.fn.ready(function() {

  var $listmode = $('#listmode'),
      $listentries = $("#list-entries"),
      $bagit = $('#bagit'),
      $bagitForm = $('#bagit-form');

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

  /* ==========================================================================
    bag it link and close button
    ========================================================================== */

  function toggleSaveLinkForm(url) {
    $bagit.toggleClass("active-current");
    $bagitForm.toggle();
    $('#content').toggleClass("opacity03");
    if (url !== 'undefined' && url) {
      $('#plainurl').val(url);
    }
    $('#plainurl').focus();
  }

  $bagit.click(function(){
    toggleSaveLinkForm();
  });

  $("#bagit-form-close").click(function(){
    toggleSaveLinkForm();
  });

  $('#bagit-form form').submit(function(){
    toggleSaveLinkForm();
    return true;
  });

  /* ==========================================================================
    Keyboard gestion
    ========================================================================== */

  $(window).keydown(function(e){
    if ( ( e.target.tagName.toLowerCase() !== 'input' && e.keyCode == 83 ) || e.keyCode == 27 ) {
      toggleSaveLinkForm();
      return false;
    }
  });

  /* ==========================================================================
  Process all links inside an article
  ========================================================================== */

  $("article a[href^='http']").after(function() {
        return " <a href=\"" + $(this).attr('href') + "\" class=\"add-to-wallabag-link-after\" alt=\"add to wallabag\" title=\"add to wallabag\">w</a> ";
  });

  $(".add-to-wallabag-link-after").click(function(event){
    event.preventDefault();
    toggleSaveLinkForm($(this).attr('href'));
    return false;
  });




});
