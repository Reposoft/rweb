'''
Subversion API for the Repos Web Servicelayer.
'''

import csvn_setup_path
import csvn
from csvn.core import *
from csvn.repos import RemoteRepository
from csvn.auth import User

import tempfile

import json

def test(url, user):
    '''
    Test svn access given url and credentials. Useful during development.
    '''
    session = csvn.repos.RemoteRepository(url, user.toCsvn())
    # this could be the implementation for ?s=youngest
    return "Latest revision is %d" % session.latest_revnum()


class SvnAccess(object):
    '''
    Read-only subversion operations
    
    To be able to unit tests this class it can not have dependencies to mod_python
    '''


    def __init__(self, rootUrl, user, accept=None):
        '''
        Constructor
        '''
        self.session = csvn.repos.RemoteRepository(rootUrl, user.toCsvn())
        
    def kind(self, path, rev=None):
        k = self.session.check_path(path, rev, False)
        return "%s" % csvn.core.svn_node_kind_to_word(k)
        
    def proplist(self, path, rev=-1):
        # seems to segfault regardless of argument
        props = self.session.proplist(path, rev)
        # assume accept json
        #return json.dumps(props, sort_keys=True, indent=4)
        return repr(props).replace(',', ',\n')
        

class SvnEdit(SvnAccess):
    
    def __init__(self, rootUrl, user, message):
        SvnAccess.__init__(self, rootUrl, user)
        self.message = message
        pass
    
    def save(self, path, text):
        tmp = tempfile.NamedTemporaryFile()
        tmp.write(text)
        tmp.flush()
        txn = self.session.txn()
        txn.upload(path, tmp.name)
        result = txn.commit(self.message)
        tmp.close()
        return result


class User():
    '''
    Representing credentials.
    Should support anonymous access for repositories that do so.
    
    >>> User('testuser', 'secret').toCsvn().username()
    'testuser'
    
    >>> User().isAnonymous()
    True
    '''
    
    def __init__(self, username='', password=''):
        '''
        Just calling superclass constructor
        '''
        self.username = username
        self.password = password

    def toCsvn(self):
        return csvn.auth.User(self.username, self.password)

    def isAnonymous(self):
        # csvn hangs on None in username
        return self.username == ''

class Accept:
    """
    Specifies the requested content types
    and the type chosen by the operation
    """
    def __init__(self):
        '''
        Sets default content type
        '''
        self.chosen = 'text/plain'
    
    def choose(self, contentType):
        """ marks the chose content type from the operation """
        self.chosen = contentType

    def getContentType(self):
        return self.chosen


if __name__ == "__main__":
    import doctest
    doctest.testmod()
