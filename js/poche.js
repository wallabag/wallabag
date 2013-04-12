function toggle_favorite(element, id) {
    $(element).toggleClass('fav-off');
    $.ajax ({
        url: "process.php?action=toggle_fav",
        data:{id:id}
    });
}

function toggle_archive(element, id, view_article) {
    $(element).toggleClass('archive-off');
    $.ajax ({
        url: "process.php?action=toggle_archive",
        data:{id:id}
    });
    var obj = $('#entry-'+id);

    // on vient de la vue de l'article, donc pas de gestion de grille
    if (view_article != 1) {
        $('#content').masonry('remove',obj);
        $('#content').masonry('reloadItems');
        $('#content').masonry('reload');
    }
}