module.exports = function( grunt ) {

	'use strict';

	// Load multiple grunt tasks using globbing patterns
	require( 'load-grunt-tasks' )( grunt );

	// Project configuration
	grunt.initConfig( {

		pkg: grunt.file.readJSON( 'package.json' ),

		rtlcss: {
			options: {
				opts: {
					processUrls: false,
					autoRename: false,
					clean: true,
				},
				saveUnmodified: false,
			},
			target: {
				files: [
					{
						expand: true,
						cwd: '<%= pkg.name %>/assets/css/ltr',
						src: [ 'user-profiles.css' ],
						dest: '<%= pkg.name %>/assets/css/rtl',
						ext: '.css',
					},
				],
			},
		},

		cssmin: {
			options: {
				mergeIntoShorthands: false,
			},
			ltr: {
				files: [
					{
						expand: true,
						cwd: '<%= pkg.name %>/assets/css/ltr',
						src: [ 'user-profiles.css' ],
						dest: '<%= pkg.name %>/assets/css/min/ltr',
						ext: '.css',
					},
				],
			},
			rtl: {
				files: [
					{
						expand: true,
						cwd: '<%= pkg.name %>/assets/css/rtl',
						src: [ 'user-profiles.css' ],
						dest: '<%= pkg.name %>/assets/css/min/rtl',
						ext: '.css',
					},
				],
			},
		},

		replace: {

			// /README.md
			readme_md: {
				src: [ 'README.md' ],
				overwrite: true,
				replacements: [{
					from: /Current Version:\s*(.*)/,
					to: "Current Version: <%= pkg.version %>",
				}],
			},

			// /readme.txt
			readme_txt: {
				src: [ 'readme.txt' ],
				overwrite: true,
				replacements: [{
					from: /Stable tag:\s*(.*)/,
					to: "Stable tag:        <%= pkg.version %>",
				}],
			},

			// /wp-user-profiles.php
			bootstrap_php: {
				src: [ '<%= pkg.name %>.php' ],
				overwrite: true,
				replacements: [{
					from: /Version:\s*(.*)/,
					to: "Version:           <%= pkg.version %>",
				}],
			},

			// /wp-user-profiles/wp-user-profiles.php
			loader_php: {
				src: [ '<%= pkg.name %>/<%= pkg.name %>.php' ],
				overwrite: true,
				replacements: [{
					from: /private\s*\$version\s*=\s*'(.*)'/,
					to: "private $version = '<%= pkg.version %>'",
				}],
			},
		},

		checktextdomain: {
			options: {
				text_domain: '<%= pkg.name %>',
				keywords: [
					'__:1,2d',
					'_e:1,2d',
					'_x:1,2c,3d',
					'esc_html__:1,2d',
					'esc_html_e:1,2d',
					'esc_html_x:1,2c,3d',
					'esc_attr__:1,2d',
					'esc_attr_e:1,2d',
					'esc_attr_x:1,2c,3d',
					'_ex:1,2c,3d',
					'_n:1,2,3,4d',
					'_nx:1,2,4c,5d',
					'_n_noop:1,2,3d',
					'_nx_noop:1,2,3c,4d',
					' __ngettext:1,2,3d',
					'__ngettext_noop:1,2,3d',
					'_c:1,2d',
					'_nc:1,2,4c,5d',
				],
			},
			files: {
				src: [
					'*.php',
					'**/*.php',
					'!\.git/**/*',
					'!bin/**/*',
					'!node_modules/**/*',
					'!tests/**/*',
					'!build/**/*'
				],
				expand: true,
			},
		},

		addtextdomain: {
			options: {
				textdomain: '<%= pkg.name %>',
			},
			update_all_domains: {
				options: {
					updateDomains: true
				},
				src: [
					'*.php',
					'**/*.php',
					'!\.git/**/*',
					'!bin/**/*',
					'!node_modules/**/*',
					'!tests/**/*',
					'!build/**/*'
				]
			}
		},

		wp_readme_to_markdown: {
			your_target: {
				files: {
					'README.md': 'readme.txt'
				}
			},
		},

		makepot: {
			target: {
				options: {
					domainPath: '/<%= pkg.name %>/assets/languages/',
					exclude: [ '\.git/*', 'bin/*', 'node_modules/*', 'tests/*', 'build/*' ],
					mainFile: '<%= pkg.name %>.php',
					potFilename: '<%= pkg.name %>.pot',
					type: 'wp-plugin',
					updateTimestamp: false,
					potHeaders: {
						poedit: true,
						'x-poedit-keywordslist': true,
					},
					processPot: function( pot, options ) {
						pot.headers[ 'report-msgid-bugs-to' ] = 'https://github.com/stuttter/wp-user-profiles/issues/new';
						pot.headers[ 'last-translator' ]      = 'WP-Translations (http://wp-translations.org/)';
						pot.headers[ 'language-team' ]        = 'WP-Translations <wpt@wp-translations.org>';
						pot.headers[ 'language' ]             = 'en_US';

						let translation,
							excluded_meta = [
								'Plugin Name of the plugin/theme',
								'Plugin URI of the plugin/theme',
								'Author of the plugin/theme',
								'Author URI of the plugin/theme',
							];

						for ( translation in pot.translations[ '' ] ) {
							if ( 'undefined' !== typeof pot.translations[ '' ][ translation ].comments.extracted ) {
								if ( excluded_meta.indexOf( pot.translations[ '' ][ translation ].comments.extracted ) >= 0 ) {
									console.log( 'Excluded meta: ' + pot.translations[ '' ][ translation ].comments.extracted );
									delete pot.translations[ '' ][ translation ];
								}
							}
						}

						return pot;
					},
				},
			},
		},

		// Clean up build directory
		clean: {
			main: [ 'build/<%= pkg.name %>' ],
		},

		// Copy the plugin into the build directory
		copy: {
			main: {
				src: [
					'<%= pkg.name %>/**',
					'*.php',
					'*.txt',
				],
				dest: 'build/<%= pkg.name %>/',
			},
		},

		// Compress build directory into <name>.zip and <name>-<version>.zip
		compress: {
			main: {
				options: {
					mode: 'zip',
					archive: './build/<%= pkg.name %>.zip',
				},
				expand: true,
				cwd: 'build/<%= pkg.name %>/',
				src: [ '**/*' ],
				dest: '<%= pkg.name %>/',
			},
		},
	} );

	// Default
	grunt.registerTask( 'default', [
		'i18n',
		'readme'
	] );

	// Internationalization
	grunt.registerTask( 'i18n', [
		'addtextdomain',
		'makepot'
	] );

	// Read Me
	grunt.registerTask( 'readme', [
		'wp_readme_to_markdown'
	] );

	// Bump versions
	grunt.registerTask( 'bump', [
		'replace'
	] );

	// Bump assets
	grunt.registerTask( 'update', [
		'bump',
		'cssmin:ltr',
		'rtlcss',
		'cssmin:rtl',
		'force:checktextdomain',
		'makepot'
	] );

	// Build the .zip to ship somewhere
	grunt.registerTask( 'build', [
		'update',
		'clean',
		'copy',
		'compress'
	] );

	grunt.util.linefeed = '\n';
};
