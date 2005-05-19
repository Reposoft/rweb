// check browser
if (navigator.userAgent.indexOf("Opera") != -1) var isOpera = 1;
else if (navigator.userAgent.indexOf("MSIE") != -1) var isIE = 1;
else if (navigator.userAgent.indexOf("Mozilla") != -1) var isMoz = 1;
		
function layoutWorkspaceCalendar (part) {
	var tool = part;
	var colHeight;
	var colWidth;
	var spacing = 20;
	var colDiv;
	var W3Cborder; // compensate for mozilla/netscape border-inclusion in width
	
	// get current document size
	if (isIE) {
		W3Cborder = 0;
		colHeight = document.getElementById('workspace').style.pixelHeight - 30;
		colWidth = parseInt(document.getElementById('workspace').style.pixelWidth);
	} else {
		W3Cborder = 1;
		colHeight = window.innerHeight - 30;
		colWidth = parseInt(window.innerWidth);
	}
	
	if (tool == "main") {
		var calendarMiniMonths = document.getElementById('calendarMiniMonths');
		var calendarInvitations = document.getElementById('calendarInvitations');
		var calendarTimeManager = document.getElementById('calendarTimeManager');
		var miniCalWidth = parseInt(calendarMiniMonths.style.width);
		var miniCalHeight = parseInt(calendarMiniMonths.style.height);

		/* miniMonths */
		calendarMiniMonths.style.top = 30+"px"; // set portlet top
		calendarMiniMonths.style.left = spacing+"px"; // set portlet left
		calendarMiniMonths.childNodes[1].style.height = parseInt(calendarMiniMonths.style.height)-18+"px"; // set portlet height
		if (isMoz) {
			calendarMiniMonths.childNodes[1].childNodes[0].style.width = parseInt(calendarMiniMonths.style.width)*0.5-3-10+"px"; // set portlet height
			calendarMiniMonths.childNodes[1].childNodes[1].style.width = parseInt(calendarMiniMonths.style.width)*0.5-2-10+"px"; // set portlet height
		}

		/* meeting invitations */
		calendarInvitations.style.top = 30+"px"; // set portlet top
		calendarInvitations.style.left = 2*spacing+miniCalWidth+"px"; // set portlet left
		calendarInvitations.style.height = miniCalHeight+"px"; // set portlet left
		calendarInvitations.style.width = parseInt(colWidth-3*spacing-miniCalWidth)+"px"; // set portlet width
		calendarInvitations.childNodes[1].style.height = parseInt(calendarInvitations.style.height)-17+"px"; // set portlet height
		
		/* time manager */
		calendarTimeManager.style.top = 30+miniCalHeight+spacing+"px"; // set portlet top
		calendarTimeManager.style.left = spacing+"px"; // set portlet left
		calendarTimeManager.style.height = parseInt(colHeight-miniCalHeight-2*spacing)+"px"; // set portlet height
		calendarTimeManager.style.width = parseInt(colWidth-2*spacing)+"px"; // set portlet width
		//calendarTimeManager.childNodes[1].style.height = parseInt(calendarTimeManager.style.height)-17+"px"; // set portlet height

	} else if (tool == "weekOrDayView") {
		colDiv = document.getElementById("column1");
		colDiv.childNodes[0].style.width = parseInt(colWidth)-2*2*W3Cborder+"px";
		colDiv.childNodes[1].style.width = parseInt(colWidth)-2*2*W3Cborder+"px";
		colDiv.childNodes[2].style.height = parseInt(colHeight+30-17-15)-2*2*W3Cborder+"px";
		colDiv.childNodes[2].style.width = parseInt(colWidth)-2*2*W3Cborder+"px";
		
		var colCount;
		var noVisibleDays;
		
		var TMweekdays = colDiv.childNodes[1].childNodes;
		colCount = 1;
		noVisibleDays = TMweekdays.length-2; // number of days (7 for week, or 1 for day)
		for (var x = 1; x < TMweekdays.length-1; x++) {
			TMweekdays[x].style.width = parseInt((colWidth-50-23-2*W3Cborder)/noVisibleDays)+"px";
			TMweekdays[x].style.left = parseInt((colWidth-50-23-2*W3Cborder)/noVisibleDays*colCount)-parseInt((colWidth-50-23-2*W3Cborder)/noVisibleDays-50)+"px";
			colCount++;
		}
		TMweekdays[TMweekdays.length-1].style.left = parseInt((colWidth-50-23-2*W3Cborder)/noVisibleDays*colCount)-parseInt((colWidth-50-23-2*W3Cborder)/noVisibleDays-50)+"px";

		var TMhours = colDiv.childNodes[2].childNodes;
		calEvent = new Array();
		var z = -1;
		var u = 0;
		var a = 0;
		var hourHeight = 40;
		for (var y = 0; y < TMhours.length; y++) { // position hours
			if (TMhours[y].className != "event") { // do not layout events yet
				if (TMhours[y].id.substring(0,4) == "hour") {
					z++;
					u = 0;
				}
				TMhours[y].style.top = z*40+"px";
				TMhours[y].style.height = hourHeight+"px";
				if (u==0) {
					TMhours[y].style.width = 50-2*W3Cborder-6*W3Cborder+"px"; // compansate for border and padding
				}
				if (u!=0) {
					TMhours[y].style.width = parseInt((colWidth-50-23-2*W3Cborder)/noVisibleDays)+"px";
					TMhours[y].style.left = parseInt((colWidth-50-23-2*W3Cborder)/noVisibleDays*u)-parseInt((colWidth-50-23-2*W3Cborder)/noVisibleDays-50)+"px";
				}
				u++;
			} 
		}
		var TMevents = TMhours;
		for (var y = 0; y < TMevents.length; y++) { // position events, TMhours now = TMevents
			if (TMevents[y].className == "event") {
				var starthour = TMevents[y].getAttribute("starthour");
				var endhour = TMevents[y].getAttribute("endhour");
				var startoff = TMevents[y].getAttribute("startoff");
				var endoff = TMevents[y].getAttribute("endoff");
				var date = TMevents[y].getAttribute("date");
				if (starthour=="") starthour = 0;
				if (endhour=="") endhour = 23;
				if (startoff=="") startoff = 0;
				if (endoff=="") endoff = 99;
				
				TMevents[y].style.top = parseInt(document.getElementById("hour"+starthour).style.top)+hourHeight*startoff/100+2+"px";
				TMevents[y].style.left = parseInt(document.getElementById("date"+date).style.left)+2+"px";
				TMevents[y].style.width = parseInt(document.getElementById("date"+date).style.width)-2-2*W3Cborder+"px";
				TMevents[y].style.height = parseInt(document.getElementById("hour"+endhour).style.top)+hourHeight*endoff/100-parseInt(TMevents[y].style.top)-1-W3Cborder+"px";
				TMevents[y].style.zIndex = 100+parseInt(TMevents[y].style.top);
			} 
		}
		document.getElementById("hour"+8).scrollIntoView(true); // scroll to 08:00

	}
	document.getElementById('workspace').style.visibility = "visible"; // show
}
function outlineEvent(eventId, overout) {
	eventParts = new Array();
	
	var allDivs = document.getElementById("column1").childNodes[2].getElementsByTagName("div");
	var i = 0;
	for (var j = 0; j < allDivs.length; j++) {
		if (allDivs[j].getAttribute("eventid") == eventId) {
			if (overout=="over") allDivs[j].style.borderColor = "#D83838";
			if (overout=="out") allDivs[j].style.borderColor = "#000000";
		}
	}
}
