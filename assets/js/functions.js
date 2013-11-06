function toggleCheckboxes(name) {
    var inputlist = document.getElementsByName(name);
    for (i = 0; i < inputlist.length; i++) {
    if ( inputlist[i].getAttribute("type") == 'checkbox' ) {    // look only at input elements that are checkboxes
            if (inputlist[i].checked)   inputlist[i].checked = false
            else inputlist[i].checked = true;
        }
    }
}