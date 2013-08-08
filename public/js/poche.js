function toggle_favorite(element, id) {
    $(element).toggleClass('fav-off');
    $.ajax ({
        url: "index.php?action=toggle_fav",
        data:{id:id}
    });
}

function toggle_archive(element, id, view_article) {
    $(element).toggleClass('archive-off');
    $.ajax ({
        url: "index.php?action=toggle_archive",
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

function sort_links(view, sort) {
    $.get('index.php', { view: view, sort: sort, full_head: 'no' }, function(data) {
      $('#content').html(data);
    });
}


// ---------- Swith light or dark view
function setActiveStyleSheet(title) {
	var i, a, main;
	for(i=0; (a = document.getElementsByTagName("link")[i]); i++) {
		if(a.getAttribute("rel").indexOf("style") != -1 && a.getAttribute("title")) {
			a.disabled = true;
			if(a.getAttribute("title") == title) a.disabled = false;
		}
	}
}
$('#themeswitch').click(function() {
	// we want the dark
	if ($('body').hasClass('light-style')) {
		setActiveStyleSheet('dark-style');
		$('body').addClass('dark-style');
		$('body').removeClass('light-style');
		$('#themeswitch').text('light');
	// we want the light
	} else if ($('body').hasClass('dark-style')) {
		setActiveStyleSheet('light-style');
		$('body').addClass('light-style');
		$('body').removeClass('dark-style');
		$('#themeswitch').text('dark');
	}
	return false;
});
