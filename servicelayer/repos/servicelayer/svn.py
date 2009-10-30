'''

'''

import csvn
import csvn.auth
import csvn.repos


def test(url, user):
    '''
    Test svn access given url and credentials. Useful during development.
    '''
    session = csvn.repos.RemoteRepository(url, user.toCsvn())
    # this could be the implementation for ?s=youngest
    return "Latest revision is %d" % session.latest_revnum();


class SvnAccess(object):
    '''
    classdocs
    '''


    def __init__(self, targetUrl, user):
        '''
        Constructor
        '''
        self.repo = csvn.repos.RemoteRepository(targetUrl, user.toCsvn())
        

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
        return csvn.auth.User(self.username, self.password)


class Accept:
    """
    Specifies the requested content types
    and the type chosen by the operation
    """
    def choose(self):
        """ marks the chose content type from the operation """
        pass


if __name__ == "__main__":
    import doctest
    doctest.testmod()
