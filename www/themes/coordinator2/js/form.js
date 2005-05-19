// Used when printing error messages, these are deprecated
// TODO Different dateformats are only sporadically supported in the code below
var dateFormatString = new Array("yyyy-mm-dd","mm/dd/yyyy")
var timeFormatString = new Array("hh:mm","h:mm a")
var formatSettings = [0,0,'.']; // XXX Shall be set for every user

// XXX settingsArray should be moved to be a global variable on the page...
// XXX inactive fields should not be checked

// ********************
// receiverSelect
// ********************
function moveSelected(from, to) {
    for(var i = from.options.length - 1; i >= 0; i--) {
		if (from.options[i].selected) {
		    to.options[to.length] = new Option(from.options[i].text, from.options[i].value);
		    from.options[i] = null;
		}
    }
}

// ********************
// addSelect
// ********************
function appendSelected(list, e) {
    list.options[list.length] = new Option(e.value, e.value);
}

function removeSelected(list) {
    for(var i = list.options.length - 1; i >= 0; i--) {
		if (list.options[i].selected) {
		    list.options[i] = null;
		}
    }
}

function editSelected(list,e) {
    if (list.selectedIndex >= 0) {
        e.value = list.options[list.selectedIndex].value;
		list.options[list.selectedIndex] = null;
    }
}

// ************************
// form submit alternatives
// ************************

// adapt form submit action to the current view
// requires navigation.js to be loaded
// form.action can not be referenced if there is a form field named 'action', which ther shouldn't be
function checkAction(formObj) {
	try {
		if (isPopup) {
			act = formObj.action.toString().replace('\.jwa','.popup.jwa');
			formObj.action = act;
			// does not work for GET forms: formObj.action = appendQueryParameter(act,'view','popup');
		}
	} catch(er) {
		homeWin.reportScriptError("Failed changing form action. " + er.toString()); 
	}
	return true;
}

// Set the submit action of a form
function setAction(formObj,actionName) {
	// old, makes the real action inaccessible: formObj.elements['action'].value=value + '.jwa';
	formObj.action = actionName+'.jwa';
}

// set a form field named action to the specified value
function setActionSubmit(formId, value) {
    var f = document.getElementById(formId);
    setAction(f,value);
	checkAction(f);
    f.submit();
	return true;
}

function setActionCheckFormSubmit(formId, value, fieldList) {
    var f = document.getElementById(formId);
    setAction(f,value);
	checkAction(f);
    success = checkForm(f, fieldList, formatSettings);
	if (success)
		f.submit();
}

// run client-side validation and submit on success. alert on invalid values.
function checkFormSubmit(formId, fieldList) {
    var f = document.getElementById(formId);
	checkAction(f);
    success = checkForm(f, fieldList, formatSettings);
	if (success)
		f.submit();
}

// Set a single field value and then always submit
function setValueSubmit(formId, fieldname, value) {
    var f = document.getElementById(formId);
    f.elements[fieldname].value=value;
	succes = checkAction(f);
    f.submit();
}

function formSelectNone(form) {
    for (var i = 0; i < form.elements.length; i++) {
		if (form.elements[i].type == "checkbox") {
		    form.elements[i].checked = false;
		}
    }
}

function documentToolSelect(id, selectObject) {
    var index = selectObject.selectedIndex;
	if (index > 0) { //If a tool is selected
		var selectedOption = selectObject.options[index];
		var f = selectObject.form;
		formSelectNone(f); // unceck all
		if (f.elements['id'+id].type == "checkbox") { // if it is a checkbox
		    f.elements['id'+id].checked = true; // check this checkbox
		}
		setAction(f, selectedOption.value); //set what to do
		f.submit();// submit
    }
}

// ********************
// Autofills, validates and changes a form before submitting
// usage:
// href=javascript:checkForm(document.getElementById('formId'), 
//                           [[fieldname,fielddesc,type,property*]*], 
//                           [dateFormat,timeFormat,decimalSign])
// type: [none], datetime, password, decimal, number, receiverSelect, addSelect, multipleSelect
// property: required, lt [ref], gt [ref]
// @return true if form is valid
// ********************
function checkForm(form, fieldArray, settingsArray) {	
	// Auto fill some fields
	autoCompleteForm(form, fieldArray, settingsArray);
	// Check that all fields are correctly filled
	if (validateForm(form, fieldArray, settingsArray)) {
	    // Change the value of all fields to normal
	    transformForm(form, fieldArray, settingsArray);
	    return true;
	} else {
		return false;
	}
}

// ********************
// Try to be a wiseass in sime fields
// For example a date: 5 > thisyear-thismonth-5
// ********************
function autoCompleteForm(form, fieldArray, settingsArray) {
    var dateFormat = settingsArray[0];
    var timeFormat = settingsArray[1];
    var decimalSign = settingsArray[2];

    for (var i = 0; i < fieldArray.length ; i ++) {
    	try {
			var fieldInfo = fieldArray[i];
			var element = form.elements[fieldInfo[0]];
			var type = fieldInfo[2];
			if (type == "datetime")
				autoCompleteDateTime(element, dateFormat, timeFormat);
		} catch(er) {
	  		homeWin.reportScriptError('Script error ' + er + ', element=' + element); 
	  	}
    }
}

// ********************
// Change the value of all fields to normal form
// concatenates date & time, mark all of the users in a receiverSelect, etc
// ********************
function transformForm(form, fieldArray, settingsArray) {
    var dateFormat = settingsArray[0];
    var timeFormat = settingsArray[1];
    var decimalSign = settingsArray[2];

    for (var i = 0; i < fieldArray.length ; i ++) {
		var fieldInfo = fieldArray[i];
		var element = form.elements[fieldInfo[0]];
		var type = fieldInfo[2];
	
		if (type == "datetime") transformDateTime(element, dateFormat, timeFormat);
		//if (type == "decimal") transformDecimal(element, decimalSign);
		if (type == "receiverSelect") selectAllOptions(element);
		if (type == "addSelect") selectAllOptions(element);
		if (type == "fileUpload") transformFileUpload(element);
    }
}

// ********************
// Concatenates date & time
// Time must be found in a field named {name of datefield}_time
// ********************
function transformDateTime(element, dateFormat, timeFormat) {
    var date = element.value;
    var time = element.form.elements[element.name+'_time'].value;

    if (date != "") {
		// There might be another order or separator in other formats
		element.value = date + " " + time;
    }
}

// ********************
// Changes the decimal sign to a . (dot)
// ********************
//function transformDecimal(element, decimalSign) {
    // TODO not implemented yet
//}

// ********************
// Append time fileds to the end of date fields.
// ********************
function autoCompleteDateTime(element, dateFormat, timeFormat) {
    var name = element.name;

    var dateElement = element; //script error:
    var date = dateElement.value;

    var timeElement = element.form.elements[name+'_time'];
    var time = timeElement.value;

    if (date != "") {
		// Auto complete date, ie fill in year and month unless specified
		var today = new Date();
		var myRegExp1 = /^\d+$/;
		var myRegExp2 = /^\d+\-\d+$/;
		if (dateFormat == 1)
			myRegExp2 = /^\d+\/\d+$/;
	
		if (date.match(myRegExp1)) { // only day
			if (dateFormat == 1)
				dateElement.value = today.getFullYear() + "/" + (today.getMonth()+1) + "/" + date;
			else
				dateElement.value = today.getFullYear() + "-" + (today.getMonth()+1) + "-" + date;
		}

		if (date.match(myRegExp2)) { // day and month
			if (dateFormat == 1) 
				dateElement.value = today.getFullYear() + "/" + date;
			else
				dateElement.value = today.getFullYear() + "-" + date;
		}
    }

    if (time != "") {
		// Auto complete minutes unless specified
		var myRegExp = /^\d+$/;
		if (time.match(myRegExp)) {
			//if (timeFormat == 1) timeElement.value = time + ":0";
			//else 
			timeElement.value = time + ":0";
		}
    }
}


// ********************
// Copies the name of the file to the name field
// ********************
function transformFileUpload(element) {
    var from = element.value;
    if (from != '' && element.form.elements['name'+element.name].value == "") {
		var newStrings = new Array;
		newStrings = from.split(/[\:\/\\]/);
		// NB add name at the beginning
		element.form.elements['name'+element.name].value = newStrings[newStrings.length - 1];
    }
}

// ********************
// selects all options in a selectbox
// NB it must be a <select multiple
// ********************
function selectAllOptions(element) {
	try {
    	for(var i = 0; i < element.options.length; i++) {
			element.options[i].selected = true;
    	}
	} catch (er) {
		homeWin.reportScriptError('Script error ' + er + ', element=' + element); 
	}
}

// ********************
// Check that all fields are correctly filled
// @todo Coordinator 2.5, the 'desc' parameters should now specify the localized validation error message.
//   thus, functions below should not construct a message. We don't want to keep localized versions of the script files.
// ********************
function validateForm(form, fieldArray, settingsArray) {
    var dateFormat = settingsArray[0];
    var timeFormat = settingsArray[1];
    var decimalSign = settingsArray[2];

    for (var i = 0; i < fieldArray.length ; i++) {
 	  var fieldInfo, element, desc, type;
 	  try {	
 		var fieldInfo = fieldArray[i];
		var element = form.elements[fieldInfo[0]];
		var desc = fieldInfo[1];
		var type = fieldInfo[2];
	
		if (type == "datetime" && ! validateDateTime(element, desc, dateFormat, timeFormat)) return false;
		if (type == "password" && ! validatePassword(element, desc)) return false;
		if (type == "decimal" && ! validateDecimal(element, desc, decimalSign)) return false;
		if (type == "number" && ! validateNumber(element, desc)) return false;
		
		for (var j = 3; j < fieldInfo.length; j++) {
		    
		    if (fieldInfo[j] == "required" && ! validateRequired(element, desc, type)) return false;
	
		    if (fieldInfo[j] == "gt") {
				if (! validateGT(element, desc, fieldInfo[++j], type, dateFormat, timeFormat, decimalSign))
					return false;
		    }
		    if (fieldInfo[j] == "lt") {
				if (! validateLT(element, desc, fieldInfo[++j], type, dateFormat, timeFormat, decimalSign))
					return false;
		    }
		}
	  } catch(er) {
	  	homeWin.reportScriptError('Script error ' + er + ', element=' + element + ',desc=' + desc + ',type=' + type); 
	  }
    }
    return true;
}

// ********************
// Checks if element is filled
// NN does not yet work on all multiple selectboxes
// @param desc The message to the user on validation error
// ********************
function validateRequired(element, desc, type) {
    if (type == "multipleSelect") {
		for (var i = 0; i < element.options.length; i++) {
	    	if (element.options[i].selected) return true;
		}
		alert(desc);
			element.focus();
		return false;
    }
    // added 2004-03-29 to make validation work when responsible box has not been clicked
    if (type == "receiverSelect" && element.selectedIndex == -1)
    	selectAllInList(element);
    // original validation
    if ((type == "receiverSelect" || type == "addSelect") && element.length == 0 || element.value == "") {
		alert(desc);
		element.focus();
		return false;
    }
    return true;
}

// ********************
// Checks if the value if greater than something
// @param reference may be the name of a field or a static value
// NB if type=datetime reference must be a reference to a field
// (consisiting of a [x],[x]_time pair) that might be hidden.
// ********************
// Only tested for dates
// XXX Number seems to behave strange, string comparison?
function validateGT(element, desc, reference, type, dateFormat, timeFormat, decimalSign) {

    if (type == "datetime") {
		if (element.form.elements[reference].value == "" || element.value == "") return true;
	
		var date1 = dateFromString(element.value, dateFormat);
		var time1 = timeFromString(element.form.elements[element.name + '_time'].value, timeFormat);
		date1.setHours(time1[0]);
		date1.setMinutes(time1[1]);
	
		var date2 = dateFromString(element.form.elements[reference].value, dateFormat);
		var time2 = timeFromString(element.form.elements[reference + '_time'].value, timeFormat);
		date2.setHours(time2[0]);
		date2.setMinutes(time2[1]);
	
		if (date1.getTime() <= date2.getTime()) {
			alert(desc + " (" + element.value + " "  + element.form.elements[element.name + '_time'].value + ")" +
			  " mustByGreaterThan " + 
			  element.form.elements[reference].value + " " + 
			  element.form.elements[reference + '_time'].value);
			element.focus();
			return false;
		}
    } else {
		if (element.form.elements[reference] != null) reference = element.form.elements[reference].value;
	
		if (reference == "" || element.value == "") return true;
	
		if (element.value <= reference) {
			alert(desc + " (" + element.value + ") mustBeGreaterThan " + reference);
			element.focus();
			return false;
		}
    }
    return true;
}

// ********************
// Checks if the value if less than something
// @param reference may be the name of a field or a static value
// NB if type=datetime reference must be a reference to a field
// (consisiting of a [x],[x]_time pair) that might be hidden.
// ********************
// Only tested for dates
// XXX Number seems to behave strange, string comparison?
function validateLT(element, desc, reference, type, dateFormat, timeFormat, decimalSign) {

    if (type == "datetime") {
		if (element.form.elements[reference].value == "" || element.value == "") return true;
	
		var date1 = dateFromString(element.value, dateFormat);
		var time1 = timeFromString(element.form.elements[element.name + '_time'].value, timeFormat);
		date1.setHours(time1[0]);
		date1.setMinutes(time1[1]);
	
		var date2 = dateFromString(element.form.elements[reference].value, dateFormat);
		var time2 = timeFromString(element.form.elements[reference + '_time'].value, timeFormat);
		date2.setHours(time2[0]);
		date2.setMinutes(time2[1]);
	
		if (date1.getTime() >= date2.getTime()) {
			alert(desc + " (" + element.value + " "  + element.form.elements[element.name + '_time'].value +
			  ") isNotLessThan " + 
			  element.form.elements[reference].value + " " + 
			  element.form.elements[reference + '_time'].value);
			element.focus();
			return false;
		}
    } else {
		if (element.form.elements[reference] != null)
			reference = element.form.elements[reference].value;

		if (reference == "" || element.value == "") return true;
	
		if (element.value >= reference) {
			alert(desc + " (" + element.value + ") isNotLessThan " + reference);
			element.focus();
			return false;
		}
    }
    return true;
}

// *********
// Select all rows in a multiple-select box
// *********
function selectAllInList(CONTROL){
	for(var i = 0;i < CONTROL.length;i++){
		CONTROL.options[i].selected = true;
	}
}

// ********************
// Checks if element is a correct datetime
// requres that there is a field with the name element.name+"_verify"
// ********************
function validatePassword(element, desc) {
    var value = element.value;
    var verifyValue = element.form.elements[element.name+'_verify'].value;
    if (value == "" && verifyValue == "")
		return true;
    if (value != verifyValue) {
		alert(desc + " passwordsDoNotMatch");
		element.focus();
		return false;
    }
    return true;
}
// ********************
// Checks if element is a correct number
// ********************
function validateNumber(element, desc) { 
    var myRegExp = /^\-?\d+$/;
    var number = element.value;
    if (! number.match(myRegExp)) {
		alert(desc + " (" + number + ") isNotACorrectNumber");
		element.focus();
		return false;
    }
    return true;
}
// ********************
// Checks if element is a correct decimal number
// XXX Not working
// ********************
function validateDecimal(element, desc, decimalSign) {
    /*
    var myRegExp = /^\-?\d+\.?\d*$/;
    var number = element.value;

    var pos = number.indexOf(decimalSign);
    if (pos = 0
    var nnumber = number.substring(0,pos);
    nnumber += ".";
    nnumber += number.substring(pos+decimalSign.length,number.length);

    var myReplRegExp = new RegExp(decimalSign,"");
    number.replace(myReplRegExp,".");
    
    if (! number.match(myRegExp)) {
	alert(number + " isNotACorrectDecimalNumber");
	element.focus();
	return false;
    }
    */
    return true;
}


// ********************
// Checks if element is a correct datetime
// NB The fields with _date and _time in the end are the visible fields
// ********************
function validateDateTime(element, desc, dateFormat, timeFormat) {
    var name = element.name;

    var dateElement = element;
    var date = dateElement.value;

    var timeElement = element.form.elements[name+'_time'];
    var time = timeElement.value;

    if (date == "" && time == "") return true;

    // Check the date
    var myRegExp = /^\d+\-\d+\-\d+$/; // Standard ISO format
    if (dateFormat == 1) myRegExp = /^\d+\/\d+\/\d+$/; //XXX different formats
    if (! date.match(myRegExp) || dateFromString(date, dateFormat) == null) {
		alert(desc + " (" + date + ") isNotACorrectDate\nFormat " + dateFormatString[dateFormat]);
		dateElement.focus();
		return false;
    }
    
    // Check the time
    myRegExp = /^\d+\:\d+$/; // Standard format
    //if (timeFormat == 1) myRegExp = /^\d+\:\d+$/; //XXX different formats
    if (! time.match(myRegExp) || timeFromString(time, timeFormat) == null) {
		alert(desc + " (" + time + ") IsNotACorrectTime\nFormat "+timeFormatString[timeFormat]);
		timeElement.focus();
		return false;
    }
    
    return true;
}

// ********************
// Convert a string to a Date object
// return null if not a correct date
// ********************
function dateFromString(string, dateFormat) {
    var year = 0;
    var month = 0;
    var mday = 0;
    if (dateFormat == 1) { 	// XXX format
		var myDateSep = /\//;
		var myDateArray = string.split(myDateSep);
		year = myDateArray[2];
		month = myDateArray[0] - 1; // Month is 0-11
		mday = myDateArray[1];
    } else { // Normal format
		var myDateSep = /\-/;
		var myDateArray = string.split(myDateSep);
		year = myDateArray[0];
		month = myDateArray[1] - 1; // Month is 0-11
		mday = myDateArray[2];
    }
    var myDate = new Date(year, month, mday);

    if (myDate.getDate() != mday || myDate.getMonth() != month || myDate.getFullYear() != year) {
		return null;
    }

    return myDate;
}

// ********************
// Convert a string to an array containing hour and minutes
// return null if not a correct time
// ********************
function timeFromString(string, timeFormat) {
    var hour = 0;
    var minute = 0;

    if (timeFormat == 1) {
		// XXX format
    } else { // Normal format
		var myDateSep = /\:/;
		var myDateArray = string.split(myDateSep);
		hour = myDateArray[0];
		minute = myDateArray[1];
    }

    if (hour < 0 || hour > 23 || minute < 0 || minute > 59) {
		return null;
    }

    return new Array(hour, minute);
}
