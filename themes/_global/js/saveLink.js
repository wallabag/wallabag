$.fn.ready(function() {

  var $bagit = $('#bagit'),
      $bagitForm = $('#bagit-form'),
      $bagitFormForm = $('#bagit-form-form');

$("#tags2add").hide();

  /* ==========================================================================
   bag it link and close button
   ========================================================================== */

  function toggleSaveLinkForm(url, event) {
    $("#add-link-result").empty();

    $bagit.toggleClass("active-current");

    //only if bag-it link is not presented on page
    if ( $bagit.length === 0 ) {
      if ( event !== 'undefined' && event ) {
        $bagitForm.css( {position:"absolute", top:event.pageY, left:event.pageX-200});
      }
      else {
        $bagitForm.css( {position:"relative", top:"auto", left:"auto"});
      }
    }

    if ($("#search-form").length != 0) {
    	$("#search").removeClass("current");
    	$("#search-arrow").removeClass("arrow-down");
    	$("#search-form").hide();
    }
    $bagitForm.toggle();
    $('#content').toggleClass("opacity03");
    if (url !== 'undefined' && url) {
      $('#plainurl').val(url);
    }
    $('#plainurl').focus();
  }

	//---------------------------------------------------------------------------
	// These two functions are now taken care of in popupForm.js
	//---------------------------------------------------------------------------

  // $bagit.click(function(){
  //   $bagit.toggleClass("current");
  //   $("#bagit-arrow").toggleClass("arrow-down");
  //   toggleSaveLinkForm();
  // });

  // $("#bagit-form-close").click(function(){
  //   $bagit.removeClass("current");
  //   $("#bagit-arrow").removeClass("arrow-down");
  //   toggleSaveLinkForm();
  // });


  //send "bag it link" form request via ajax
  $bagitFormForm.submit( function(event) {
    $("body").css("cursor", "wait");
    $("#add-link-result").empty();

    $.ajax({
      type: $bagitFormForm.attr('method'),
      url: $bagitFormForm.attr('action'),
      data: $bagitFormForm.serialize(),
      success: function(data) {
        $('#add-link-result').html("Done!");
        $('#plainurl').val('');
        $('#plainurl').blur('');
        $("body").css("cursor", "auto");
        //setTimeout( function() { toggleSaveLinkForm(); }, 1000); //close form after 1000 delay
      },
      error: function(data) {
        $('#add-link-result').html("Failed!");
        $("body").css("cursor", "auto");
      }
    });

    event.preventDefault();
  });

$('#showtaginput').click(function(){
  $('#tags2add').toggle();
  $('#plainurl').toggle();
  $('#showtaginput').toggleClass('icon-tags');
  $('#showtaginput').toggleClass('icon-check');
});


  /* ==========================================================================
   Keyboard gestion
   ========================================================================== */

  $(window).keydown(function(e){
    if ( ( e.target.tagName.toLowerCase() !== 'input' && e.keyCode == 83 ) || (e.keyCode == 27 && $bagitForm.is(':visible') ) ) {
      $bagit.removeClass("current");
      $("#bagit-arrow").removeClass("arrow-down");
      toggleSaveLinkForm();
      return false;
    }
  });

  /* ==========================================================================
   Process all links inside an article
   ========================================================================== */

  $("article a[href^='http']").after(function() {
    return " <a href=\"" + $(this).attr('href') + "\" class=\"add-to-wallabag-link-after\" alt=\"add to wallabag\" title=\"add to wallabag\"></a> ";
  });

  $(".add-to-wallabag-link-after").click(function(event){
    toggleSaveLinkForm($(this).attr('href'), event);
    event.preventDefault();
  });

});


