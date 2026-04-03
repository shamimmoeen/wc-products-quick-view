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
					'assets/css/quick-view.css': 'assets/scss/quick-view.scss',
					'assets/css/admin.css': 'assets/scss/admin.scss',
				},
			},
			dist: {
				options: {
					sourceMap: false,
				},
				files: {
					'assets/css/quick-view.css': 'assets/scss/quick-view.scss',
					'assets/css/admin.css': 'assets/scss/admin.scss',
				},
			},
		},

		uglify: {
			dist: {
				options: {
					sourceMap: false
				},
				files: {
					'assets/js/quick-view.min.js': [ 'assets/js/quick-view.js' ]
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
				files: [ 'assets/scss/**/*.scss' ],
				tasks: [ 'sass:dev', 'cssmin' ],
			},
			js: {
				files: [ 'assets/js/*.js', '!assets/js/*.min.js' ],
				tasks: [ 'uglify' ],
			},
		},

	} );

	grunt.loadNpmTasks( 'grunt-wp-readme-to-markdown' );
	grunt.loadNpmTasks( 'grunt-sass' );
	grunt.loadNpmTasks( 'grunt-contrib-watch' );
	grunt.loadNpmTasks( 'grunt-contrib-uglify' );
	grunt.loadNpmTasks( 'grunt-contrib-cssmin' );

	grunt.registerTask( 'default', [ 'readme' ] );
	grunt.registerTask( 'readme', [ 'wp_readme_to_markdown' ] );
	grunt.registerTask( 'css', [ 'sass:dev', 'cssmin' ] );
	grunt.registerTask( 'build', [ 'sass:dist', 'cssmin', 'js' ] );
	grunt.registerTask( 'js', [ 'uglify' ] );
	grunt.registerTask( 'dev', [ 'watch' ] );

	grunt.util.linefeed = '\n';

};
