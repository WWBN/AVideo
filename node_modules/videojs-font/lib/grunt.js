const fs = require('fs');
const path = require('path');
const sass = require('node-sass');

let iconsIndex = [];

// Merge a `source` object to a `target` recursively
const merge = (target, source) => {
  // Check if font name is changed
  if (source['font-name']) {
    target['font-name'] = source['font-name'];
  }

  // Check if root dir is changed
  if (source['root-dir']) {
    target['root-dir'] = source['root-dir'];
  }

  // Check for icon changes
  if (source.icons) {
    for (let icon of source['icons']) {
      let index = iconsIndex.indexOf(icon.name);

      // Icon is replaced
      if (index !== -1) {
        target.icons[index] = icon;
      }
      // New icon is added
      else {
        target.icons.push(icon);
        iconsIndex.push(icon.name);
      }
    }
  }

  return target;
}

module.exports = function(grunt) {
  grunt.initConfig({
    sass: {
      options: {
        implementation: sass
      },
      dist: {
        files: {
          'css/videojs-icons.css': 'scss/videojs-icons.scss'
        }
      }
    },
    watch: {
      all: {
        files: ['**/*.hbs', '**/*.js', './icons.json'],
        tasks: ['default']
      }
    }
  });

  grunt.registerTask('generate-font', function() {
    const done = this.async();
    let webfontsGenerator = require('webfonts-generator');
    let iconConfig = grunt.file.readJSON(path.join(__dirname, '..', 'icons.json'));
    let svgRootDir = iconConfig['root-dir'];

    if (grunt.option('exclude-default')) {
      // Exclude default video.js icons
      iconConfig.icons = [];
    }

    let icons = iconConfig.icons;

    // Index default icons
    icons.forEach(icon => iconsIndex.push(icon.name));

    // Merge custom icons
    const customPaths = (grunt.option('custom-json') || '').split(',').filter(Boolean);
    customPaths.forEach(customPath => {
      const customConfig = grunt.file.readJSON(path.resolve(process.cwd(), customPath));
      iconConfig = merge(iconConfig, customConfig);
    });

    icons = iconConfig.icons;

    let iconFiles = icons.map(icon => {
      // If root-dir is specified for a specific icon, use that.
      if (icon['root-dir']) {
        return icon['root-dir'] + icon.svg;
      }

      // Otherwise, use the default root-dir.
      return svgRootDir + icon.svg;
    });

    webfontsGenerator({
      files: iconFiles,
      dest: 'fonts/',
      fontName: iconConfig['font-name'],
      cssDest: 'scss/_icons.scss',
      cssTemplate: './templates/scss.hbs',
      htmlDest: 'index.html',
      htmlTemplate: './templates/html.hbs',
      html: true,
      rename: iconPath => {
        const fileName = path.basename(iconPath);
        const iconName = icons.find(icon => path.basename(icon.svg) === fileName).name;

        return iconName;
      },
      types: ['svg', 'woff', 'ttf']
    }, error => {
      if (error) {
        console.error(error);
        done(false);
      }

      done();
    });
  });

  grunt.registerTask('update-base64', function() {
    const iconScssFile = './scss/_icons.scss';
    const iconConfig = grunt.option('custom-json') ?
      grunt.file.readJSON(path.resolve(process.cwd(), grunt.option('custom-json'))) :
      grunt.file.readJSON(path.join(__dirname, '..', 'icons.json'));
    const fontName = iconConfig['font-name'];
    const fontFiles = {
      woff: './fonts/' + fontName + '.woff'
    };

    let scssContents = fs.readFileSync(iconScssFile).toString();

    Object.keys(fontFiles).forEach(font => {
      const fontFile = fontFiles[font];
      const fontContent = fs.readFileSync(fontFile);
      const regex = new RegExp(`(url.*font-${font}.*base64,)([^\\s]+)(\\).*)`);

      scssContents = scssContents.replace(regex, `$1${fontContent.toString('base64')}$3`);
    });

    fs.writeFileSync(iconScssFile, scssContents);
  });

  grunt.registerTask('default', ['generate-font', 'update-base64', 'sass']);
};
