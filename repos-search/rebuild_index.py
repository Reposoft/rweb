#!/usr/bin/env python
""" index all revisions in a repository """

from optparse import OptionParser
from commands import getoutput
import os
import sys

parser = OptionParser()
parser.add_option("-p", "--repository", dest="repo",
                  help="Local repository path")
parser.add_option("-i", "--indexer", dest="indexer", default="hook.py",
                  help="Execution path to the indexing script. Default: %default")
(options, args) = parser.parse_args()
if options.repo is None:
    parser.print_help()
    sys.exit(2)

youngest = int(getoutput('svnlook youngest %s' % options.repo))
if not youngest:
    raise NameError('invalid repository %s, svnlook youngest retunred %d' % (options.repo, youngest))
print '# Latest revision is %d' % youngest

# TODO performance would be better if commit is disabled in index.py and done here after last rev

for i in range(1, youngest + 1):
    cmd = 'python %s -p %s -r %d' % (options.indexer, options.repo, i)
    print('# ' + cmd)
    result = os.system(cmd)
    if result > 0:
        raise NameError('Got exit code %d for command; %s' % (result, cmd))
        break
