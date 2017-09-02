/* To avoid CSS expressions while still supporting IE 7 and IE 6, use this script */
/* The script tag referencing this file must be placed before the ending body tag. */

/* Use conditional comments in order to target IE 7 and older:
	<!--[if lt IE 8]><!-->
	<script src="ie7/ie7.js"></script>
	<!--<![endif]-->
*/

(function() {
	function addIcon(el, entity) {
		var html = el.innerHTML;
		el.innerHTML = '<span style="font-family: \'icomoon\'">' + entity + '</span>' + html;
	}
	var icons = {
		'icon-update': '&#xe930;',
		'icon-help': '&#xe92e;',
		'icon-table': '&#xe921;',
		'icon-file-binary': '&#xe918;',
		'icon-copy': '&#xe917;',
		'icon-file': '&#xe90c;',
		'icon-back': '&#xe90a;',
		'icon-trash': '&#xe90b;',
		'icon-folder-download': '&#xe909;',
		'icon-folder-upload': '&#xe907;',
		'icon-folder-create': '&#xe908;',
		'icon-mysql': '&#xe903;',
		'icon-pencil': '&#xe920;',
		'icon-users': '&#xe972;',
		'icon-spinner': '&#xe980;',
		'icon-view': '&#xe9ce;',
		'icon-terminal': '&#xea81;',
		'icon-plus': '&#xe922;',
		'icon-check': '&#xe92f;',
		'icon-feedback': '&#xe92d;',
		'icon-upload': '&#xe92c;',
		'icon-column': '&#xe924;',
		'icon-search': '&#xe912;',
		'icon-key': '&#xe911;',
		'icon-archive': '&#xe913;',
		'icon-download': '&#xe914;',
		'icon-edit': '&#xe915;',
		'icon-file-code': '&#xe919;',
		'icon-file-video': '&#xe91a;',
		'icon-file-audio': '&#xe91b;',
		'icon-file-archive': '&#xe91c;',
		'icon-file-image': '&#xe91d;',
		'icon-file-document': '&#xe945;',
		'icon-file-text': '&#xe946;',
		'icon-exit': '&#xe916;',
		'icon-folder': '&#xe90d;',
		'icon-folder-open-o': '&#xe90e;',
		'icon-folder-o': '&#xe90f;',
		'icon-folder-open': '&#xe910;',
		'icon-home': '&#xe906;',
		'icon-help2': '&#xe904;',
		'icon-about': '&#xe905;',
		'icon-config': '&#xe902;',
		'icon-close': '&#xe900;',
		'icon-user': '&#xe901;',
		'icon-backup': '&#xe92a;',
		'icon-restore': '&#xe92b;',
		'icon-storage': '&#xe929;',
		'icon-checkbox_unchecked': '&#xe927;',
		'icon-checkbox_checked': '&#xe928;',
		'icon-radio_checked': '&#xe925;',
		'icon-radio_unchecked': '&#xe926;',
		'icon-theme': '&#xe91e;',
		'icon-select-arrows': '&#xe923;',
		'icon-cord': '&#xe91f;',
		'0': 0
		},
		els = document.getElementsByTagName('*'),
		i, c, el;
	for (i = 0; ; i += 1) {
		el = els[i];
		if(!el) {
			break;
		}
		c = el.className;
		c = c.match(/icon-[^\s'"]+/);
		if (c && icons[c[0]]) {
			addIcon(el, icons[c[0]]);
		}
	}
}());
