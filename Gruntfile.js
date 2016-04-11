/* jshint node:true */
'use strict';

/**
 * Manage project tasks. 
 */
module.exports = function(grunt) {
	
	// auto load grunt tasks
	require('load-grunt-tasks')(grunt);

	grunt.initConfig({
		pkg: grunt.file.readJSON('package.json'),
		config: {
			site: {
				src: 'wp',
				server: 'public',
				dist: 'dist'
			},
			theme: {
				src: '<%= config.site.src %>/theme',
				server: '<%= config.site.server %>/wp-content/themes/<%= pkg.name %>',
				dist: '<%= config.site.dist %>/theme'
			},
			plugin: {
				src: '<%= config.site.src %>/plugin',
				server : '<%= config.site.server %>/wp-content/plugins/<%= pkg.name %>',
				dist: '<%= config.site.dist %>/plugin'
			}
		},
		
		// watch for changes for files and execute an execute a task
		watch: {
			gruntfile: {
				files: ['Gruntfile.js'],
				tasks: ['jshint:gruntfile']
			},
			stylus: {
				files: ['.stylintrc', '<%= config.theme.src %>/stylus/*.styl'],
				tasks: ['stylint', 'stylus:dev']
			},
			js: {
				files: ['<%= config.theme.src %>/js/**/*.js'],
				tasks: ['jshint:theme', 'sync:dev', 'concat']
			},
			site: {
				files: [
			        '<%= config.plugin.src %>/**/*',
			        '<%= config.theme.src %>/src/**/*' 
				],
				tasks: ['sync:dev']
			},
			reload: {
				files: ['<%= config.site.server %>/**/*'],
				options: {
					livereload: true
				}
			}
		},
	
		// sync files
		sync : {
			dev : {
				files : [
				    // Theme PHP files
				    {
						cwd : '<%= config.theme.src %>/src',
						src : '**',
						dest : '<%= config.theme.server %>'
					},
					// Theme JS files
					{
						cwd : '<%= config.theme.src %>/js',
						src : '*.js',
						dest : '<%= config.theme.server %>/assets/js'
					}, 
					// Plugin PHP files
					{
						cwd : '<%= config.plugin.src %>',
						src : '**',
						dest : '<%= config.plugin.server %>'
					}
				],
				pretend : false,
				verbose: true
			},
			dist : {
				files : [
				    // Theme PHP files
				    {
						cwd : '<%= config.theme.src %>/src',
						src : '**',
						dest : '<%= config.theme.dist %>'
					},
					// Plugin PHP files
					{
						cwd : '<%= config.plugin.src %>',
						src : '**',
						dest : '<%= config.plugin.dist %>'
					}
				],
				pretend : false,
				verbose: true
			}
		},
		
		// compile stylus file
		stylus: {
			dev: {
				options: {
					compress: false,
					linenos: true
				},
				files : {
					'<%= config.theme.server %>/assets/css/style.css' : '<%= config.theme.src %>/stylus/style.styl'
				}
			},
			dist: {
				files : {
					'<%= config.theme.dist %>/assets/css/style.css' : '<%= config.theme.src %>/stylus/style.styl'
				}
			}
		},

		// lint stylus files
		stylint: {
			options: {
				
			},
			src: ['<%= config.theme.src %>/stylus/**/*.styl']
		},
		
		// validate JS code
		jshint: {
			options: {
				jshintrc: '.jshintrc'
			},
			gruntfile: ['Gruntfile.js'],
			theme: ['<%= config.theme.src %>/js/script.js']
		},
		
		// minify js files
		uglify: {
			dist : {
				files : {
					'<%= config.theme.dist %>/assets/js/script.js' : [ '<%= config.theme.src %>/js/*.js' ]
				}
			}
		},
		
		// compress theme and plugin 
		compress : {
			theme : {
				options : {
					archive : '<%= config.site.dist %>/<%= pkg.name %>-theme.zip'
				},
				files : [{
					expand: true, 
					cwd: '<%= config.theme.dist %>', 
					src: ['**/*'], 
					dest: '<%= pkg.name %>'
				}]
			},
			plugin : {
				options : {
					archive : '<%= config.site.dist %>/<%= pkg.name %>-plugin.zip'
				},
				files : [{
					expand: true, 
					cwd: '<%= config.plugin.dist %>', 
					src: ['**/*'], 
					dest: '<%= pkg.name %>'
				}]
			}
		},
		
		// remove dist generated directories
		clean : {
			dist : [
				'<%= config.site.dist %>/*', 
				'!<%= config.site.dist %>/*.zip'
			]
		},
		
		// concat all javascript files in one
		concat : {
			dev : {
				files : {
					'<%= config.theme.server %>/assets/js/script.js' : [ '<%= config.theme.src %>/js/**/*.js' ]
				}
			}
		}
	});
	
	// default task: execute dev environment tasks
	grunt.task.registerTask('default', ['lint', 'sync:dev', 'stylus:dev', 'concat:dev']);

	// dist task: generate theme and plugin packages
	grunt.task.registerTask('dist', [
		'sync:dist', 
		'stylus:dist', 
		'uglify:dist', 
		'compress:theme', 
		'compress:plugin', 
		'clean:dist'
	]);
	
	// lint all project
	grunt.task.registerTask('lint', ['jshint:theme', 'stylint']);
};
