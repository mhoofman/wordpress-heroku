(function () {
  "use strict";

  var walk = require('../lib/walk').walk
    , path = require('path')
    , dirname = process.argv[2] || './'
    , walker
    ;

  walker = walk(dirname);

  walker.on('directories', function (root, stats, next) {
    stats.forEach(function (stat) {
      console.log('[ds]', path.join(root, stat.name));
    });
    next();
  });

  /*
  walker.on('directory', function (root, stat, next) {
    console.log('[d]', path.join(root, stat.name));
    next();
  });
  */

  walker.on('file', function (root, stat, next) {
    console.log('[f]', path.join(root, stat.name));
    next();
  });

  walker.on('end', function () {
    console.log('All Done!');
  });
}());
