#!/usr/bin/env python

import unittest
import doctest

import svn, test_svnaccess, test_svnedit

def full_suite():
    suite = unittest.TestSuite()
    # doctests
    suite.addTest(doctest.DocTestSuite(svn))
    # unit tests
    suite.addTest(unittest.makeSuite(test_svnaccess.TestSvnAccess, 'test'))
    suite.addTest(unittest.makeSuite(test_svnedit.TestSvnEdit, 'test'))
    return suite

if __name__ == '__main__':
    unittest.main(defaultTest='full_suite')
