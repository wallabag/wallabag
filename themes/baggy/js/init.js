document.addEventListener('DOMContentLoaded', function() {
  var menu = document.getElementById('menu');

  menu.addEventListener('click', function(){
    if(this.nextElementSibling.style.display === "block") {
      this.nextElementSibling.style.display = "none";
    }else {
      this.nextElementSibling.style.display = "block";  
    }
    
  });
});