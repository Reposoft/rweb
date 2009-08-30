#!/usr/bin/env python
"""Fulltext indexing of committed files.
(c) Staffan Olsson reposstyle.com
Requires Solr 1.4-dev example set up as described in:
http://wiki.apache.org/solr/ExtractingRequestHandler
"""

import logging
import logging.handlers
from optparse import OptionParser

from csvn.core import SVN_INVALID_REVNUM
from csvn.repos import LogEntry
from csvn.repos import RemoteRepository

import StringIO
import httplib
import urllib

# needed because httplib can't post multipart
from tempfile import NamedTemporaryFile
import os

""" initialize csvn (ctypes subversion bindings) """
import sys
import csvn.core
src_swig_python_tests_dir = os.path.dirname(os.path.dirname(__file__))
sys.path[0:0] = [ src_swig_python_tests_dir ]
csvn.core.svn_cmdline_init("", csvn.core.stderr)

""" hook options """
parser = OptionParser()
parser.add_option("-p", "--repository", dest="repo",
    help="A local repository path")
parser.add_option("", "--parentpath", dest="parent", default="/svn/",
    help="The parent path as setup in Apache. Deprecated. Defaults to %default.")
parser.add_option("-v", "--verbose", dest="verbose",
    action='store_true', help="verbose mode",
    default=False)
parser.add_option("-r", "--revision", dest="rev",
    help="Committed revision")
parser.add_option("", "--logfile", dest="logfile", default="./indexing-parentchild.log",
    help="The absolute path to logfile. Defaults to %default.")
parser.add_option("", "--loglevel", dest="loglevel", default="info",
    help="The loglevel (standard Log4J levels, lowercase). Defaults to %default.")
parser.add_option("", "--cloglevel", dest="cloglevel", default="info",
    help="The console loglevel (standard Log4J levels, lowercase). Defaults to %default.")


def openRepo(url):
    """ connect to repo.
    Returns RemoteRepository. """
    logger.debug("Opening repository: %s" % (url))
    repo = RemoteRepository(url)
    logger.debug("Repo HEAD rev: %s" % repo.latest_revnum())
    return repo

def submitDelete(path, rev):
    raise NameError('Delete not implemented')

def submitContents(path, rev):
    #output = StringIO.StringIO()
    f = NamedTemporaryFile('wb')
    repo.cat(f, path.strip("/"), rev)

    params = {"literal.id": path, "commit": "true"}
    """ httplib does not support posting as multipart, using tempfile instead of StringIO """
    #h = httplib.HTTPConnection('localhost', 8983)
    #h.putrequest('POST', '/solr/update/extract' + "?" + urllib.urlencode(params))
    #h.endheaders()
    #h.send(output.getvalue()) # TODO stream from StringIO and set Content-Length from svn info
    #response = h.getresponse()
    #logger.debug(response)
    #if response.status == 200:
    #    logger.debug(response.read())
    #else:
    #    logger.error("%d %s" % (response.status, response.read()))
    
    # use curl
    f.flush()
    logger.debug(f.name)
    os.system("/usr/bin/curl -v 'http://localhost:8983/solr/update/extract?%s' -F 'myfile=@%s'"
              % (urllib.urlencode(params), f.name))
    f.close()
    
    
""" global variables """
(options, args) = parser.parse_args()

""" set up logger """
logger = logging.getLogger("Indexing")
logger.setLevel(logging.DEBUG)
# console
ch = logging.StreamHandler()
ch.setLevel(logging.DEBUG)
formatter = logging.Formatter("%(asctime)s - %(name)s - %(levelname)s - %(message)s")
ch.setFormatter(formatter)
logger.addHandler(ch)

""" set up repository connection """
repo_path = options.repo.rstrip("/")
rev = long(options.rev)
root = "file://" + repo_path
logger.debug("URL is: %s" % (root))
repo = openRepo(root)

""" read log """
log = repo.log(rev, rev, discover_changed_paths=1)
for entry in log:
    if entry.changed_paths != None:
        for key, value in entry.changed_paths.items():
            value = value[0]
            if value.copyfrom_rev != SVN_INVALID_REVNUM:
                logger.info(" %s %s (from %s:%d)" % (value.action, key,
                    value.copyfrom_path,
                    value.copyfrom_rev))
            else:
                logger.info(" %s %s" % (value.action, key))
            
            if value.action == 'D':
                submitDelete(key, rev)
                continue
            
            submitContents(key, rev)

