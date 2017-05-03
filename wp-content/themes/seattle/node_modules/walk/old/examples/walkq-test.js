#!/usr/bin/env node
(function () {
  var walk = require('../lib/walk-queue'),
    emitter = walk(process.argv[2] || '.'),
    _ = require('underscore'),
    count = 0;

  emitter.on('error', function (err, path, files) {
    console.log(err);
  });
  emitter.on('nodes', function (path, files, next) {
    //next();
    var filenames = _.map(files, function (file) {
      return path + '/' + file;
    })
    filenames.forEach(function (name) {
      count += 1;
      console.log('[' + count  + '] ' + name)
    });
    //filenames.forEach(console.log);
    //console.log(_.pluck(files, 'name'));
  });
  emitter.on('directories', function (path, files, next) {
    next();
  });
  emitter.on('directory', function (path, stat, next) {
    //console.log(stat.name);
    next();
  });
  emitter.on('end', function () {
    console.log("The eagle has landed");
  });
}());
