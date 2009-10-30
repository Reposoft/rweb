""" Install using
<Location /servicelayer>
    PythonPath "sys.path+['/path/to/servicelayer']"
    PythonFixupHandler repostesthandler
</Location>
"""

from mod_python import apache

def uppercase(filter):
    s = filter.read()
    while s:
        filter.write(s.upper())
        s = filter.read()
    if s is None:
        filter.close()

def handler(req):
    req.content_type = 'text/plain'
    req.write('looks like the handler setup works \n')
    return apache.OK
	
def fixuphandler(req):
    req.handler = 'mod_python'
    req.register_output_filter("UPPERCASE", uppercase)
    req.add_output_filter("UPPERCASE")
    req.add_handler('PythonHandler', handler)
    return apache.OK

