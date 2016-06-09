var $ = global.jquery = require('jquery');

$(document).ready(function () {
  var currentUrl = window.location.href;
  if (currentUrl.match('&closewin=true')) {
    window.close();
  }
});
