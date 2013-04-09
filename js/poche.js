function toggle_favorite(element,id) {
    $(element).toggleClass('fav-off');
    $.ajax ({
        url: "process.php?action=toggle_fav",
        data:{id:id}
    });
}


function toggle_archive(id) {
    $('#entry-'+id).toggle();
    $.ajax ({
        url: "process.php?action=toggle_archive",
        data:{id:id}
    });
}
