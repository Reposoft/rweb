// Addon to the Repos class to provide XSLT transform

// TODO use Repos to require Sarissa dependency

// exceptions
function XsltException(message) {
   this.message = message;
   this.name = "XSLT error";
}

/**
 * Transform XML using an XSLT file.
 * Both files are loaded using synchronous XMLHttpRequest.
 * @param xmlUrl valid XML document
 * @param xslUrl stylesheet, note that it must return one (1) root node
 * @return DOM fragment, simply append to a node
 */
transform = function(xmlUrl, xslUrl) {
	// Load the XML data
    var xmlDocument =  Sarissa.getDomDocument();
    xmlDocument.async = false; 
    xmlDocument.load(xmlUrl);
	//alert(new XMLSerializer().serializeToString(xmlDocument));
	
	// Load the transformation styleshet
    var xslt = Sarissa.getDomDocument();
    xslt.async = false; 
    xslt.load(xslUrl);
	//alert(new XMLSerializer().serializeToString(xslt));
	
	// Transform
    var xsltProc  = new XSLTProcessor();
    xsltProc.importStylesheet(xslt); // makes the styleshet reusable 
	xsltProc.setParameter("", "contentsOnly", "true"); // no <html><head>, must be after importStylesheet to work in IE
    fragment = xsltProc.transformToDocument(xmlDocument);
    if (Sarissa.getParseErrorText(fragment) != Sarissa.PARSED_OK) {
        alert ("Transformer error: " +   Sarissa.getParseErrorText(fragment));
    };
	
	return fragment;
}

transformToElement = function(xmlUrl, xslUrl, targetElement) {
	fragment = transform(xmlUrl, xslUrl);
	// write the result into the target element
    targetElement.innerHTML = "";
    targetElement.innerHTML =  new XMLSerializer().serializeToString(fragment);
}
