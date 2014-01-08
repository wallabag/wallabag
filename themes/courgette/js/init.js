$.fn.ready(function () {
  $('#menu').on('click', function(){
    $('body').toggleClass('menuOpen');
    $('#menuContainer, #article_toolbar').toggleClass('open');
  });
})