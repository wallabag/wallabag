/* Use this script if you need to support IE 7 and IE 6. */

window.onload = function() {
	function addIcon(el, entity) {
		var html = el.innerHTML;
		el.innerHTML = '<span style="font-family: \'linecons\'">' + entity + '</span>' + html;
	}
	var icons = {
			'li_heart' : '&#xe000;',
			'li_cloud' : '&#xe001;',
			'li_star' : '&#xe002;',
			'li_tv' : '&#xe003;',
			'li_sound' : '&#xe004;',
			'li_video' : '&#xe005;',
			'li_trash' : '&#xe006;',
			'li_user' : '&#xe007;',
			'li_key' : '&#xe008;',
			'li_search' : '&#xe009;',
			'li_settings' : '&#xe00a;',
			'li_camera' : '&#xe00b;',
			'li_tag' : '&#xe00c;',
			'li_lock' : '&#xe00d;',
			'li_bulb' : '&#xe00e;',
			'li_pen' : '&#xe00f;',
			'li_diamond' : '&#xe010;',
			'li_display' : '&#xe011;',
			'li_location' : '&#xe012;',
			'li_eye' : '&#xe013;',
			'li_bubble' : '&#xe014;',
			'li_stack' : '&#xe015;',
			'li_cup' : '&#xe016;',
			'li_phone' : '&#xe017;',
			'li_news' : '&#xe018;',
			'li_mail' : '&#xe019;',
			'li_like' : '&#xe01a;',
			'li_photo' : '&#xe01b;',
			'li_note' : '&#xe01c;',
			'li_clock' : '&#xe01d;',
			'li_paperplane' : '&#xe01e;',
			'li_params' : '&#xe01f;',
			'li_banknote' : '&#xe020;',
			'li_data' : '&#xe021;',
			'li_music' : '&#xe022;',
			'li_megaphone' : '&#xe023;',
			'li_study' : '&#xe024;',
			'li_lab' : '&#xe025;',
			'li_food' : '&#xe026;',
			'li_t-shirt' : '&#xe027;',
			'li_fire' : '&#xe028;',
			'li_clip' : '&#xe029;',
			'li_shop' : '&#xe02a;',
			'li_calendar' : '&#xe02b;',
			'li_vallet' : '&#xe02c;',
			'li_vynil' : '&#xe02d;',
			'li_truck' : '&#xe02e;',
			'li_world' : '&#xe02f;'
		},
		els = document.getElementsByTagName('*'),
		i, attr, html, c, el;
	for (i = 0; i < els.length; i += 1) {
		el = els[i];
		attr = el.getAttribute('data-icon');
		if (attr) {
			addIcon(el, attr);
		}
		c = el.className;
		c = c.match(/li_[^\s'"]+/);
		if (c && icons[c[0]]) {
			addIcon(el, icons[c[0]]);
		}
	}
};