module.exports = function (grunt) {
    "use strict";

    var livereload = {
        host: 'localhost',
        port: 35729
    };

    grunt.initConfig({
        pkg: grunt.file.readJSON('package.json'),
        build: {
            all: {
                dest: "dist/emojionearea.js"
            }
        },
        uglify: {
            all: {
                files: {
                    "dist/emojionearea.min.js": ["dist/emojionearea.js"]
                },
                options: {
                    preserveComments: false,
                    sourceMap: true,
                    ASCIIOnly: true,
                    sourceMapName: "dist/emojionearea.min.map",
                    report: "min",
                    beautify: {
                        "ascii_only": true
                    },
                    banner: "/*! EmojioneArea v<%= pkg.version %> | MIT license */",
                    compress: {
                        "hoist_funs": false,
                        loops: false,
                        unused: false
                    }
                }
            }
        },
        sass: {
            all: {
                options: {
                    unixNewlines: true,
                    compass: true,
                    lineNumbers: false
                },
                files: {
                    'dist/emojionearea.css': 'scss/emojionearea.scss'
                }
            },
        },
        cssmin: {
            target: {
                files: {
                    'dist/emojionearea.min.css': ['dist/emojionearea.css']
                },
                options: {
                    sourceMap: false
                }
            }
        },
        watch: {
            sass: {
                files: [
                    'scss/**/*.scss'
                ],
                tasks: ['sass'],
                options: {
                    livereload: livereload
                }
            },
            js: {
                files: [
                    'src/**/*.js'
                ],
                tasks: ['build'],
                options: {
                    livereload: livereload
                }
            }
        },
    });

    grunt.loadNpmTasks('grunt-contrib-uglify');
    grunt.loadNpmTasks('grunt-contrib-cssmin');
    grunt.loadNpmTasks('grunt-contrib-watch');
    grunt.loadNpmTasks('grunt-contrib-sass');
    grunt.loadTasks("tasks");

    grunt.registerTask("default", ["build", "uglify", "sass", "cssmin"]);
};
