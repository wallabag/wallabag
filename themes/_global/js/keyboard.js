function navigateKeyboard(leftURL, rightURL) {
  window.addEventListener("keypress", function (event) {
    var key = event.which || event.keyCode; // event.keyCode is used for IE8 and earlier versions
    console.log("key pressed : " + key);
    switch (key) {
      case 37:
        // left arrow
        if (leftURL != "?view=view&id=") {
          window.location = window.location.origin + window.location.pathname + leftURL;
        }
      break;
        /*
        case 38:
        // top arrow
        window.location = window.location.origin + window.location.pathname + window.location.search + "#top";
        break;
        */
      case 39:
        //right arrow
        if (rightURL != "?view=view&id=") {
        window.location = window.location.origin + window.location.pathname + rightURL;
        }
      break;
    }

  }, false);
}