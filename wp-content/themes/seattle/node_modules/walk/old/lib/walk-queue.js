(function () {
  "use strict";

  var fs = require('fs'),
    fstat = fs.lstat,
    Futures = require('futures'),
    EventEmitter = require('events').EventEmitter,
    upath = require('path'),
    // "FIFO" isn't easy to convert to camelCase and back reliably
    isFnodeTypes = [
      "isFile", "isDirectory",  "isBlockDevice",  "isCharacterDevice",  "isSymbolicLink", "isFIFO", "isSocket"
    ],
    fnodeTypes = [
      "file",   "directory",    "blockDevice",    "characterDevice",    "symbolicLink",   "FIFO",   "socket"
    ],
    fnodeTypesPlural = [
      "files",  "directories",  "blockDevices",   "characterDevices",   "symbolicLinks",  "FIFOs",  "sockets"
    ];

  // Get the current number of listeners (which may change)
  // Emit events to each listener
  // Wait for all listeners to `next()` before continueing
  // (in theory this may avoid disk thrashing)
  function emitSingleEvents(emitter, path, stats, next) {
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
  function emitPluralEvents(emitter, path, nodes, next) {
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
  function sortFnodesByType(stats, fnodes) {
    isFnodeTypes.forEach(function (isType, i) {
      if (stats[isType]()) {
        if (stats.type) { throw new Error("is_" + type + " and " + isType); }
        stats.type = fnodeTypes[i];
        fnodes[fnodeTypesPlural[i]].push(stats);
        //console.log(isType, fnodeTypesPlural[i], stats.name);
        // TODO throw to break;
      }
    });
    /*
    // Won't really ever happen
    if (!stats.type) {
      stats.error = new Error(upath.join(path, stats.name) + ' isAnUndefinedType');
    }
    */
  }

  function create(path) {
    var emitter = new EventEmitter(),
      paths = [],
      path;

    function next() {
      // path could be local if dirHandler were anonymous
      //console.log('LEN: '+ paths.length);
      if (0 == paths.length) {
        emitter.emit('end');
        return;
      }
      path = paths.pop();
      //console.log("POP: " + path);
      fs.readdir(path, dirHandler);
    }

    function nodesHandler(nodes, args) {
      //console.log('USE: ' + path);
      var statses = [];

      var nodeGroups = {};
      fnodeTypesPlural.concat("nodes", "errors").forEach(function (fnodeTypePlural) {
        nodeGroups[fnodeTypePlural] = [];
      });

      args.forEach(function (arg, i) {
        var file = nodes[i],
          err = arg[0],
          stats = arg[1];

        if (err) {
          stats = { error: err, name: file };
          emitter.emit('error', err, path, stats);
        }
        if (stats) {
          stats.name = file;
          sortFnodesByType(stats, nodeGroups);
          emitter.emit('stat', path, stats);
        }
      });
      emitter.emit('stats', path, statses);
      nodeGroups['directories'].forEach(function (stat) {
        paths.push(path + '/' + stat.name);
        //console.log('PUSH: ' + path + '/' + stat.name);
      });
      /*
      //console.log('USE: ' + path);
      next();
      */
      emitPluralEvents(emitter, path, nodeGroups, next);
    }

    function dirHandler(err, nodes) {
      //console.log("HANDLE: " + path);
      var join = Futures.join(),
        i;

      if (err) {
        emitter.emit('error', err, path);
      }
      if (!nodes || 0 == nodes.length) {
        //console.log('EMPTY: ' + path);
        return next();
      }
      // TODO don't duplicate efforts
      emitter.emit('nodes', path, nodes);

      for (i = 0; i < nodes.length; i += 1) {
        fstat(path + '/' + nodes[i], join.deliverer());
      }

      join.when(function () {
        var args = Array.prototype.slice.call(arguments);
        nodesHandler(nodes, args);
      });
    }

    //paths.push([path]);
    paths.push(path);


    next();
    return emitter;
  }

  module.exports = create;
}());
