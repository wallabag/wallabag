$(function(){
     //---------------------------------------------------------------------------
     // Show the close icon when the user hover over a message 
     //---------------------------------------------------------------------------
     // $('.messages').on('mouseenter', function(){
     //      $(this).find('a.closeMessage').stop(true, true).show();
     // }).on('mouseleave', function(){
     //      $(this).find('a.closeMessage').stop(true, true).hide();
     // });
     //---------------------------------------------------------------------------
     // Close the message box when the user clicks the close icon
     //---------------------------------------------------------------------------
     $('a.closeMessage').on('click', function(){
          $(this).parents('div.messages').slideUp(300, function(){ $(this).remove(); });
          return false;
     });
});