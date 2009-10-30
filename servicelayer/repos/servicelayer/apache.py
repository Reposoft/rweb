""" Install using
<Location /servicelayer>
    PythonFixupHandler repos.servicelayer.apache
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
    req.write('im here \n')
    return apache.OK
    
def fixuphandler(req):
    req.handler = 'mod_python'
    req.register_output_filter("UPPERCASE", uppercase)
    req.add_output_filter("UPPERCASE")
    req.add_handler('PythonHandler', handler)
    return apache.OK
