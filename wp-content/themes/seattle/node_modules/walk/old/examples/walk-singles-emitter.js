#!/usr/bin/env node

(function () {
  var walk = require('../lib/walk').walk,
    // todo remove(arr, obj, true), remove(arr, from, to)
    remove = require('../lib/walk').remove,
    util = require('util');

  Array.prototype.removeAt = function (i) {
    return this.splice(i, 1)[0];
  }

  var count = 0, emitter = walk('/System');
  emitter.on("directory", function (path, stats) {
    count += 1;
    console.log('[' + count + '] D:' + [path,stats.name].join('/'));
  });

  emitter.on("file", function (path, stats) {
    count += 1;
    console.log('[' + count + '] F:' + [path,stats.name].join('/'));
  });
}());
