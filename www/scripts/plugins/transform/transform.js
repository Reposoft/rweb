// Repos plugin to provide XSLT transform (c) Staffan Olsson

Repos.require('lib/sarissa/sarissa.js');

// There is another script at http://www.xslt.com/html/xsl-list/2005-09/msg00180.html

/**
 * Transform XML using an XSLT file.
 * Both files are currently loaded using synchronous XMLHttpRequest.
 * @version $Id$
 */
var Transform = Class.create();
Transform.prototype = {
	
	/**
	 * @param xslUrl URL to stylesheet. Note that it, after transform of any XML, must return one (1) root node
	 *  must be on the same host as the page
	 */
	initialize: function(xslUrl) {	
		if (!xslUrl) {
			throw "Stylesheet URL for transform is not set.";	
		}
		xslUrl;
		
				// Load the transformation styleshet
		var xslt = Sarissa.getDomDocument();
		xslt.async = false; 
		xslt.load(xslUrl);
		//alert(new XMLSerializer().serializeToString(xslt));
		
		// Transform
		var xsltProc  = new XSLTProcessor();
		xsltProc.importStylesheet(xslt); // makes the styleshet reusable 
		
		// Global parameters
		xsltProc.setParameter("", "contentsOnly", "true"); // no <html><head>, supported by Repos stylesheets
		
		this.transformer = xsltProc;
	},

	/**
	 * @param xmlUrl valid XML document url on the same host as the page
	 * @return DOM fragment, simply append to a node
 	 */
	urlToFragment: function(xmlUrl) {
		if (!xmlUrl) {
			throw "Source xml URL for transform is not set.";
		}
		// Load the XML data
		var xmlDocument =  Sarissa.getDomDocument();
		xmlDocument.async = false; 
		xmlDocument.load(xmlUrl);
		//alert(new XMLSerializer().serializeToString(xmlDocument));
		
		fragment = this.transformer.transformToDocument(xmlDocument);
		if (Sarissa.getParseErrorText(fragment) != Sarissa.PARSED_OK) {
			alert ("Transformer error: " +   Sarissa.getParseErrorText(fragment));
		};
		
		return fragment;
	},
	
	/**
	 * @param xmlUrl valid XML document url on the same host as the page
	 * @param the DOM element that will be emptied and get the transform results as child
	 * @return true if successful
 	 */
	urlToElement: function(xmlUrl, targetElement) {
		if (!targetElement) {
			throw "Target element is not a DOM node";	
		}
		fragment = this.urlToFragment(xmlUrl);
		// write the result into the target element
    	targetElement.innerHTML = "";
    	targetElement.innerHTML =  new XMLSerializer().serializeToString(fragment);
		return true;
	}
}
