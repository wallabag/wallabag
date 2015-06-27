$(document).ready(function() {
	current_url = window.location.href
	if (current_url.match("&closewin=true")) {
		if (opener) {
			var msgDiv = $("div.messages");
			var msg = msgDiv.children("p").text();
			var status = msgDiv.hasClass("success") ?
				'success' : 'unknown';
			/* TODO
			 * I would also like to send the link back
			 * but the first link e.g. $(".tool.link")[0]
			 * may not be the right way to find it:
			 * We also get a "success" when the item is
			 * already stored. In this case the item
			 * will be sorted to the top.
			 * Are there other cases where we get a
			 * success but the url is not on top?!
			*/
			var url = $(".tool.link")[0].href;
			opener.postMessage({"wallabag-status": status,
				"wallabag-msg": msg,
				"wallabag-url": url }, "*");
		}
		window.close();
	}
});
