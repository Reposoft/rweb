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

# needed until we have a better way of detecting files/folders
import re

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
parser.add_option("", "--nobase", dest="nobase", action='store_true', default=False,
    help="Set to false to disable indexing of paths prefixed with repo name (i.e. @base)."
        + " If the index is not for SVNParentPath repsitories, this makes paths easier to read.")
parser.add_option("-r", "--revision", dest="rev",
    help="Committed revision")
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
    logger.warn('Delete not implemented. File %s will remain in search index.' % path)

def submitContents(path, rev, base=None):
    #output = StringIO.StringIO()
    f = NamedTemporaryFile('wb')
    repo.cat(f, path.strip("/"), rev)

    params = {"literal.id": path, "commit": "true"}
    # path should begin with slash so that base can be prepended
    # this means that for indexes containing repo name paths do not begin with slash 
    if base:
        params["literal.id"] = base + params["literal.id"]
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
    logger.debug("Using temp file %s" % f.name)
    curl = "/usr/bin/curl"
    if logger.getEffectiveLevel() is logging.DEBUG:
        curl = curl + " -v"
    result = os.system("%s 'http://localhost:8983/solr/update/extract?%s' -F 'myfile=@%s'"
              % (curl, urllib.urlencode(params), f.name))
    if result:
        raise NameError("Failed to submit document to index, got %d" % result)
    f.close()
    logger.info("Successfully indexed id: %s" % params["literal.id"]);
    
    
""" global variables """
(options, args) = parser.parse_args()
if options.repo is None:
    parser.print_help()
    sys.exit(2)

""" set up logger """
LEVELS = {'debug': logging.DEBUG,
          'info': logging.INFO,
          'warning': logging.WARNING,
          'error': logging.ERROR,
          'critical': logging.CRITICAL}
level = LEVELS.get(options.loglevel)
if not level:
    raise NameError("Invalid log level %s" % options.loglevel)
logger = logging.getLogger("Repos Search hook")
logger.setLevel(level)
# console
ch = logging.StreamHandler()
ch.setLevel(level)
ch.setFormatter(logging.Formatter("%(asctime)s - %(levelname)s - %(message)s"))
logger.addHandler(ch)

""" set up repository connection """
repo_path = options.repo.rstrip("/")
base = None
if not options.nobase:
    base = os.path.basename(repo_path)
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
            
            # TODO ignore folders from changed paths, or submit will throw exception
            # How do we detect files?
            # For now we require files to have an extension
            if not re.match(r".*\.\w+$", key):
                logger.debug("Ignoring %s because it is a folder" % key)
                continue
            
            if value.action == 'D':
                submitDelete(key, rev)
                continue       
            
            submitContents(key, rev, base)

