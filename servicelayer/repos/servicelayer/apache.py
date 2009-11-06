""" Install using
<Location /servicelayer>
    PythonFixupHandler repos.servicelayer.apache
</Location>
"""

from mod_python import apache
from urlparse import parse_qs
from re import match
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
    # TODO support HEAD
    if not req.method == 'GET':
        return apache.DECLINED
    if not req.args:
        return apache.DECLINED
    # All our GET requests use s=[service]
    if not match(r'(\A|&)s=', req.args):
        return apache.DECLINED
    # So the rest is ours
    return override(req)

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
    # how do we know at what path element repository root is?
    (x, parent, base, target) = req.uri.split('/', 3)
    repoRootUrl = 'http://%s:%d/%s/%s' % (address, port, parent, base)
    
    ''' csvn fails spectacularly on trailing slashes in path '''
    target = target.rstrip('/')
    
    args = dict()
    if req.args:
        args = parse_qs(req.args)
    
    # TODO consistently use parameters p and r the same way mod_dav_svn does
    
    if req.method == 'POST':
        text = args['text'][0]
        message = args['message'][0]
        client = svn.SvnEdit(repoRootUrl, user, message)
        changeset = client.save(target, text)
        response = "%d" % changeset
        req.content_type = 'text/plain'
        req.set_content_length(len(response))
        req.write(response)
        return apache.OK
    
    # GET without a service should not end up in this hander
    if not 's' in args:
        raise apache.SERVER_RETURN, apache.HTTP_INTERNAL_SERVER_ERROR
    service = args['s'][0]
    
    accept = svn.Accept()
    
    try:
        client = svn.SvnAccess(repoRootUrl, user, accept)
    except Exception as inst:
        response = '' # + inst + "\n"
        response = response + "Tepository: %s\n" % repoRootUrl
        response = response + "Target: %s" % target
        req.status = apache.HTTP_INTERNAL_SERVER_ERROR
        req.content_type = accept.chosen
        req.set_content_length(len(response))
        req.write(response)
        return
    
    if service == 'youngest':
        response = svn.test(repoRootUrl, user)
    elif service == 'kind':
        response = client.kind(target)
    elif service == 'proplist':
        response = client.proplist(target)
    else:
        raise apache.SERVER_RETURN, apache.HTTP_BAD_REQUEST
    
    req.content_type = accept.chosen
    req.set_content_length(len(response))
    req.write(response)
    req.flush()
    
    return apache.OK
