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
  emitter.on("directories", function (path, dirs) {
    count += 1;
    console.log('[' + count + '] REMOVED: ' + [path,dirs.splice(0,1).name].join('/'));
    console.log(dirs);
  });

  emitter.on("files", function (path, files) {
    count += 1;
    console.log('[' + count + '] F:' + [path,files[0].name].join('/'));
    console.log(files);
  });
}());
