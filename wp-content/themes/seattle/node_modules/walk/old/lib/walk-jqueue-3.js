(function () {
  "use strict"

  // Array.prototype.forEachAsync(next, item, i, collection)
  require('futures/forEachAsync');

  var fs = require('fs'),
    EventEmitter = require('events').EventEmitter,
    TypeEmitter = require('./node-type-emitter');

  // 2010-11-25 jorge@jorgechamorro.com
  function create(pathname, cb) {
    var emitter = new EventEmitter(),
      q = [],
      queue = [q],
      curpath;

    function walk() { 
      fs.readdir(curpath, function(err, files) {
        if (err) {
          emitter.emit('error', curpath, err);
        }
        // XXX bug was here. next() was omitted
        if (!files || 0 == files.length) {
          return next();
        }
        var stats = [],
          fnodeGroups = TypeEmitter.createNodeGroups();

        // TODO could allow user to selectively stat
        // and don't stat if there are no stat listeners
        emitter.emit('names', curpath, files);
        files.forEachAsync(function (cont, file) {
          emitter.emit('name', curpath, file);
          fs.lstat(curpath + '/' + file, function (err, stat) {
            if (err) {
              emitter.emit('error', curpath, err);
            }
            if (!stat) {
              cont();
            }
            stat.name = file;
            stats.push(stat);
            //emitter.emit('stat', curpath, file, stat);
            TypeEmitter.sortFnodesByType(stat, fnodeGroups);
            TypeEmitter.emitNodeType(emitter, curpath, stat, cont);
          });
        }).then(function () {
          var dirs = []
          //emitter.emit('stats', curpath, files, stats);
          TypeEmitter.emitNodeTypeGroups(emitter, curpath, fnodeGroups, function () {
            fnodeGroups.directories.forEach(function (stat) {
              dirs.push(stat.name);
            });
            dirs.forEach(fullPath);
            queue.push(q = dirs);
            next();
          });
        });
      });
    }
    
    function next() {
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
    
    curpath = pathname;
    walk();
    
    return emitter;
  }

  module.exports = create;
}());
