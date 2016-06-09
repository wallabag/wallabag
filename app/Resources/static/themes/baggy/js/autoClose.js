$(document).ready(function() {
	current_url = window.location.href
	if (current_url.match("&closewin=true")) {
		window.close();
	}
});
