// 2011-11-25 jorge@jorgechamorro.com

function walk (file, cb) {
  var fs = require('fs');
  var q= [];
  var queue= [q];
  walk2();
  
  function walk2 () { 
    cb(file);
    fs.lstat(file, function (err, stat) {
      if (err || !stat.isDirectory()) return next();
      getDirectory(function (files) {
        queue.push(q= files);
        next();
      });
    });
  }
  
  function next () {
    if (q.length) {
      file= q.pop();
      walk2();
    }
    else if (queue.length-= 1) {
      q= queue[queue.length-1];
      next();
    }
  }
  
  function getDirectory (cb) {
    fs.readdir(file, function(err, files) {
      // XXX bug was here. `next()` added by coolaj86
      if (!files) return next();
      //if (err) throw Error(err);
      files.sort(sort);
      files.forEach(fullPath);
      cb(files);
    });
  }
  
  function fullPath (v,i,o) {
    o[i]= [file, '/', v].join('');
  }
  
  function sort (a,b) {
    a= a.toLowerCase();
    b= b.toLowerCase();
    if (a > b) return -1;
    if (a < b) return  1;
    else       return  0;
  }
}

// your callback here
var ctr= 0;
function callBack (file) { console.log( ["[", ++ctr, "] ", file].join('') ) };

process.argv.forEach(function(val, index, array) {
  if (index > 1) walk(val, callBack);
});
