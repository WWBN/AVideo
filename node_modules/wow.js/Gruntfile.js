/*global module:false*/

module.exports = function(grunt) {
  require('load-grunt-tasks')(grunt);
  mainTasks = [
    'eslint', 'babel', 'coffee', 'growl:coffee', 'uglify', 'jasmine',
    'growl:jasmine',
  ]

  // Project configuration.
  grunt.initConfig({
    pkg: grunt.file.readJSON('package.json'),
    eslint: {
      target: ['src/WOW.js']
    },
    uglify: {
      dist: {
        files: {
          'dist/wow.min.js': 'dist/wow.js'
        }
      },
      options: {
        banner : '/*! <%= pkg.title %> wow.js - v<%= pkg.version %> - ' +
          '<%= grunt.template.today("yyyy-mm-dd") %>\n' +
          '<%= pkg.homepage ? "* " + pkg.homepage + "\\n" : "" %>' +
          '* Copyright (c) <%= grunt.template.today("yyyy") %> Thomas Grainger;' +
          ' Licensed <%= pkg.license %> */',
        report: 'gzip'
      }
    },
    babel : {
      options : {
        presets: ['es2015', 'stage-1'],
        plugins: [
          'add-module-exports',
          "transform-es2015-modules-umd"
        ]
      },
      dist: {
        files: {
          'dist/wow.js': 'src/WOW.js'
        }
      }
    },
    coffee : {
      specs : {
        files: [{
          expand: true,
          cwd: 'spec/coffeescripts/',
          src: '*.coffee',
          dest: 'spec/javascripts/',
          ext: '.js'
        }]
      },
      helpers : {
        files: [{
          expand: true,
          cwd: 'spec/coffeescripts/helpers/',
          src: '*.coffee',
          dest: 'spec/javascripts/helpers/',
          ext: '.js'
        }]
      }
    },
    jasmine : {
      src     : ['spec/javascripts/libs/*.js', 'dist/wow.min.js'],
      options : {
        specs   : 'spec/javascripts/**/*.js',
        helpers : 'spec/javascripts/helpers/**/*.js'
      }
    },
    watch : {
      files: [
        'src/*',
        'spec/coffeescripts/**/*.coffee'
      ],
      tasks: mainTasks
    },
    growl : {
      coffee : {
        title   : 'CoffeeScript',
        message : 'Compiled successfully'
      },
      jasmine : {
        title   : 'Jasmine',
        message : 'Tests passed successfully'
      }
    }
  });

  grunt.registerTask('default', mainTasks);
  // Travis CI task.
  grunt.registerTask('travis', ['eslint', 'babel', 'coffee', 'uglify', 'jasmine']);
};
