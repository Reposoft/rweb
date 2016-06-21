
require("css!highlight.js/styles/default.css");

onmessage = function(event) {
  var highlight = require('highlight.js');
  console.log('In highlight worker. Content:', event.data.substring(0,20), '...');
  var result = highlight.highlightAuto(event.data);
  console.log('Highlighted: ', result.value.substring(0,20), '...')
  postMessage(result.value);
}
