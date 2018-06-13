module.exports = function(grunt) {
	grunt.initConfig({
		uglify: {
			my_target: {
				files: {
					'js/admin-deactivate.min.js': ['js/admin-deactivate.js'],
					'js/admin-settings.min.js': ['js/admin-settings.js']
				}
			}
		},
		pot: {
			options: {
				text_domain: 'kanban', //Your text domain. Produces my-text-domain.pot
				dest: 'languages/', //directory to place the pot file
				keywords: ['gettext', '__'], //functions to look for
			},
			files: {
				src:  [ '**/*.php' ], //Parse all php files
				expand: true,
			},
		},
		// jshint: {
		// 	options: {
		// 		// reporter: require('jshint-stylish') // use jshint-stylish to make our errors look and read good
		// 	},
		//
		// 	// when this task is run, lint the Gruntfile and all js files in src
		// 	build: ['Gruntfile.js', 'js/*.js']
		// },
		watch: {
			scripts: {
				files: ['js/*.js'],
				tasks: ['uglify'],
				options: {
					interrupt: true
				},
			}
		}
	});


	// grunt.loadNpmTasks('grunt-notify');
	// grunt.loadNpmTasks('grunt-contrib-watch');
	grunt.loadNpmTasks('grunt-contrib-uglify');
	grunt.loadNpmTasks('grunt-pot');
	// grunt.loadNpmTasks('grunt-contrib-jshint');

	grunt.registerTask('default', ['uglify']);
	grunt.registerTask('pot', ['pot']);
	// grunt.registerTask('jshint', ['jshint']);
	grunt.registerTask('watch', ['watch']);
};