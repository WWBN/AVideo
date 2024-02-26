/* jshint node: true */

'use strict';


// Source: UMD (Universal Module Definition)
// https://github.com/umdjs/umd/blob/master/templates/returnExports.js
const umdCode = {
	// Uses Node, AMD or browser globals to create a module.
	vanilla: {
		opening:
`<%= banner %>

'use strict';

(function (root, factory) {
	if (typeof define === 'function' && define.amd) {
		// AMD. Register as an anonymous module.
		define([], factory);
	} else if (typeof module === 'object' && module.exports) {
		// Node. Does not work with strict CommonJS, but
		// only CommonJS-like environments that support module.exports,
		// like Node.
		module.exports = factory();
	} else {
		// Browser globals (root is window)
		root.CircleProgress = factory();
  }
}(typeof self !== 'undefined' ? self : this, function () {
`,
		closing:
`
	// Just return a value to define the module export.
	// This example returns an object, but the module
	// can return a function as the exported value.
	return CircleProgress;
}));
`
	},

	// Uses CommonJS, AMD or browser globals to create a jQuery plugin.
	jQuery: {
		opening:
`(function (factory) {
	if (typeof define === 'function' && define.amd) {
		// AMD. Register as an anonymous module.
		define(['jquery'], factory);
	} else if (typeof module === 'object' && module.exports) {
		// Node/CommonJS
		module.exports = function( root, jQuery ) {
			if ( jQuery === undefined ) {
				// require('jQuery') returns a factory that requires window to
				// build a jQuery instance, we normalize how we use modules
				// that require this pattern but the window provided is a noop
				// if it's defined (how jquery works)
				if ( typeof window !== 'undefined' ) {
					jQuery = require('jquery');
				}
				else {
					jQuery = require('jquery')(root);
				}
			}
			factory(jQuery);
			return jQuery;
		};
	} else {
		// Browser globals
		factory(jQuery);
	}
}(function (jQuery) {
`,
		closing:
`
}));
`
	}
}

module.exports = function (grunt) {
	// Load all grunt tasks
	require('load-grunt-tasks')(grunt);
	// Show elapsed time at the end
	require('time-grunt')(grunt);

	// Project configuration.
	grunt.initConfig({
		// Metadata.
		pkg: grunt.file.readJSON('package.json'),
		banner: `/*!
 * Circle Progress - v<%= pkg.version %> - <%= grunt.template.today("yyyy-mm-dd") %>
 * <%= pkg.homepage %>
 * Copyright (c) <%= pkg.author.name %>
 * Licensed <%= pkg.license %>
 */`,
		// Task configuration.
		clean: {
			files: ['dist'],
		},
		babel: {
			vanilla: {
				src: 'dist/circle-progress.js',
				dest: 'dist/circle-progress.js',
			},
			jquery: {
				src: 'dist/jquery.circle-progress.js',
				dest: 'dist/jquery.circle-progress.js',
			},
			jqueryBare: {
				src: 'dist/jquery.circle-progress.bare.js',
				dest: 'dist/jquery.circle-progress.bare.js',
			},
		},
		concat: {
			options: {
				stripBanners: true,
				process: function(src) {
					return src.replace('\'use strict\';\n', '');
				},
			},
			vanilla: {
				options: {
					banner: umdCode.vanilla.opening,
					footer: umdCode.vanilla.closing,
				},
				src: ['src/innersvg.js', 'src/svgpaper.js', 'src/animator.js', 'src/circle-progress.js'],
				dest: 'dist/circle-progress.js',
			},
			jquery: {
				options: {
					banner: umdCode.jQuery.opening,
					footer: umdCode.jQuery.closing,
				},
				src: ['src/innersvg.js', 'src/svgpaper.js', 'src/animator.js', 'src/circle-progress.js', 'lib/jquery.ui.widget.js', 'src/jquery.circle-progress.js'],
				dest: 'dist/jquery.circle-progress.js',
			},
			jqueryBare: {
				options: {
					banner: umdCode.jQuery.opening,
					footer: umdCode.jQuery.closing,
				},
				src: ['src/innersvg.js', 'src/svgpaper.js', 'src/animator.js', 'src/circle-progress.js', 'src/jquery.circle-progress.js'],
				dest: 'dist/jquery.circle-progress.bare.js',
			}
		},
		uglify: {
			options: {
				banner: '<%= banner %>\n',
			},
			dist: {
				files: {
					'dist/circle-progress.min.js': 'dist/circle-progress.js',
					'dist/jquery.circle-progress.min.js': 'dist/jquery.circle-progress.js',
					'dist/jquery.circle-progress.bare.min.js': 'dist/jquery.circle-progress.bare.js',
				}
			}
		},
		copy: {
			dist: {
				dest: 'docs/js/circle-progress.js',
				src: 'dist/circle-progress.js',
			}
		},
		watch: {
			src: {
				files: 'src/**/*',
				tasks: ['build'],
			}
		},
	});

	grunt.registerTask('build', ['clean', 'concat', 'babel', 'uglify', 'copy']);
};
