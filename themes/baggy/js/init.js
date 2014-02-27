$(document).ready(function() {

  $("#menu").click(function(){
    $("#links").toggle();
  });


  $("#listmode").click(function(){
    if ( $.cookie("listmode") == 1 ) {
      $(".entrie").css("width", "");
      $(".entrie").css("margin-left", "");

      $.removeCookie("listmode");
      $("#listmode").removeClass("tablemode");
      $("#listmode").addClass("listmode");
    }
    else {
      $.cookie("listmode", 1, {expires: 365});

      $(".entrie").css("width", "100%");
      $(".entrie").css("margin-left", "0");
      $("#listmode").removeClass("listmode");
      $("#listmode").addClass("tablemode");
    }

  });

  if ( $.cookie("listmode") == 1 ) {
    $(".entrie").css("width", "100%");
    $(".entrie").css("margin-left", "0");
    $("#listmode").removeClass("listmode");
    $("#listmode").addClass("tablemode");
  }


});
