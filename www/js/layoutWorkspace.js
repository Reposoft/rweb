// check browser
if (navigator.userAgent.indexOf("Opera") != -1) var isOpera = 1;
else if (navigator.userAgent.indexOf("MSIE") != -1) var isIE = 1;
else if (navigator.userAgent.indexOf("Mozilla") != -1) var isMoz = 1;
		
function layoutWorkspace (columns) {
	var noCols = columns;
	var noRows;
	var perc;
	var colHeight;
	var colWidth;
	var spacing = 20;
	var colDiv;
	var portlet;
	var usedPerc;
	var border; // compensate for mozilla/netscape border-inclusion in width
	var scrollbarWidth = 0; // width of scrollbar. used in resizing textareas
	height = new Array(2);
	height[0] = 17; // window top border
	height[3] = 0;
	height[4] = 0;
	
	
	// get current document size
	if (isIE) {
		border = 0;
		colHeight = document.getElementById('workspace').style.pixelHeight - 30;
		colWidth = parseInt(document.getElementById('workspace').style.pixelWidth / noCols);
	} else {
		border = 1;
		colHeight = window.innerHeight - 30;
		colWidth = parseInt(window.innerWidth / noCols);
	}
	
	for (var i = 1; i <= noCols; i++) {
		colDiv = document.getElementById("column"+i);
		portlet = colDiv.childNodes;
		noRows = portlet.length;
		usedPerc = 0;
		for (var j = 0; j < noRows; j++) {
			/* set whole portlet */
			perc = parseInt(portlet[j].getAttribute("layoutPerc")); // get portlets percentage of total height
			portlet[j].style.top = parseInt(colHeight * usedPerc / 100 + 30)+"px"; // set portlet top
			portlet[j].style.left = parseInt(spacing / i)+"px"; // set portlet left
			portlet[j].style.height = parseInt((colHeight-noRows*spacing) * perc / 100)+"px"; // set portlet height
			portlet[j].style.width = parseInt(colWidth-spacing-spacing/noCols)+"px"; // set portlet width
			/* set portlet parts */
			if (portlet[j].childNodes.length == 3) {
				if (portlet[j].childNodes[2].id == "pasteitems") height[2] = 75; // window with paste area (filearchive)
				else if (portlet[j].childNodes[2].id == "discmsgs") height[2] = parseInt(portlet[j].style.height)-65-height[0]; // discussion (lower win = main)
				else height[2] = 22; // window with bottom buttons
			}
			else if (portlet[j].childNodes.length > 3) {
				height[2] = 22; // window with bottom buttons
				for (var m = 3; m < portlet[j].childNodes.length; m++) { // resize window content
					height[m] = parseInt(portlet[j].style.height)*0.25; // set windows below 25 % each
				}
			}
			else height[2] = 0; // no third part
			height[1] = parseInt(portlet[j].style.height)-height[0]-height[2]-height[3]-height[4];
			for (var m = 0; m < portlet[j].childNodes.length; m++) { // resize window content
				portlet[j].childNodes[m].style.height = height[m]+"px"; // set scrollable area height
				portlet[j].childNodes[m].style.width = parseInt(portlet[j].style.width)-2*border+"px"; // set scrollable area width
				if (isIE) {
					if (parseInt(portlet[j].childNodes[m].style.height) < portlet[j].childNodes[m].scrollHeight) scrollbarWidth = 17; // scrollbar is visible => compensate in textarea resizing
				}
			}
			usedPerc += perc; // update used precentage
			if (isIE) { // fix explorers weird textarea width handling
				var TAs = document.getElementsByTagName("textarea");
				for (var k = 0; k < TAs.length; k++) {
					if (TAs[k].className.indexOf("dynamic") != -1) TAs[k].style.width = parseInt(portlet[j].style.width)*0.99-44-scrollbarWidth+"px";
				}
			}
		}
	}
	document.getElementById('workspace').style.visibility = "visible"; // show
}
