#!/usr/bin/env node
(function () {
  "use strict";

  var walk = require('walk')
    , count = 0
    , emitter
    , saneCount = 0
    ;

  function sort(a,b) {
    a= a.toLowerCase();
    b= b.toLowerCase();
    if (a > b) return -1;
    if (a < b) return  1;
    return  0;
  }

  process.argv.forEach(function(startpath, index) {
    if (index > 1) {
      emitter = walk.walk(startpath);

  // Non-`stat`ed Nodes
      emitter.on('name', function (path, file, stat) {
        saneCount += 1;
        //console.log( ["[", count, "] ", path, '/', file].join('') )
        //console.log( [path, '/', file].join('') )
      });
      emitter.on('names', function (path, files, stats) {
        files.sort(sort);
        //console.log('sort: ' + files.join(' ; '));
      });



  // Single `stat`ed Nodes
      emitter.on('error', function (path, err, next) {
        next()
        // ignore
      });
      emitter.on('directoryError', function (path, stats, next) {
        next();
      });
      emitter.on('nodeError', function (path, stats, next) {
        next();
      });
      /*
      emitter.on('node', function (path, stat, next) {
        count += 1;
        console.log( [path, '/', stat.name].join('') )
        //console.log( ["[", count, "] ", path, '/', stat.name].join('') )
        next();
      });
      */
      emitter.on('file', function (path, stat, next) {
        count += 1;
        console.log( [path, '/', stat.name].join('') )
        //console.log( ["[", count, "] ", path, '/', stat.name].join('') )
        next();
      });
      emitter.on('directory', function (path, stat, next) {
        count += 1;
        console.log( [path, '/', stat.name].join('') )
        next();
      });
      emitter.on('symbolicLink', function (path, stat, next) {
        count += 1;
        console.log( [path, '/', stat.name].join('') )
        next();
      });
      /*
      emitter.on('blockDevice', function (path, stat, next) {
        next();
      });
      emitter.on('characterDevice', function (path, stat, next) {
        next();
      });
      emitter.on('FIFO', function (path, stat, next) {
        next();
      });
      emitter.on('socket', function (path, stat, next) {
        next();
      });
      */



    // Grouped `stat`ed Nodes
      emitter.on('errors', function (path, stats, next) {
        next();
      });
      /*
      emitter.on('nodes', function (path, stats, next) {
        next();
      });
      */
      emitter.on('files', function (path, stats, next) {
        next();
      });
      emitter.on('directories', function (path, stats, next) {
        //delete stats[1];
        next();
      });
      emitter.on('symbolicLinks', function (path, stats, next) {
        next();
      });
      /*
      emitter.on('blockDevices', function (path, stats, next) {
        next();
      });
      emitter.on('characterDevices', function (path, stats, next) {
        next();
      });
      emitter.on('FIFOs', function (path, stats, next) {
        next();
      });
      emitter.on('sockets', function (path, stats, next) {
        next();
      });
      */



    // The end of all things
      emitter.on('end', function () {
        console.log("The eagle has landed. [" + count + " == " + saneCount + "]");
      });
    }
  });

}());
