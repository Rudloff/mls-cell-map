/*jslint node: true */
module.exports = function (grunt) {
    'use strict';
    grunt.initConfig(
        {
            uglify: {
                bower: {
                    files: {
                        'dist/bower.js': 'dist/_bower.js'
                    },
                    options: {
                        sourceMap: true
                    }
                },
                js: {
                    files: {
                        'dist/main.js': ['js/*.js']
                    },
                    options: {
                        sourceMap: true
                    }
                }
            },
            cssmin: {
                bower: {
                    files: {
                        'dist/bower.css': 'dist/_bower.css'
                    }
                },
                css: {
                    files: {
                        'dist/main.css': ['css/*.css']
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
                    src: ['*.php', 'ajax/*.php', 'classes/*.php']
                }
            },
            bower_concat: {
                css: {
                    dest: {
                        'css': 'dist/_bower.css'
                    },
                    mainFiles: {
                        'font-awesome': 'css/font-awesome.min.css'
                    }
                },
                js: {
                    dest: {
                        'js': 'dist/_bower.js'
                    },
                    dependencies: {
                        'Leaflet.MakiMarkers': 'leaflet'
                    },
                    mainFiles: {
                        'leaflet-control-geocoder': 'dist/Control.Geocoder.js',
                        'leaflet-plugins': 'control/Permalink.js'
                    }
                }
            },
            phpdocumentor: {
                doc: {
                    options: {
                        directory: 'classes/,controllers/,tests/'
                    }
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
    grunt.loadNpmTasks('grunt-bower-concat');
    grunt.loadNpmTasks('grunt-phpdocumentor');

    grunt.registerTask('default', ['bower_concat', 'uglify', 'cssmin']);
    grunt.registerTask('lint', ['jslint', 'fixpack', 'jsonlint', 'phpcs']);
    grunt.registerTask('doc', ['phpdocumentor']);
};
