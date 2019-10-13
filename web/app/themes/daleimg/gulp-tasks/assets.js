module.exports = function(assetsPath) {
  const gulp = require('gulp');
  const changed = require('gulp-changed');
  const del = require('del');
  const { join } = require('path');

  const src = [
    join(assetsPath, '/src/**/*'),
    '!' + join(assetsPath, '/src/js'),
    '!' + join(assetsPath, '/src/js/**/*'),
    '!' + join(assetsPath, '/src/strands'),
    '!' + join(assetsPath, '/src/strands/**/*'),
    '!' + join(assetsPath, '/src/sass'),
    '!' + join(assetsPath, '/src/sass/**/*'),
    '!' + join(assetsPath, '/src/svg/**/*.svg')
  ];

  const dist = join(assetsPath, '/dist/');

  function assets_copy() {
    return gulp.src(src)
      .pipe(changed(dist))
      .pipe(gulp.dest(dist));
  }

  /**
   * delete everything in dist directory
   */
  function assets_clean() {
    return del([dist+'*']);
  }

  return {
    copy: assets_copy,
    clean: assets_clean,
    src
  };
};
