""" Install using
<Location /servicelayer>
    PythonFixupHandler repos.servicelayer.apache
</Location>
"""

from mod_python import apache
from urlparse import parse_qs
# the repos service
import svn

def fixuphandler(req):
    ''' Identify service requelts and do not interfere with normal svn operation
    
    Subversion requests uses DAV methods (and GET of course)
    and since 1.6 urls may contain ?[p=PEG][&r=REV]
    
    '''
    # Subversion does not use post but we do
    if req.method == 'POST':
        return override(req)
    # All other methods are dav (at least until we implement PUT with log message
    if not req.method == 'GET':
        return apache.DECLINED
    if not req.args:
        return apache.DECLINED
    # All our get requests use s=[service], and subversions p and r never contain "s"
    if req.args.find('s') == -1:
        return apache.DECLINED
    # The rest is ours
    return override(req);

def override(req):
    ''' Override the default content handler with our servicelayer '''
    req.handler = 'mod_python'
    req.add_handler('PythonHandler', servicelayer)
    return apache.OK

def servicelayer(req):
    # get user, already authenticated because this hander is invoked after auth handler
    user_pw = req.get_basic_auth_pw()
    user_name = req.user
    user = svn.User(user_name, user_pw)
    
    (address, port) = req.connection.local_addr
    url = 'http://%s:%d%s' % (address, port, req.uri)
    
    if req.method == 'POST':
        req.write("Got post")
    
    args = parse_qs(req.args)
    
    req.content_type = "text/plain"
    req.write("Hello user: %s\n" % user_name)
    req.write("Service will use url: %s\n" % url)
    req.write("s parameter is %s\n" % args['s'])
    
    req.write("URI: %s\n" % req.unparsed_uri)
    req.write("Parse: %s\n" % (req.parsed_uri,))
    req.write("Method is %s\n" % req.method)
    req.write("Query string is: %s\n" % req.args)
    
    # try repository access
    req.write("debug: " + svn.test(url, user))
    
    return apache.OK
