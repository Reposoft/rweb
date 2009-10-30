'''

'''

import csvn
import csvn.auth
import csvn.repos


def test(url, username, password):
    '''
    Test svn access given url and credentials. Useful during development.
    '''
    user = csvn.auth.User(username, password)
    session = csvn.repos.RemoteRepository(url, user)
    # this could be the implementation for ?s=youngest
    return "Latest revision is %d" % session.latest_revnum();


class SvnAccess(object):
    '''
    classdocs
    '''


    def __init__(self, repositoryRootUrl, user):
        '''
        Constructor
        '''
        self.repo = csvn.repos.RemoteRepository(repositoryRootUrl)
        


class SvnUser(csvn.auth.User):
    
    def __init__(self, username=None, password=None):
        '''
        Constructor
        '''
        
        
    