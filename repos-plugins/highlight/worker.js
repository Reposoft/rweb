
require("css!highlight.js/styles/default.css");

var highlight = require('highlight.js');

onmessage = function(event) {
  console.log('In highlight worker. Content:', event.data.substring(0,20), '...');
  var result = highlight.highlightAuto(event.data);
  console.log('Highlighted: ', result.value.substring(0,20), '...')
  postMessage(result.value);
}
