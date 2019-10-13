"use strict";

// theme options
const assetsPath = 'assets/';
const themeName = 'sockman';
const options = {
  appName: 'sockman',
  bgColor: '#fff',
  themeColor: '#fff'
};
const livereloadPort = '35729';

// imports
const gulp = require('gulp');
const livereload = require('gulp-livereload');

// tasks
const sass = require('./gulp-tasks/styles.js')(assetsPath);
const js = require('./gulp-tasks/es-scripts.js')(assetsPath);
const svgs = require('./gulp-tasks/svgs.js')(assetsPath);
const assets = require('./gulp-tasks/assets.js')(assetsPath);
const favicons = require('./gulp-tasks/favicons.js')(assetsPath, options, themeName);

function watch() {
  gulp.watch(sass.src, sass.dev);
  gulp.watch(js.src, js.dev);
  gulp.watch(assets.src, assets.copy);
  gulp.watch(svgs.src, svgs.optimize);

  livereload.listen({
    host: '0.0.0.0',
    port: livereloadPort
  });
};

exports.default = gulp.series(
  assets.clean,
  gulp.parallel(
    assets.copy,
    svgs.optimize,
    sass.dev,
    js.dev
  ),
  watch
);

exports.build = gulp.series(
  assets.clean,
  gulp.parallel(
    assets.copy,
    svgs.optimize,
    sass.build,
    js.build
  )
);

exports.favicons = favicons;
