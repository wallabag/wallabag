jQuery(function($) {

  function split( val ) {
    return val.split( /,\s*/ );
  }
  function extractLast( term ) {
    return split( term ).pop();
  }


  $("#value").bind("keydown", function(event) {
    if (event.keyCode === $.ui.keyCode.TAB && $(this).data("ui-autocomplete").menu.active) {
      event.preventDefault();
    }
  }).autocomplete({
    source : function(request, response) {
      $.getJSON("./?view=tags", {
        term : extractLast(request.term),
        //id: $(':hidden#entry_id').val()
      }, response);
    },
    search : function() {
      // custom minLength
      var term = extractLast(this.value);
      if (term.length < 1) {
        return false;
      }
    },
    focus : function() {
      // prevent value inserted on focus
      return false;
    },
    select : function(event, ui) {
      var terms = split(this.value);
      // remove the current input
      terms.pop();
      // add the selected item
      terms.push(ui.item.value);
      // add placeholder to get the comma-and-space at the end
      terms.push("");
      this.value = terms.join(", ");
      return false;
    }
  });

  $('.suggestedtag').click(function(){
    var input = $("#value");
    var value = input.val();
    var tag = $(this).text();
    var terms = value.split(','); // tags into the <input>
    $(".alreadytagged").each(function(index) {
      terms.push($(this).text() );
    });
    if (jQuery.inArray(tag, terms) == -1 ) { // if the tag hasn't already been added
      value += tag + ",";
      input.val(value);
    }
    input.focus();
    input[0].selectionStart = input[0].selectionEnd = input.val().length;

  });


});
