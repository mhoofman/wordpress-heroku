// TODO
//  * add types by listener dynamically
//  * unroll loops for better readability?
//  * should emitted errors wait for `next()`?
(function (undefined) {
  var fs = require('fs'),
    upath = require('path'),
    util = require('util'),
    Futures = require('futures'),
    events = require('events'),
    noop = function () {},
    // "FIFO" isn't easy to convert to camelCame and back reliably
    isFnodeTypes = [
      "isFile", "isDirectory",  "isBlockDevice",  "isCharacterDevice",  "isSymbolicLink", "isFIFO", "isSocket"
    ],
    fnodeTypes = [
      "file",   "directory",    "blockDevice",    "characterDevice",    "symbolicLink",   "FIFO",   "socket"
    ],
    fnodeTypesPlural = [
      "files",  "directories",  "blockDevices",   "characterDevices",   "symbolicLinks",  "FIFOs",  "sockets"
    ];

  function newVersion() {
    throw new Error("New Version. Please see API on github.com/coolaj86/node-walk");
  }

  // Create a new walk instance
  function create(path, options, cb) {
    if (cb) {
      newVersion();
    }
    
    var emitter = new events.EventEmitter(),
      fstat = (options||{}).followLinks ? fs.stat : fs.lstat;


    // Get the current number of listeners (which may change)
    // Emit events to each listener
    // Wait for all listeners to `next()` before continueing
    // (in theory this may avoid disk thrashing)
    function emitSingleEvents(path, stats, next) {
      var num = 1 + emitter.listeners(stats.type).length + emitter.listeners("node").length;

      function nextWhenReady() {
        num -= 1;
        if (0 === num) { next(); }
      }

      emitter.emit(stats.type, path, stats, nextWhenReady);
      emitter.emit("node", path, stats, nextWhenReady);
      nextWhenReady();
    }


    // Since the risk for disk thrashing among anything
    // other than files is relatively low, all types are
    // emitted at once, but all must complete before advancing
    function emitPluralEvents(path, nodes, next) {
      var num = 1;

      function nextWhenReady() {
        num -= 1;
        if (0 === num) { next(); }
      }

      fnodeTypesPlural.concat(["nodes", "errors"]).forEach(function (fnodeType) {
        if (0 === nodes[fnodeType].length) { return; }
        num += emitter.listeners(fnodeType).length;
        emitter.emit(fnodeType, path, nodes[fnodeType], nextWhenReady);
      });
      nextWhenReady();
    }


    // Determine each file node's type
    // 
    function sortFnodesByType(path, stats, fnodes, nextFile) {
      isFnodeTypes.forEach(function (isType, i) {
        if (stats[isType]()) {
          if (stats.type) { throw new Error("is_" + type + " and " + isType); }
          stats.type = fnodeTypes[i];
          fnodes[fnodeTypesPlural[i]].push(stats);
          // TODO throw to break;
        }
      });
      if (!stats.type) { throw new Error(upath.join(path, stats.name) + ' isAnUndefinedType'); }
      emitSingleEvents(path, stats, nextFile);
    }


    // Asynchronously get the stats 
    //
    function getStats(path, files, walkDirs) {
      var nodeGroups = {};

      fnodeTypesPlural.concat("nodes", "errors").forEach(function (fnodeTypePlural) {
        nodeGroups[fnodeTypePlural] = [];
      });

      function nextFile() {
        var file = files.pop(), dirs = [], fnames = [];

        if (undefined === file) {
          emitPluralEvents(path, nodeGroups, function () {
            nodeGroups.directories.forEach(function (dir) {
              dirs.push(dir.name);
            });
            walkDirs(dirs);
          });
          return;
        }

        fstat(upath.join(path, file), function (err, stats) {
          stats = stats || {};
          stats.name = file;
          nodeGroups.nodes.push(stats);
          if (err) {
            stats.error = err;
            stats.type = 'error';
            nodeGroups.errors.push(stats);
            //emitter.emit('fileError', path, stats, noop);
            return nextFile();
          }
          sortFnodesByType(path, stats, nodeGroups, nextFile);
        });
      }
      nextFile();
    }

    function walk(path, next) {
      fs.readdir(path, function (err, nodes) {
        if (err) { 
          emitter.emit('directoryError', path, { error: err, name: path }, noop);
          return next(); /*TODO*/ throw err;
        }
        getStats(path, nodes, function (dirs) {
          walkDirs(path, dirs, next);
        });
      });
    }

    function walkDirs(path, dirs, cb) {
      function nextDir() {
        var dir = dirs.pop();
        if (undefined === dir) {
          delete dirs;
          return cb();
        }
        walk(upath.join(path, dir), nextDir);
      }
      nextDir();
    }

    walk(upath.normalize(path), function () {
      emitter.emit('end');
    });
    emitter.walk = newVersion;
    emitter.whenever = newVersion;
    return emitter;
  }
  module.exports = create;
  module.exports.isFnodeTypes = isFnodeTypes;
  module.exports.fnodeTypes = fnodeTypes;
  module.exports.fnodeTypesPlural = fnodeTypesPlural;
}());
