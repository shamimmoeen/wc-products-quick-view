module.exports = function( grunt ) {

	'use strict';

	const sass = require( 'sass' );

	// Project configuration
	grunt.initConfig( {

		pkg: grunt.file.readJSON( 'package.json' ),

		wp_readme_to_markdown: {
			your_target: {
				files: {
					'README.md': 'readme.txt'
				}
			},
		},

		sass: {
			options: {
				implementation: sass,
				outputStyle: 'expanded',
			},
			dev: {
				options: {
					sourceMap: true,
				},
				files: {
					'assets/css/quick-view.css': 'assets/src/scss/quick-view.scss',
					'assets/css/admin.css': 'assets/src/scss/admin.scss',
				},
			},
			dist: {
				options: {
					sourceMap: false,
				},
				files: {
					'assets/css/quick-view.css': 'assets/src/scss/quick-view.scss',
					'assets/css/admin.css': 'assets/src/scss/admin.scss',
				},
			},
		},

		copy: {
			js: {
				files: {
					'assets/js/quick-view.js': 'assets/src/js/quick-view.js',
				},
			},
		},

		uglify: {
			dist: {
				options: {
					sourceMap: false
				},
				files: {
					'assets/js/quick-view.min.js': [ 'assets/src/js/quick-view.js' ]
				}
			}
		},

		cssmin: {
			dist: {
				options: {
					sourceMap: false,
				},
				files: {
					'assets/css/quick-view.min.css': [ 'assets/css/quick-view.css' ],
					'assets/css/admin.min.css': [ 'assets/css/admin.css' ]
				}
			}
		},

		watch: {
			css: {
				files: [ 'assets/src/scss/**/*.scss' ],
				tasks: [ 'sass:dev', 'cssmin' ],
			},
			js: {
				files: [ 'assets/src/js/**/*.js' ],
				tasks: [ 'copy:js', 'uglify' ],
			},
		},

	} );

	grunt.loadNpmTasks( 'grunt-wp-readme-to-markdown' );
	grunt.loadNpmTasks( 'grunt-sass' );
	grunt.loadNpmTasks( 'grunt-contrib-watch' );
	grunt.loadNpmTasks( 'grunt-contrib-uglify' );
	grunt.loadNpmTasks( 'grunt-contrib-cssmin' );
	grunt.loadNpmTasks( 'grunt-contrib-copy' );

	grunt.registerTask( 'default', [ 'readme' ] );
	grunt.registerTask( 'readme', [ 'wp_readme_to_markdown' ] );
	grunt.registerTask( 'css', [ 'sass:dev', 'cssmin' ] );
	grunt.registerTask( 'build', [ 'sass:dist', 'cssmin', 'js' ] );
	grunt.registerTask( 'js', [ 'copy:js', 'uglify' ] );
	grunt.registerTask( 'dev', [ 'watch' ] );

	grunt.util.linefeed = '\n';

};
