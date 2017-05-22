/*jslint node: true */
module.exports = function (grunt) {
    'use strict';
    grunt.initConfig(
        {
            uglify: {
                combine: {
                    files: {
                        'dist/main.js': ['js/*.js']
                    },
                    options: {
                        sourceMap: true
                    }
                }
            },
            cssmin: {
                combine: {
                    files: {
                        'dist/main.css': ['css/style.css']
                    }
                }
            },
            watch: {
                scripts: {
                    files: ['js/*.js'],
                    tasks: ['uglify']
                },
                styles: {
                    files: ['css/*.css'],
                    tasks: ['cssmin']
                }
            },
            jsonlint: {
                manifests: {
                    src: '*.json',
                    options: {
                        format: true
                    }
                }
            },
            jslint: {
                scripts: {
                    src: ['js/*.js']
                },
                Gruntfile: {
                    src: ['Gruntfile.js']
                }
            },
            fixpack: {
                package: {
                    src: 'package.json'
                }
            },
            phpcs: {
                options: {
                    standard: 'PSR2',
                    bin: 'vendor/bin/phpcs'
                },
                php: {
                    src: ['*.php', 'ajax/*.php']
                }
            }
        }
    );

    grunt.loadNpmTasks('grunt-contrib-uglify');
    grunt.loadNpmTasks('grunt-contrib-cssmin');
    grunt.loadNpmTasks('grunt-contrib-watch');
    grunt.loadNpmTasks('grunt-jslint');
    grunt.loadNpmTasks('grunt-jsonlint');
    grunt.loadNpmTasks('grunt-fixpack');
    grunt.loadNpmTasks('grunt-phpcs');

    grunt.registerTask('default', ['uglify', 'cssmin']);
    grunt.registerTask('lint', ['jslint', 'fixpack', 'jsonlint', 'phpcs']);
};
