
// disable default initialization because it adds search box to every page
ReposSearch.onready = false;

// integrate search box on start page and in repository index
Repos.service('index/', ReposSearch.init);
Repos.service('open/start/', ReposSearch.init);
//Repos.service('home/', ReposSearch.init);
