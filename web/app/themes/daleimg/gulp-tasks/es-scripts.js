/**
 *  See also:
 * - https://gulpjs.com/docs/en/getting-started/using-plugins
 * - https://github.com/rollup/rollup/issues/863
 */

module.exports = function(assetsPath) {
  const gulp = require('gulp');
  const { join } = require('path');
  const livereload = require('gulp-livereload');

  const rollup = require('rollup');
  const resolve = require('rollup-plugin-node-resolve');
  const commonjs = require('rollup-plugin-commonjs');
  const babel = require('rollup-plugin-babel');
  const { terser } = require('rollup-plugin-terser');

  const rev = require('gulp-rev');

  const srcDir = join(assetsPath, '/src/js/');
  const src = join(srcDir, '/**/*.js');
  const dist = join(assetsPath, '/dist/js/');

  const jsManifest = join(assetsPath, '/dist/js/js-manifest.json');

  const outputs = [
    join(dist, '/**/*.js'),
    '!' + join(dist, '/**/_*.js')
  ];

  const entries = [
    'site.js',
    'block-editor-customisations.js'
  ];

  function scripts_dev() {
    return Promise.all(entries.map(single_bundle_dev))
      .then(reload);
  }

  function single_bundle_dev(filename) {
    const input = join(srcDir, filename);
    const output = join(dist, filename);

    return rollup.rollup({
        input: input,
        plugins: [
          resolve({
            browser: true
          }),
          commonjs(),
        ]
      })
      .then(bundle => {
        return bundle.write({
          file: output,
          format: 'iife',
          name: 'site',
          sourcemap: true,
        });
      })
      .then(bundle => {
        bundle.output.forEach(file => console.log(`bundled ${file.fileName}`));
      })
      .then(reload);
  }

  function scripts_build() {
    return Promise.all(entries.map(single_bundle_prod))
      .then(reload);
  }

  function single_bundle_prod(filename) {
    const input = join(srcDir, filename);
    const output = join(dist, filename);

    return rollup.rollup({
      input: input,
      plugins: [
        resolve({
          browser: true
        }),
        commonjs(),
        babel({
          extensions: ['.js', '.mjs', '.html', '.svelte'],
          presets: [
            [
              '@babel/env', {
                'modules': false,
                'targets': {
                  'chrome': '64',
                  'ie': '11'
                }
              }
            ]
          ]
        }),
        terser()
      ]
    })
    .then(bundle => {
      return bundle.write({
        file: output,
        format: 'iife',
        name: 'site',
        sourcemap: true,
      })
      .then(revJs);
    });
  }

  function reload() {
    return gulp.src(outputs)
      .pipe(livereload());
  }

  function revJs() {
    return gulp.src(outputs)
      .pipe(rev())
      .pipe(gulp.dest(dist))
      .pipe(rev.manifest(jsManifest, {
        base: assetsPath,
        merge: true
      }))
      .pipe(gulp.dest(assetsPath));
  }

  return {
    src,
    dev: scripts_dev,
    build: scripts_build
  };
};
