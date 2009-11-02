
import unittest
import tempfile
from subprocess import Popen, PIPE

#import repos.servicelayer.svn
from svn import *

from csvn.core import *
from urllib import pathname2url
from csvn.repos import LocalRepository

repolocation = os.path.join(tempfile.gettempdir(), "svn_test_repos")
repourl = pathname2url(repolocation)
if repourl.startswith("///"):
  # Don't add extra slashes if they're already present.
  # (This is important for Windows compatibility).
  repourl = "file:" + repourl
else:
  # If the URL simply starts with '/', we need to add two
  # extra slashes to make it a valid 'file://' URL
  repourl = "file://" + repourl
# the dumpfile from subversion/bindings/ctypes-python/test/test.dumpfile in svn source
dumpfile = os.path.join(os.path.split(__file__)[0], 'csvn_test.dumpfile')
print "URL passed to RemoteRepository is %s" % repourl

class TestSvnEdit(unittest.TestCase):

    def setUp(self):
        self.tearDown() # avoid "is a subdirectory of an existing repository"
        svnadmin = LocalRepository(repolocation, create=True)
        svnadmin.load(open(dumpfile))

    def tearDown(self):
        if os.path.exists(repolocation):
            csvn.core.svn_repos_delete(repolocation, Pool())

    def testSave(self):
        svn = SvnEdit(repourl, User('test','test'), 'log message')
        text = ('This repository is for test purposes only. Any resemblance to any other\n' +
            'added line in\n' +
            'repository, real or imagined, is purely coincidental.\n')
        rev = svn.save('trunk/README.txt', text)
        self.assertEqual(rev, 10)
        ''' verify using command line svnadmin '''
        svnlook = 'svnlook'
        self.assertEqual(Popen([svnlook, 'youngest', repolocation], stdout=PIPE).communicate()[0], '10\n')
        self.assertEqual(Popen([svnlook, 'log', repolocation], stdout=PIPE).communicate()[0], 'log message\n')
        diff = Popen([svnlook, 'diff', repolocation], stdout=PIPE).communicate()[0]
        self.assertTrue(diff.find('+added line in\n'))
        self.assertTrue(diff.find(' repository, real or imagined, is purely coincidental.'))
        self.assertTrue(diff.find('-Contributors:'))
        
    def testSaveBasedOnRev(self):
        ''' When there is a user parameter for "based on" the new text should diff from that revision '''
        # TODO
        pass

if __name__ == '__main__':
    unittest.main()
