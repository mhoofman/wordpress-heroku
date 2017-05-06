(function () {
  "use strict";

  var walk = require('../lib/walk')
    , fs = require('fs')
    , options
    , walker
    ;

  options = {
      listeners: {
          names: function (root, nodeNamesArray) {
            nodeNamesArray.sort(function (a, b) {
              if (a > b) return 1;
              if (a < b) return -1;
              return 0;
            });
          }
        , directories: function (root, dirStatsArray, next) {
            // dirStatsArray is an array of `stat` objects with the additional attributes
            // * type
            // * error
            // * name
            
            next();
          }
        , file: function (root, fileStats, next) {
            fs.readFile(fileStats.name, function () {
              // doStuff
              console.log(root, fileStats.name);
              next();
            });
          }
        , errors: function (root, nodeStatsArray, next) {
            next();
          }
      }
  };

  walker = walk.walkSync("/tmp", options);

  console.log("all done");
}());
