#!/usr/bin/env python

import unittest

from index import *

class TestParentchild(unittest.TestCase):

    def testReadSolrIndexParentchild1(self):
        r = readSolrIndexParentchild1(query1)
        self.assertTrue(r)
        r1 = r["/Slides/Section - Introduction.xml"]
        self.assertTrue(r1)
        self.assertTrue("added" in r1)
        self.assertEquals(102, r1["added"])
        r2 = r["/Slides/CMS_presentation.xml"]
        self.assertTrue(r2)
        self.assertEquals(132, r2["removed"])


# sample responses from solr parentchild schema
query1 = """{
 'responseHeader':{
  'status':0,
  'QTime':1},
 'response':{'numFound':3,'start':0,'maxScore':2.410987,'docs':[
    {
     'childId':'/Slides/Repos_screenshot1.png',
     'id':'/Slides/Section - Introduction.xml/Slides/Repos_screenshot1.png',
     'parentId':'/Slides/Section - Introduction.xml',
     'parentRev':102,
     'addedRev':[102],
     'score':2.410987},
    {
     'childId':'/Slides/Repos_screenshot1.png',
     'id':'/Slides/CMS_presentation.xml/Slides/Repos_screenshot1.png',
     'parentId':'/Slides/CMS_presentation.xml',
     'parentRev':132,
     'removedRev':[132],
     'score':2.410987}]
 }}"""

# run test
if __name__ == '__main__':
    unittest.main()


 
 