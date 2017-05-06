(function () {
  "use strict"

  var fs = require('fs'),
    EventEmitter = require('events').EventEmitter;

  // 2010-11-25 jorge@jorgechamorro.com
  function create (pathname, cb) {
    var emitter = new EventEmitter(),
      q = [],
      queue = [q],
      curpath;

    function walk() { 
      //cb(curpath);
      fs.lstat(curpath, function (err, stat) {
        if (err) {
          emitter.emit('error', curpath, err);
        }
        if (!stat) {
          return next();
        }
        emitter.emit('node', curpath, stat);
        if (!stat.isDirectory()) {
          return next();
        }
        fs.readdir(curpath, function(err, files) {
          if (err) {
            emitter.emit('error', curpath, err);
          }
          // XXX bug was here. next() was omitted
          if (!files || 0 == files.length) {
            return next();
          }
          files.sort(sort);
          emitter.emit('nodes', curpath, files);
          files.forEach(fullPath);
          queue.push(q = files);
          next();
        });
      });
    }
    
    function next () {
      if (q.length) {
        curpath = q.pop();
        return walk();
      }
      if (queue.length -= 1) {
        q = queue[queue.length-1];
        return next();
      }
      emitter.emit('end');
    }
    
    function fullPath(v,i,o) {
      o[i]= [curpath, '/', v].join('');
    }
    
    function sort(a,b) {
      a= a.toLowerCase();
      b= b.toLowerCase();
      if (a > b) return -1;
      if (a < b) return  1;
      else       return  0;
    }

    curpath = pathname;
    walk();
    
    return emitter;
  }

  module.exports = create;
}());
