'use strict';
module.exports = function(grunt) {
    grunt.initConfig({
        less: {
            dist: {
                options: {
                    compile: true,
                    compress: false
                },
                files: {
                    'dist/css/fontawesome-iconpicker.css': [
                        'src/less/iconpicker.less'
                    ]
                }
            },
            distMin: {
                options: {
                    compile: true,
                    compress: true
                },
                files: {
                    'dist/css/fontawesome-iconpicker.min.css': [
                        'src/less/iconpicker.less'
                    ]
                }
            }
        },
        jsbeautifier: {
            files: ['Gruntfile.js', 'src/js/*.js']
        },
        uglify: {
            distMin: {
                options: {
                    compress: {},
                    beautify: false
                },
                files: {
                    'dist/js/fontawesome-iconpicker.min.js': [
                        'src/js/jquery.ui.pos.js',
                        'src/js/iconpicker.js'
                    ]
                }
            },
            dist: {
                options: {
                    compress: false,
                    beautify: true
                },
                files: {
                    'dist/js/fontawesome-iconpicker.js': [
                        'src/js/jquery.ui.pos.js',
                        'src/js/iconpicker.js'
                    ]
                }
            }
        },
        watch: {
            less: {
                files: [
                    'src/less/*.less'
                ],
                tasks: ['less']
            },
            js: {
                files: [
                    'src/js/*.js'
                ],
                tasks: ['uglify']
            }
        },
        clean: {
            dist: [
                'dist/css',
                'dist/js/*.js'
            ]
        }
    });

    // Load tasks
    grunt.loadNpmTasks('grunt-contrib-clean');
    grunt.loadNpmTasks('grunt-contrib-uglify');
    grunt.loadNpmTasks('grunt-contrib-watch');
    grunt.loadNpmTasks('grunt-contrib-less');
    grunt.loadNpmTasks('grunt-jsbeautifier');

    // Register tasks
    grunt.registerTask('default', [
        'clean',
        'less',
        'jsbeautifier',
        'uglify'
    ]);
    grunt.registerTask('dev', [
        'watch'
    ]);

};
