'''

'''

from csvn.core import *
from csvn.repos import RemoteRepository
from csvn.auth import User


def test(url, user):
    '''
    Test svn access given url and credentials. Useful during development.
    '''
    session = csvn.repos.RemoteRepository(url, user.toCsvn())
    # this could be the implementation for ?s=youngest
    return "Latest revision is %d" % session.latest_revnum()


class SvnAccess(object):
    '''
    classdocs
    '''


    def __init__(self, targetUrl, user, accept):
        '''
        Constructor
        '''
        self.session = csvn.repos.RemoteRepository(targetUrl, user.toCsvn())
        
        
    def proplist(self, rev=-1):
        # seems to segfault regardless of argument
        props = self.session.proplist('')
        # assume accept json
        return json.dumps(props, sort_keys=True, indent=4)
        

class SvnEdit(SvnAccess):
    
    def __init__(self, targetUrl, user, message):
        SvnAccess(self, targetUrl, user)
        pass


class User():
    '''
    Representing credentials.
    Should support anonymous access for repositories that do so.
    
    >>> User('testuer', 'secret').toCsvn().username()
    testuser
    
    >>> User().isAnonymous()
    True
    '''
    
    def __init__(self, username=None, password=None):
        '''
        Just calling superclass constructor
        '''
        self.username = username
        self.password = password

    def toCsvn(self):
        #return csvn.auth.User(self.username, self.password)
        pass


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
