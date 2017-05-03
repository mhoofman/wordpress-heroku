#!/usr/bin/env node

(function () {
  var walk = require('../lib/walk').walk,
    // todo remove(arr, obj, true), remove(arr, from, to)
    remove = require('../lib/walk').remove,
    util = require('util'),
    emitter = walk('/System');

  emitter.whenever(function (err, path, errs, dirs, files, links, blocks, chars, fifos, sockets) {
    // If there was an error reading the directory
    // then we can return already
    if (err) {
      util.debug('ERROR reading path: ' + path + '\n' +  util.inspect(err));
      return;
    }

    // If there was an error `stat`ing a node
    // then there may still be other nodes read successfully
    if (errs) {
      errs.forEach(function (err) {
        util.debug('ERROR fs.stat node: ' + path + '\n' +  util.inspect(err));
      });
    }

    // 
    dirs.forEach(function (item, i, arr) {
      if (item.name.match(/trash/i)) {
        console.log('REMOVE: found a trash');
        remove(arr, item);
      }
    });
    console.log("PATH: " + path);
    console.log("FILES: " + util.inspect(files));
  });

}());
