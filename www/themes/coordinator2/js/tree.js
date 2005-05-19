/**
 * Navigation tree functions for expand/collapse
 * Tables that use this should, after </table>, print
 * <script language="javascript" type="text/javascript"><!--
 *  	initNavigation();
 *  --></script>
 * The current tree state is set to default if there is a query string parameter 'treeReset' (with any value)
 * @requires cookies.js loaded in parent document. see functions saveTreeState and loadTreeState
 */

/** settings **/
var FIRST_ROW = 3;				// row of the first unit that can be hidden (counting from 0)
var COOKIE_PATH = '/';
var COLLAPSED_ICON = 'images/tree/collapsed.gif';
var EXPANDED_ICON = 'images/tree/expanded.gif';
var UNIT_BGCOLOR = '#999999';
var ACTIVE_BGCOLOR = '#CCCCCC';
var cookieName = 'treeMask';	// concatenated with rowcount, so that mask is reset when tree is changed

/** global variables */
var treeTable;
var treeRows;
var treeRowcount;

/**
 * Initialize navigationTree variables and cookie
 * @author Staffan Olsson
 */
function initNavigation() {
	treeTable = document.getElementById('navigationTreeTable');
	treeRows = treeTable.rows;
	treeRowcount = treeRows.length;
	if ( isNaN(treeRowcount) || treeRowcount<=FIRST_ROW )
		alert('No navigation tree loaded');
	// load persisten value if not query string treeReset is set
	cookieName += treeRowcount;
	if(window.location.search.indexOf('treeReset')<0)
		var mask = loadTreeState();
	if (mask == null) // if no tree info found, set default
		mask = reInitNavigation();
	// set this up
	if (mask>0) {
		hideRows(mask);
		// and the icons
		deriveCollapsed(mask);
	}
	
	// display active unit from start
	//setActive( getActiveUnit() );
	setActive( 'unit1' );
	
	/*
	// ---- tests ----
	// display
	alert('hide row 3');
	hideRows(4); // row 3
	alert('hide row 4 and 5');
	hideRows(24); // row 4&5
	alert('show defaults');
	hideRows(reInitNavigation());
	// cliks
	xImg = document.getElementById('unit3x');
	if (xImg == null)
		alert('Testing without icon');
	alert('collapse unit at row 3, previous mask: ' + loadTreeState());
	clickUnit(3,24,xImg);
	alert('show unit at row 3, back wih children 4&5, previous mask: ' + loadTreeState());
	clickUnit(3,24,xImg );
	alert('collapse row1, hide child row 3, previous mask: ' + loadTreeState());
	clickUnit(1,4,xImg );
	alert('show defaults');
	hideRows(reInitNavigation());
	setExpanded(3,xImg);
	*/
}

/**
 * Shows toolbar buttons according to bitmask
 * Unit links should call this function, so that
 * the way the real function is called is decided here
 */
function setVisibleTools(mask) {
	parent.frames['toolbar'].setVisible(mask);
}

/**
 * @return String identifier of the current unit
 */
function getActiveUnit() {
	return parent.getActiveUnit();
}

/**
 * Saves layout of the tree
 */
function saveTreeState(mask) {
	parent.setPersistentCookie(cookieName,mask,COOKIE_PATH);
}

/**
 * Loads layout of tree
 * @return bitmask
 */
function loadTreeState() {
	return parent.getCookie(cookieName)	
}

/**
 * Mark the row <tr id="rowId"> as selected
 */
function setActive(rowId) {
	// mark all inactive	
	for(var r=FIRST_ROW; r<treeRowcount; r++)
		treeRows[r].style.backgroundColor = UNIT_BGCOLOR;
	// mark specified row active
	var row = document.getElementById(rowId);
	if (row != null)
		row.style.backgroundColor = ACTIVE_BGCOLOR;
	//else
		//window.status = 'Error looking for ' + rowId + ', please log in again';
}

/**
 * Set navigation bitmask to the default value, and save that value as a cookie
 * @return the default bitmask
 * @author Staffan Olsson
 */
function reInitNavigation() {
	saveTreeState(0);
	return 0;
}

/**
 * Show/hide rows n the object array treeRows using the given bitmask where 1=hidden
 * For example mask = 11 = bits 1011 --> hide row 1,2 and 4.
 * If the mask is shorter than the number of rows, all subsequest rows will be displayed.
 * Row 0 is ignored, as it is recommended to keep that row with fxed width, so that the layout is not affected.
 * Rule number one of fight club is: if the parent is collapsed, no descendant may be visible.
 * @author Staffan Olsson
 */
function hideRows(mask) {
	var m = 1; // bit corresponding to current row in mask
	var hidden = 0; // 0 if the row at this iteration is not descendant of a collapsed parent. Otherwise, > 0 marks the level at which the current collapse started.
	for(var r=FIRST_ROW; r<treeRowcount; r++) {
		if (hidden > 0 && treeRows[r].cells.length < hidden) // we have just left the last child of a collapsed parent
			hidden = 0;
		if (hidden > 0 || mask & m) { // if inside collapse, or if bit at this position is 1 -> hide
			treeRows[r].style.display='none';
			if (hidden == 0) // this is the first hidden child, exit level is parent's level
				hidden = treeRows[r].cells.length;
		} else
			treeRows[r].style.display='';
		m <<= 1; // next bit
	}
}

/**
 * Set collapse icons given a mask
 * A row is collapsed if not hidden AND the following row is hidden
 * @author Staffan Olsson
 */
function deriveCollapsed(mask) {
	var r = FIRST_ROW-1;
	var hidden; // row 0 can not be collapsed
	while(mask && ++r<treeRowcount) {
		hidden = mask & 1;
		mask >>>= 1; // right shif 1, shift in 0
		if (!hidden && mask & 1)
			treeRows[r].getElementsByTagName('img')[0].src=COLLAPSED_ICON;
	}
}

/**
 * Set state of a unit row to expanded
 * Currently this only means set icon
 * @author Staffan Olsson
 */
function setExpanded(rownum, xImg) {
	if(xImg!=null)
		xImg.src=EXPANDED_ICON;
}

/**
 * Set state of a unit row to collapsed
 * Currently this only means set icon
 * @author Staffan Olsson
 */
function setCollapsed(rownum, xImg) {
	if(xImg!=null)
		xImg.src=COLLAPSED_ICON;
}

/**
 * Process a click on an expand/collaps icon. Both expand and collapse uses this function.
 * @param unitRow the table row where the click occured (conting from 0)
 * @param childcount number of children
 * @param xImg the image object tjat should have it's icon changed.
 * @author Staffan Olsson
 */
function clickUnit(unitRow, childcount, xImg) {
	// get the mask for the child rows
	bitmaskChildrows = getChildMask(unitRow, childcount);
	// assume there is a cookie now because initNavigation has been run
	var mask = loadTreeState();
	// if any of the childriws were already hiddem, this is an expand operation
	if( mask & bitmaskChildrows )
		setExpanded(unitRow, xImg);
	else
		setCollapsed(unitRow, xImg);
	// switch the bits on the childrows' positions
	mask = mask ^ bitmaskChildrows;
	// apply
	hideRows(mask);
	// save results
	saveTreeState(mask);
}

/**
 * @unitRow parent row
 * @children number of children
 * @return bitmaskChildren the children to this unit; 1 at the corresponding bit positions. for example 24 for row 3 and 4.
 * @author Staffan Olsson
 */
function getChildMask(unitRow, children) {
	var m = 1;
	while(--children > 0) {
		m <<= 1;
		m += 1;
	}
	m <<= unitRow;
	return m;
}