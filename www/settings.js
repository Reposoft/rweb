// This is the dynamic settings for the default theme, found in /style
// The other themes have a similar files under /themes/[themename]
//  and the contents under /themes/[themename]/style
// This script customizes the page behaviour for a theme. It is required by head.js.

Repos.requirePlugin('dateformat');
Repos.requirePlugin('tmt-validator');

// this theme uses transparent pngs
if (navigator.platform == "Win32" && navigator.appName == "Microsoft Internet Explorer" && window.attachEvent) {
	//window.attachEvent("onload", fnLoadPngs);
	//fnLoadPngs();
}

function fnLoadPngs() {
	var rslt = navigator.appVersion.match(/MSIE (\d+\.\d+)/, '');
	var itsAllGood = (rslt != null && Number(rslt[1]) >= 5.5);
	for (var i = document.all.length - 1, obj = null; (obj = document.all[i]); i--) {
		if (itsAllGood && obj.currentStyle.backgroundImage.match(/\.png/i) != null) {
			this.fnFixPng(obj);
		}
	}
}
	
function fnFixPng(obj) {
	var bg	= obj.currentStyle.backgroundImage;
	var src = bg.substring(5,bg.length-2);
	alert(src);
	obj.style.filter = "progid:DXImageTransform.Microsoft.AlphaImageLoader(src='" + src + "', sizingMethod='scale')";
	obj.style.backgroundImage = "url(/repos/style/blank.gif)";
}
