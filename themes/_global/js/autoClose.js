$(document).ready(function() {
  if (location.search.match("&closewin=true")) {
		if (window.opener) {
			var msgDiv = $("div.messages");
			var msg = msgDiv.children("p").text();
			var status = msgDiv.hasClass("success") ?
				'success' : 'unknown';
			var url = $(".tool.link")[0].href;
			window.opener.postMessage({"wallabag-status": status,
				"wallabag-msg": msg,
				"wallabag-url": url }, "*");
		}
		window.close();
	}
});
