/*jslint node: true */
module.exports = function (grunt) {
    'use strict';
    grunt.initConfig({
        uglify: {
            combine: {
                files: {
                    'dist/main.js': ['js/mnccolors.js', 'js/map.js']
                }
            }
        },
        cssmin: {
            combine: {
                files: {
                    'dist/main.css': ['css/style.css']
                }
            }
        }
    });

    grunt.loadNpmTasks('grunt-contrib-uglify');
    grunt.loadNpmTasks('grunt-contrib-cssmin');

    grunt.registerTask('default', ['uglify', 'cssmin']);
};
