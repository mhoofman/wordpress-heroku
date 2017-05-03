#!/usr/bin/env node

(function () {
  var walk = require('../lib/walk').walk,
    remove = require('../lib/walk').remove,
    util = require('util'),
    emitter = walk('/System'),
    whenever;

  whenever = function (cb, eb) {
    emitter.whenever(function (err, path, errs, dirs, files, links) {
      if (err || errs.length) {
        eb(path, err, errs);
        if (err) { return; }
      }
      cb(path, dirs, files.concat(links));
    });
  }

  // A much more pythonic style
  whenever(function (path, dirs, files) {
    console.log(path);
    if (dirs.length) {
      console.log(dirs);
    }
    if (files.length) {
      console.log(files);
    }
  }, function (path, err, errs) {
    util.debug(path);
    if (err) {
      util.debug(err);
    } else if (errs.length) {
      util.debug(errs);
    } else {
      throw new Error("No Error when Error Expected");
    }
  });
}());
