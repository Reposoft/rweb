import sys
import os
# http.client in python 3.1
import httplib
# urllib.parse in python 3.1
import urllib

def index(req, target=None, rev=None):

	if not target:
		return 'Target is required'

	settings = getSettings()
	
	# http.client in python 3.x
	url = settings["solrapp"] + settings["schema"] + "select/"
	params = {'q': target, "start": 0, "rows": 10}
	headers = {"Accept": "text/xml"}
	c = httplib.HTTPConnection(settings["solrhost"], settings["solrport"])
	c.request('GET', url + "?" + urllib.urlencode(params), headers=headers)
	r1 = c.getresponse()
	data = r1.read()
	c.close()
	if r1.status is not 200:
		raise NameError("Query failed with status %d and response %s" % (r1.status, data))
	
	req.content_type = "text/xml"
	req.write(data)
	#return apache.OK
	return

def getSettings():
	
	return {"solrhost": "localhost",
		    "solrport": 8080,
		    "solrapp": "/solr/",
		    "schema": "parentchild/"}

# example of sub-service
#http://localhost/modpython/hello.py/hello
#def hello(name=None):
#    if name:
#        return 'Hello, %s!' % name.capitalize()
#    else:
#        return 'Hello there!'	
	