
import unittest
import tempfile

#import repos.servicelayer.svn
from svn import *

from csvn.core import *
from urllib import pathname2url
from csvn.repos import LocalRepository
from shutil import rmtree

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
dumpfile = open(os.path.join(os.path.split(__file__)[0],
                        'csvn_test.dumpfile'))

class TestSvnAccess(unittest.TestCase):

    def setUp(self):
        self.tearDown() # avoid "is a subdirectory of an existing repository"
        svnadmin = LocalRepository(repolocation, create=True)
        svnadmin.load(dumpfile)

    def tearDown(self):
        if os.path.exists(repolocation):
            #svn_repo_delete(repolocation, Pool())
            rmtree(repolocation)

    def testType(self):
        svn = SvnAccess(repourl, User('test','test'), None)
        self.assertEqual(svn.type('trunk', 0), 'none')
        self.assertEqual(svn.type('trunk'), 'dir')
        self.assertEqual(svn.type('trunk/README.txt'), 'file')

if __name__ == '__main__':
    unittest.main()
