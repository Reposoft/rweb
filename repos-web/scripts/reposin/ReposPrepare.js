/**
 * Repos script prepare (c) 2006 Staffan Olsson www.repos.se
 * @version $Id$
 */
 
/**
 * Change createElement of default 'document' instance so that all
 * libraries can work with xhtml+xml documents (most are not tested for this)
if (document.documentElement && document.documentElement.namespaceURI && document.createElementNS) {
	document.createElement = function(t) {
		return document.createElementNS(document.documentElement.namespaceURI, t);
	};
}
 */
