module.exports = function(assetsPath, options, themeName) {
  const gulp = require('gulp');
  const favicons = require('favicons');
  const fs = require('fs');
  const del = require('del');
  const { join } = require('path');

  const {appName, bgColor, themeColor} = options;

  /**
   * delete standalone apple config meta tag. Defaults: https://github.com/evilebottnawi/favicons/blob/master/config/html.json
   */
  delete favicons.config.html.android["meta[name='mobile-web-app-capable']"];
  delete favicons.config.html.appleIcon["meta[name='apple-mobile-web-app-capable']"];
  delete favicons.config.html.appleIcon["meta[name='apple-mobile-web-app-status-bar-style']"];

  const faviconsPath = join(assetsPath, '/favicons/');
  const faviconsUrl = join('/app/themes/', themeName, assetsPath, '/favicons/');
  const faviconSource = join(assetsPath, '/src/favicons/favicon.svg');
  const faviconsTemplate = join(assetsPath, '/../templates/includes/meta-favicons.twig');

  /**
   * Options at https://github.com/evilebottnawi/favicons#nodejs
   */
  const faviconConfig = {
    path: faviconsUrl,
    icons: {
      display: 'browser',
      android: false,
      appleIcon: false,
      appleStartup: false,
      coast: false,
      favicons: true,
      firefox: false,
      windows: false,
      yandex: false
    }
  };

  const appIconSource = join(assetsPath, '/src/favicons/appicon.png');

  const appIconConfig = {
    appName: appName,
    appDescription: null,
    developerName: null,
    developerURL: null,
    background: bgColor,
    theme_color: themeColor,
    path: faviconsUrl,
    display: 'browser',
    orientation: 'any',
    start_url: '/',
    version: '1.0',
    lang: 'en-GB',
    logging: false,
    online: false,
    preferOnline: false,
    icons: {
      android: {
        background: true,
        shadow: true
      },
      appleIcon: true,
      appleStartup: false,
      coast: false,
      favicons: false,
      firefox: true,
      windows: false,
      yandex: false
    }
  };


  /**
   * Clear the output folder.
   */
  function cleanFaviconsDir() {
    return del([faviconsPath+'*', faviconsTemplate]);
  }


  function createFavicons(cb) {

    favicons(faviconSource, faviconConfig, function callback(error, response) {
      if (error) {
          console.log(error);
          return;
      }

      // save out the images.
      for (let image of response.images) {
        fs.writeFileSync(join(faviconsPath, image.name), image.contents);
        console.log(`saved ${image.name}`);
      }


      // save out the files.
      for (let file of response.files) {
        fs.writeFileSync(join(faviconsPath, file.name), file.contents);
        console.log(`saved ${file.name}`);
      }

      // save the meta tags html.
      fs.writeFileSync(faviconsTemplate, response.html.join('\n'));

      cb();
    });

  }

  function createAppIcons(cb) {

    favicons(appIconSource, appIconConfig, function callback(error, response) {
      if (error) {
          console.log(error);
          return;
      }

      // save out the images.
      for (let image of response.images) {
        fs.writeFileSync(join(faviconsPath, image.name), image.contents);
        console.log(`saved ${image.name}`);
      }


      // save out the files.
      for (let file of response.files) {
        fs.writeFileSync(join(faviconsPath, file.name), file.contents);
        console.log(`saved ${file.name}`);
      }

      // save the meta tags html.
      fs.appendFileSync(faviconsTemplate, response.html.join('\n'));

      cb();
    });

  }

  return gulp.series([cleanFaviconsDir, createFavicons, createAppIcons]);
};
