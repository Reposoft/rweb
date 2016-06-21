const webpack = require('webpack');

module.exports = {
   entry: {
     "highlight.load.js": "./index",
     "highlight.worker.js": "./worker"
   },
   output: {
     path: './',
     filename: 'bundle-[name]'
   },
   module: {
     loaders: [
     ]
   },
   plugins: [
    new webpack.optimize.UglifyJsPlugin({
      exclude: "bundle-highlight.load.js"
    })
  ]
 };
