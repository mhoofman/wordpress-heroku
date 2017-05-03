(function () {
  var fs = require('fs'),
    Futures = require('futures'),
    joinPath = require('path').join,
    util = require('util'),
    ev = require("events"),
    emitter = new ev.EventEmitter(),
    oneNodeEvent = [
      "file",
      "directory",
      "blockDevice",
      "characterDevice",
      "symbolicLink",
      "fifo",
      "socket"
    ],
    multiNodeEvents = [
      // multiple
      "files",
      "directories",
      "blockDevices",
      "characterDevices",
      "symbolicLinks",
      "fifos",
      "sockets"
    ],
    eventsTpl = {
      listeners: function () { return []; },
      next: function () { return; }
    },
    events = {},
    nexts = {};

  function newVersion() {
    throw new Error("see README.md at  http://github.com/coolaj86/node-walk");
  }

  function noop() {
  }

  function remove(arr, obj) {
    return arr.splice(arr.indexOf(obj), 1);
  }

  oneNodeEvent.forEach(function (key) {
    var e = events[key] = {}, next;

    Object.keys(eventsTpl).forEach(function (k) {
      e[k] = eventsTpl[k]();
    });

    emitter.on("newListener", function (ev, listener) {
      var count = 0,
        num = e.listeners.length + 1;

      e.listeners.push(listener);
      e.next = function (cb) {
        cb = noop;
        return function () {
          if (count === num) { cb(); }
          count += 1;
        };
      };
    });

    // TODO
    next = function () {
      
    };
  });

  function sortNodesByType(path, stats, o, cb) {
    if (stats.isFile()) {
      o.files.push(stats);
      emitter.emit("file", path, stats, (nexts["file"]||noop)(cb));
    } else if (stats.isDirectory()) {
      o.dirs.push(stats);
      emitter.emit("directory", path, stats, function () {
        remove(o.dirs, stats);
      }, (nexts["directory"]||noop)(cb));
    } else if (stats.isBlockDevice()) {
      o.blocks.push(stats);
      emitter.emit("blockDevice", path, stats, (nexts["blockDevice"]||noop)(cb));
    } else if (stats.isCharacterDevice()) {
      o.chars.push(stats);
      emitter.emit("characterDevice", path, stats, (nexts["characterDevice"]||noop)(cb));
    } else if (stats.isSymbolicLink()) {
      o.links.push(stats);
      emitter.emit("symbolicLink", path, stats, (nexts["symbolicLink"]||noop)(cb));
    } else if (stats.isFIFO()) {
      o.fifos.push(stats);
      emitter.emit("fifo", path, stats, (nexts["fifo"]||noop)(cb));
    } else if (stats.isSocket()) {
      o.sockets.push(stats);
      emitter.emit("socket", path, stats, (nexts["socket"]||noop)(cb));
    } else {
      // emitter.emit("error", stats);
      util.debug(stats.name + 'is not of any node type');
    }
  }

  /*
  import os
  from os.path import join, getsize
  for root, dirs, files in os.walk('python/Lib/email'):
      print root, "consumes",
      print sum(getsize(join(root, name)) for name in files),
      print "bytes in", len(files), "non-directory files"
      if 'CVS' in dirs:
          dirs.remove('CVS')  # don't visit CVS directories
  */

  /*
    fs.walk(path, function ({ err, root, dirs, files }) {}, {
      // currently ignored
      topdown: boolean,
      onerror: boolean, // ignored
      followLinks: boolean // lstat or stat
    });
  */

  function walk(firstPath, options, callback) {
    options = options || {};
    var fstat = options.followLinks ? fs.stat : fs.lstat,
      subscription = Futures.subscription();

    if (callback) { subscription.subscribe(callback); }

    function readDir(path) {
      var p = Futures.promise();

      fs.readdir(path, function (err, files) {
        if (err) {
          err.path = path;
          subscription.deliver(err, path);
          // Signal the completion of this readdir attempt
          p.fulfill();
          return;
        }

        // TODO fix futures sequence to not require a first function like this
        var s = Futures.sequence(function(n){n();}), 
          nodes = [], 
          o = {
            errors: [], 
            dirs: [], 
            files: [],
            links: [], 
            blocks: [], 
            chars: [], 
            fifos: [], 
            sockets: []
          };

        files.forEach(function (file) {
          // pushes onto the sequence stack without recursion
          s.then(function (next) {
            fstat(joinPath(path, file), function (err, stats) {
              stats = stats || {};
              stats.name = file;
              nodes.push(stats);

              if (err) {
                stats.err = err;
                o.errors.push(stats);
              } else {
                sortNodesByType(path, stats, o);
              }

              next();
            });
          });
        });

        s.then(function (next) {
          var s2 = Futures.sequence(function(n){n();});
          if (nodes.length > 0) {
            subscription.deliver(undefined, path, o.errors, o.dirs, o.files, o.links, o.blocks, o.chars, o.fifos, o.sockets);
            if (o.errors.length > 0) {
              emitter.emit("errors", path, o.errors);
            }
            if (o.dirs.length > 0) {
              emitter.emit("directories", path, o.dirs);
            }
            if (o.files.length > 0) {
              emitter.emit("files", path, o.files);
            }
            if (o.links.length > 0) {
              emitter.emit("symbolicLinks", path, o.links);
            }
            if (o.blocks.length > 0) {
              emitter.emit("blockDevices", path, o.blocks);
            }
            if (o.chars.length > 0) {
              emitter.emit("characterDevices", path, o.chars);
            }
            if (o.fifos.length > 0) {
              emitter.emit("fifos", path, o.fifos);
            }
            if (o.sockets.length > 0) {
              emitter.emit("sockets", path, o.fifos);
            }
            p.fulfill();

            o.dirs.forEach(function (dir) {
              s2.then(function (next2) {
                readDir(joinPath(path, dir.name))
                  .when(function () { next2(); });
              });
            });

            next();
          }
        });

      });

      return p.passable();
    }

    readDir(firstPath) //.whenever(callback);

    emitter.whenever = subscription.subscribe;
    return emitter;
  }

  newVersion.walk = walk;
  newVersion.remove = remove;
  module.exports = newVersion;
}());
