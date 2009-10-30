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
    # TODO find repository root for client init and use target paths for services?
    
    if req.method == 'POST':
        req.write("post not implemented\n")
        return apache.OK
    
    args = dict()
    if req.args:
        args = parse_qs(req.args)
    
    # GET without a service should not be possible
    if not 's' in args:
        raise apache.SERVER_RETURN, apache.HTTP_SERVER_ERROR
    service = args['s'][0]
    
    accept = svn.Accept()
    
    client = svn.SvnAccess(url, user, accept)
    
    if service == 'debug':
        response = "Hello user: %s\n" % user_name
        response = response + "Service will use url: %s\n" % url
        response = response + "s parameter is %s\n" % args['s']
    elif service == 'youngest':
        response = svn.test(url, user)
    elif service == 'proplist':
        response = client.proplist()
    else:
        raise apache.SERVER_RETURN, apache.HTTP_BAD_REQUEST
    
    req.content_type = accept.chosen
    req.set_content_length(len(response))
    req.write(response)
    
    return apache.OK
