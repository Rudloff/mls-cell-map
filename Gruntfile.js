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
            jslint: {
                scripts: {
                    src: ['js/*.js']
                },
                Gruntfile: {
                    src: ['Gruntfile.js']
                }
            }
        }
    );

    grunt.loadNpmTasks('grunt-contrib-uglify');
    grunt.loadNpmTasks('grunt-contrib-cssmin');
    grunt.loadNpmTasks('grunt-contrib-watch');
    grunt.loadNpmTasks('grunt-jslint');

    grunt.registerTask('default', ['uglify', 'cssmin']);
};
