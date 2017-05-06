(function () {
  var walk = require("../lib/walk.js"),
    emit = walk(process.argv[2] || "/tmp"),
    util = require('util'),
    path = require('path');

  // nor the root, nor the node should ever be empty
  walk.fnodeTypesPlural.forEach(function (fnodeType) {
    emit.on(fnodeType, function (root, nodes, next) {
      if (!nodes || !nodes.length || !root) {
        console.log(fnodeType, "empty set", root, nodes.length); //JSON.stringify(nodes));
      }
      next();
    });
  });
  walk.fnodeTypes.forEach(function (fnodeType) {
    emit.on(fnodeType, function (root, node, next) {
      if (!node || !node.name || !root) {
        console.log(fnodeType, "empty item", root, node.name); //JSON.stringify(node));
      }
      next();
    });
  });
  emit.on('directory', function (root, dir, next) {
    console.log(path.join(root, dir.name));
    next();
  });
  /*
  emit.on('file', function (root, file, next) {
    console.log(path.join(root, file.name));
    next();
  });
  */
  emit.on('end', function () {
    console.log("All Done!");
  });
}());

