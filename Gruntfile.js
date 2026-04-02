module.exports = function( grunt ) {

	'use strict';

	var sass = require( 'sass' );

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
				sourceMap: true,
				outputStyle: 'expanded',
			},
			dist: {
				files: {
					'assets/css/quick-view.css': 'assets/scss/quick-view.scss',
					'assets/css/admin.css':      'assets/scss/admin.scss',
				},
			},
		},
	} );

	grunt.loadNpmTasks( 'grunt-wp-readme-to-markdown' );
	grunt.loadNpmTasks( 'grunt-sass' );

	grunt.registerTask( 'default', [ 'readme' ] );
	grunt.registerTask( 'readme', [ 'wp_readme_to_markdown' ] );
	grunt.registerTask( 'css', [ 'sass' ] );

	grunt.util.linefeed = '\n';

};
