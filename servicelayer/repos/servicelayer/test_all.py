#!/usr/bin/env python

import unittest
import doctest

import svn
import test_svn

def full_suite():
    suite = unittest.TestSuite()
    # doctests
    suite.addTest(doctest.DocTestSuite(svn))
    # unit tests
    suite.addTest(unittest.makeSuite(test_svn.TestSvnAccess, 'test'))
    return suite

if __name__ == '__main__':
    unittest.main(defaultTest='full_suite')
