var gulp = require('gulp');
var file = require('gulp-file');
var rollup = require('rollup').rollup;
var babel = require('rollup-plugin-babel');

function build_dropdowns() {
  return rollup({
    input: 'web/app/themes/fitterfood/assets/src/lib/bootstrap/js/src/dropdown.js',
    plugins: [
      babel({
        presets: [
          [
            '@babel/env',
            {
              loose: true,
              modules: false,
              exclude: ['transform-typeof-symbol']
            }
          ]
        ],
        plugins: [
          '@babel/plugin-proposal-object-rest-spread'
        ],
        babelrc: false,
        exclude: 'node_modules/**'
      })
    ]
  })
  .then(bundle => {
    return bundle.generate({
      globals: {
        'popper.js': 'Popper'
      },
      format: 'cjs',
      fileName: 'dropdown.js'
    });
  })
  .then(gen => {
    return file('_bootstrap.dropdown.js', gen.output[0].code, {src: true})
      .pipe(gulp.dest('web/app/themes/fitterfood/assets/src/js/lib/'));
  });
}

module.exports = {
  build: build_dropdowns
};
