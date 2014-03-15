$.fn.ready(function() {

  var $listmode = $('#listmode'),
      $listentries = $("#list-entries"),
      $bagit = $('#bagit'),
      $bagitForm = $('#bagit-form');
      $bagitFormForm = $('#bagit-form-form');

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


  //send "bag it link" form request via ajax
  $bagitFormForm.submit( function(event) {
    $bagitFormForm.css("cursor", "wait");
    $("#add-link-result").empty();

    $.ajax({
        type: $bagitFormForm.attr('method'),
        url: $bagitFormForm.attr('action'),
        data: $bagitFormForm.serialize(),
        success: function(data) {
          $('#add-link-result').html("Done!");
          $('#plainurl').val('');
          $('#plainurl').blur('');
          $bagitFormForm.css("cursor", "auto");
          //setTimeout( function() { toggleSaveLinkForm(); }, 1000); //close form after 1000 delay
        },
        error: function(data) {
          $('#add-link-result').html("Failed!");
          $bagitFormForm.css("cursor", "auto");
        }
    });

    event.preventDefault();
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
    toggleSaveLinkForm($(this).attr('href'));
    event.preventDefault();
  });




});
