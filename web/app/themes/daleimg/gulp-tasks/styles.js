module.exports = function(assetsPath) {
  const gulp = require('gulp');
  const sass = require('gulp-sass');
  const rename = require('gulp-rename');
  const livereload = require('gulp-livereload');
  const { join } = require('path');

  const postcss = require('gulp-postcss');
  const postcssCustomProperties = require('postcss-custom-properties');
  const pxtorem = require('postcss-pxtorem');
  const autoprefixer = require('autoprefixer');
  const rev = require('gulp-rev');

  const src = join(assetsPath, '/src/sass/**/*.scss');
  const entries = [
    src,
    '!' + join(assetsPath, '/src/sass/**/_*.scss')
  ];
  const dist = join(assetsPath, '/dist/css');
  const cssManifest = join(assetsPath, '/dist/css/css-manifest.json');


  function styles_dev() {
    return gulp.src(entries, { sourcemaps: true })
      .pipe(sass({
        precision: 8
      }).on('error', sass.logError))
      .pipe(postcss([
        autoprefixer({
          browsers: ['last 2 versions']
        }),
      ]))
      .pipe(rename(function(path) {
        path.dirname = '';
      }))
      .pipe(gulp.dest(dist, { sourcemaps: true }))
      .pipe(livereload());
  }


  function styles_build() {
    return gulp.src(entries)
      .pipe(sass({
        precision: 8,
        outputStyle: 'compressed'
      }).on('error', sass.logError))
      .pipe(postcss([
        autoprefixer({
          browsers: ['last 2 versions']
        }),
        postcssCustomProperties(),
        pxtorem()
      ]))
      .pipe(rename(function(path) {
        path.dirname = '';
      }))
      .pipe(rev())
      .pipe(gulp.dest(dist))
      .pipe(rev.manifest(cssManifest, {
        base: assetsPath
      }))
      .pipe(gulp.dest(assetsPath));
  }


  return {
    dev: styles_dev,
    build: styles_build,
    src,
    dist
  };
};
