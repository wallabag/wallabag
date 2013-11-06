
document.onkeypress = function(e) {
	switch (e.keyCode || e.which) {
		case 81: // Q
        case 113: // q
            window.close();
            break;
    }
};
