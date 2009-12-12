
// disable default initialization because it adds search box to every page
ReposSearch_onready = false;

// integrate search box on start page and in repository index
Repos.service('index/', ReposSearch.initDefault);
Repos.service('open/start/', ReposSearch.initDefault);
Repos.service('home/', ReposSearch.initDefault);
