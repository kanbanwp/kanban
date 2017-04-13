module.exports = function(grunt) {

	// load all grunt tasks in package.json matching the `grunt-*` pattern
	require('load-grunt-tasks')(grunt);

	grunt.initConfig({

		pkg: grunt.file.readJSON('package.json'),

		githooks: {
			all: {
				'pre-commit': 'default'
			}
		},

		csscomb: {
			dist: {
				files: [{
					expand: true,
					cwd: '',
					src: ['*.css'],
					dest: ''
				}]
			}
		},
		
		sass: {
			dist: {
				options: {
					style: 'expanded',
					lineNumbers: true
				},
				files: {
					'board.css': 'css/board.scss'
				}
			}
		},

		autoprefixer: {
			options: {
				browsers: ['> 1%', 'last 2 versions', 'Firefox ESR', 'Opera 12.1']
			},
			dist: {
				src:  ['css/admin-settings.css','board.css']
			}
		},

		cmq: {
			options: {
				log: false
			},
			dist: {
				files: {
					'style.css': 'style.css'
					'board.css': 'board.css'
					'admin-settings.css': 'admin-settings.css'
				}
			}
		},

		cssmin: {
			minify: {
				expand: true,
				cwd: 'css/',
				src: ['*.css', '!*.min.css'],
				dest: '',
				ext: '.min.css'
			}
		},

		uglify: {
			build: {
				options: {
					mangle: true
				},
				files: [{
					expand: true,
					cwd: 'js/',
					src: ['*.js', '!*.min.js', '!/*.js'],
					dest: 'js/',
					ext: '.min.js'
				}]
			}
		},

		watch: {

			scripts: {
				files: ['js/**/*.js'],
				tasks: ['javascript'],
				options: {
					spawn: false
				}
			},

			css: {
				files: ['sass/partials/*.scss'],
				tasks: ['styles'],
				options: {
					spawn: false,
					livereload: true
				}
			}

		},

		clean: {
			js: ['js/**/*.min.js'],
			css: ['board.css', 'board.min.css','admin-settings.min.css']
		},

		update_submodules: {

			default: {
				options: {
					// default command line parameters will be used: --init --recursive
				}
			},
			withCustomParameters: {
				options: {
					params: '--force' // specifies your own command-line parameters
				}
			}

		}

	});

	grunt.registerTask('styles', ['sass', 'autoprefixer', 'cmq', 'csscomb', 'cssmin']);
	grunt.registerTask('javascript', ['uglify']);
	grunt.registerTask('default', ['styles', 'javascript']);

};
