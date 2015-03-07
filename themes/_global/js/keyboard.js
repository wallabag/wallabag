function navigateKeyboard(leftURL, rightURL) {
  window.addEventListener("keypress", function (event) {
    var key = event.which || event.keyCode; // event.keyCode is used for IE8 and earlier versions
    switch (key) {
      case 37:
       goLeft(leftURL);
      break;

      case 39:
        goRight(rightURL);
      break;
    }

  }, false);
}

function goLeft(leftURL) {
   if (leftURL != "?view=view&id=") {
          window.location = window.location.origin + window.location.pathname + leftURL;
  }
}

function goRight(rightURL) {
  if (rightURL != "?view=view&id=") {
        window.location = window.location.origin + window.location.pathname + rightURL;
  }
}