
require("css!highlight.js/styles/default.css");

var highlight = require('./highlight-config.js');

onmessage = function(event) {
  var result = highlight.highlightAuto(event.data);
  postMessage(result.value);
};
