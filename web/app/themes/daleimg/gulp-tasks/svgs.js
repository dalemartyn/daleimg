module.exports = function(assetsPath) {
  const gulp = require('gulp');
  const changed = require('gulp-changed');
  const imagemin = require('gulp-imagemin');
  const imageminSvgo = require('imagemin-svgo');
  const { join } = require('path');

  const src = join(assetsPath, 'src/svg/**/*.svg');
  const base = join(assetsPath, 'src/');
  const dist = join(assetsPath, 'dist/');

  function optimize_svgs() {
    // SVGO options at https://github.com/svg/svgo#what-it-can-do
    return gulp.src(src, {
        base: base
      })
      .pipe(changed(dist))
      .pipe(imagemin([
        imageminSvgo({
          plugins: [{
            cleanupAttrs: true,
          }, {
            removeDoctype: true,
          }, {
            removeXMLProcInst: true,
          }, {
            removeComments: true,
          }, {
            removeMetadata: true,
          }, {
            removeTitle: true,
          }, {
            removeDesc: true,
          }, {
            removeUselessDefs: true,
          }, {
            removeXMLNS: false
          }, {
            removeEditorsNSData: true,
          }, {
            removeEmptyAttrs: true,
          }, {
            removeHiddenElems: true,
          }, {
            removeEmptyText: true,
          }, {
            removeEmptyContainers: true,
          }, {
            removeViewBox: false,
          }, {
            cleanupEnableBackground: true,
          }, {
            convertStyleToAttrs: true,
          }, {
            convertColors: true,
          }, {
            convertPathData: true,
          }, {
            convertTransform: true,
          }, {
            removeUnknownsAndDefaults: true,
          }, {
            removeNonInheritableGroupAttrs: true,
          }, {
            removeUselessStrokeAndFill: true,
          }, {
            removeUnusedNS: true,
          }, {
            cleanupIDs: true,
          }, {
            cleanupNumericValues: true,
          }, {
            moveElemsAttrsToGroup: true,
          }, {
            moveGroupAttrsToElems: true,
          }, {
            collapseGroups: true,
          }, {
            removeRasterImages: false,
          }, {
            mergePaths: true,
          }, {
            convertShapeToPath: true,
          }, {
            sortAttrs: true,
          }, {
            removeDimensions: false,
          }, {
            removeAttrs: {
              // attrs: '(stroke|fill)'
            },
          }]
        })
      ]))
      .pipe(gulp.dest(dist));
  };

  return {
    optimize: optimize_svgs,
    src
  };
};
