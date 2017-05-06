#!/usr/bin/env node
(function () {
  "use strict";

  var walk = require('../lib/walk-jqueue-2'),
    count = 0;

  function sort(a,b) {
    a= a.toLowerCase();
    b= b.toLowerCase();
    if (a > b) return -1;
    if (a < b) return  1;
    else       return  0;
  }

  process.argv.forEach(function(val, index, array) {
    if (index > 1) {
      emitter = walk(val);
      emitter.on('name', function (path, file, stat) {
        count += 1;
        console.log( ["[", count, "] ", path, '/', file].join('') )
      });
      emitter.on('names', function (path, files, stats) {
        files.sort(sort);
        //console.log('sort: ' + files.join(' ; '));
      });
      emitter.on('error', function () {
        // ignore
      });
      emitter.on('stat', function (path, file, stat) {
        //console.log('stat: ' + file);
      });
      emitter.on('stats', function (path, files, stats) {
        //console.log('stats: ' + files.join(' ; '));
      });
      emitter.on('end', function () {
        console.log("The eagle has landed.");
      });
    }
  });

}());
