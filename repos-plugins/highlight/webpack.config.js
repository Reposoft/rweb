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
       //{ test: /\.css$/, loader: "style-loader!css-loader" }
     ]
   }
 };
