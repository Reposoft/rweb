
require('style!css!highlight.js/styles/github.css');

Repos.service('open/file/', function() {
  var code = document.querySelector('#file');
  if (!code) throw new Error('Node code block found');
  var content = code.textContent;
  if (!content) {
    console.log('Content is empty');
    return;
  }
  var worker = new Worker('/repos-plugins/highlight/bundle-highlight.worker.js');
  worker.onmessage = function(event) {
    code.innerHTML = event.data;
  }
  worker.postMessage(content);
});
