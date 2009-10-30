""" Install using
<Location /servicelayer>
    PythonFixupHandler repos.servicelayer.apache
</Location>
"""

from mod_python import apache

# the repos service
import svn

def fixuphandler(req):
    ''' Identify service requelts and do not interfere with normal svn operation
    
    Subversion requests uses DAV methods (and GET of course)
    and since 1.6 urls may contain ?[p=PEG][&r=REV]
    
    '''
    # Quick way to identify servicelayer requests
    if not req.args or req.args.find('s') == -1:
        return apache.DECLINED
    
    req.handler = 'mod_python'
    req.add_handler('PythonHandler', handler)
    return apache.OK

def handler(req):
    # get user
    user_pw = req.get_basic_auth_pw()
    user_name = req.user
    
    req.content_type = "text/plain"
    req.write("Hello user: %s\n" % user_name)
    
    req.write("URI: %s\n" % req.unparsed_uri)
    req.write("Parse: %s\n" % (req.parsed_uri,))
    req.write("Method is %s\n" % req.method)
    req.write("Query string is: %s\n" % req.args)
    
    # try repository access
    url = "http://localhost:8530" + req.uri 
    req.write("debug: " + svn.test(url, user_name, user_pw))
    
    return apache.OK
