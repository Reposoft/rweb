
require('style!css!highlight.js/styles/default.css');

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
    console.log('Highlight worker returned', event.data && event.data.length);
    code.innerHTML = event.data;
  }
  console.log('Highlight worker post', content.length);
  worker.postMessage(content);
});
