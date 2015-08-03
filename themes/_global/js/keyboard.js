/** 
 * @desc Navigate with Keyboard from an article to another on an article's page
 * @param string leftURL - URL of the article on the left
 * @param string rightURL - URL of the article on the right
 */

function navigateKeyboard(leftURL, rightURL) {
  window.addEventListener("keydown", function (event) {
    var key = event.which || event.keyCode; // event.keyCode is used for IE8 and earlier versions
    switch (key) {
      case 37:
       goLeft(leftURL); // left arrow
      break;
      case 72:
        goLeft(leftURL); // h letter (vim style)
      break;

      case 39:
        goRight(rightURL); // right arrow
      break;
      case 76:
        goRight(rightURL); // l letter (vim style)
      break;
      case 8:
        window.history.back();

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


/** 
 * @desc Do actions with Keyboard on an article's page
 * @param number id - ID of the current article
 */

function actionArticle(id) {
  window.addEventListener("keydown", function (event) {
    var key = event.which || event.keyCode; // event.keyCode is used for IE8 and earlier versions
    switch (key) {
      case 46:
       deleteArticle(id); // delete key
      break;
      case 68:
        deleteArticle(id); // d key letter
      break;
      case 70:
        favoriteArticle(id); // f key letter
      break;
      case 77:
        markReadArticle(id); // m key letter
      break;
      case 78:
        markReadAndNextArticle(id); // n letter
      break;
    }

  }, false);
}

function deleteArticle(id) {
   if (id) {
          window.location = window.location.origin + window.location.pathname + '?action=delete&id=' + id;
  }
}

function favoriteArticle(id) {
   if (id) {
          window.location = window.location.origin + window.location.pathname + '?action=toggle_fav&id=' + id;
  }
}

function markReadArticle(id) {
   if (id) {
          window.location = window.location.origin + window.location.pathname + '?action=toggle_archive&id=' + id;
  }
}

function markReadAndNextArticle(id) {
   if (id) {
          window.location = window.location.origin + window.location.pathname + '?action=archive_and_next&id=' + id;
  }
}

function homeNavigation() {
  selectedArticle = $('.entrie:first');
  window.addEventListener("keydown", function (event) {
    var key = event.which || event.keyCode; // event.keyCode is used for IE8 and earlier versions
    switch (key) {
      case 37: // left arrow
       selectedArticle = goSelectPrev(selectedArticle,1);
      break;
      case 72: // h letter (vim style)
        selectedArticle = goSelectPrev(selectedArticle,1);
      break;

      case 39: // right arrow
        selectedArticle = goSelectNext(selectedArticle,1);
      break;
      case 76: // l letter (vim style)
        selectedArticle = goSelectNext(selectedArticle,1);
      break;
      case 13: // enter into article
        enterArticle(selectedArticle);
      break;
      case 74: // j letter key
        selectedArticle = goSelectNext(selectedArticle,3);
      break;
      case 40: // down arrow
        selectedArticle = goSelectNext(selectedArticle,3);
      break;
      case 75: // k letter key
        selectedArticle = goSelectNext(selectedArticle,3);
      break;
      case 38: // up arrow
        selectedArticle = goSelectNext(selectedArticle,3);
      break;
    }

  }, false);
}

function goSelectNext(selectedArticle,number) {
  if (selectedArticle.next().length) {
    selectedArticle.removeClass("eselected");
    selectedArticle = selectedArticle.next();
    selectedArticle.addClass("eselected");
  }
  return selectedArticle;
}


function goSelectPrev(selectedArticle,number) {  
  if (selectedArticle.prev().length) {
    selectedArticle.removeClass("eselected");
    selectedArticle = selectedArticle.prev();
    selectedArticle.addClass("eselected");
  }
  return selectedArticle;
}

function enterArticle(selectedArticle) {
  if (!$("#bagit").hasClass("current")) {
    window.location = selectedArticle.find('a:first').attr('href');
  }
}