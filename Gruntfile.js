module.exports = function (grunt) {
  require('load-grunt-tasks')(grunt);

  grunt.initConfig({
    appDir: 'app/Resources/static',
    buildDir: 'web/bundles/wallabagcore',

    cssmin: {
      material: {
        options: {
          report: 'gzip',
          keepSpecialComments: 0
        },
        files: {
          '<%= buildDir %>/themes/material/css/style.min.css': [
            '<%= buildDir %>/material.css'
          ]
        }
      },
      baggy: {
        options: {
          report: 'gzip',
          keepSpecialComments: 0
        },
        files: {
          '<%= buildDir %>/themes/baggy/css/style.min.css': [
            '<%= buildDir %>/baggy.css'
          ]
        }
      }
    },
    concat: {
      options: {
        separator: ';'
      },
      jsMaterial: {
        src: [
          'node_modules/jquery/dist/jquery.js',
          'node_modules/jquery-ui/jquery-ui.js',
          'node_modules/materialize-css/bin/materialize.js',
          '<%= appDir %>/themes/material/js/init.js',
          '<%= appDir %>/themes/material/js/restoreScroll.js'
        ],
        dest: '<%= buildDir %>/material.js'
      },
      jsBaggy: {
        src: [
          'node_modules/jquery/dist/jquery.js',
          'node_modules/jquery-ui/jquery-ui.js',
          '<%= appDir %>/themes/baggy/js/init.js',
          '<%= appDir %>/themes/baggy/js/restoreScroll.js',
          '<%= appDir %>/themes/baggy/js/autoClose.js',
          '<%= appDir %>/themes/baggy/js/autoCompleteTags.js',
          '<%= appDir %>/themes/baggy/js/closeMessage.js',
          '<%= appDir %>/themes/baggy/js/popupForm.js',
          '<%= appDir %>/themes/baggy/js/saveLink.js'
        ],
        dest: '<%= buildDir %>/baggy.js'
      },
      cssMaterial: {
        src: [
          'node_modules/materialize-css/bin/materialize.css',
          '<%= appDir %>/themes/material/css/*.css',
          '<%= appDir %>/lib/icomoon-bower/style.css'
        ],
        dest: '<%= buildDir %>/material.css'
      },
      cssBaggy: {
        src: [
          '<%= appDir %>/themes/baggy/css/*.css'
        ],
        dest: '<%= buildDir %>/baggy.css'
      }
    },
    browserify: {
      '<%= buildDir %>/material.browser.js': ['<%= buildDir %>/material.js'],
      '<%= buildDir %>/baggy.browser.js': ['<%= buildDir %>/baggy.js']
    },
    uglify: {
      material: {
        files: {
          '<%= buildDir %>/themes/material/js/material.min.js':
            ['<%= buildDir %>/material.browser.js']
        },
        options: {
          sourceMap: true,
          sourceMapName: '<%= buildDir %>/themes/material/js/material.map'
        },
      },
      baggy: {
        files: {
          '<%= buildDir %>/themes/baggy/js/baggy.min.js':
            ['<%= buildDir %>/baggy.browser.js']
        },
        options: {
          sourceMap: true,
          sourceMapName: '<%= buildDir %>/themes/baggy/js/baggy.map'
        },
      },
    },
    copy: {
      pickerjs: {
        expand: true,
        cwd: 'node_modules/pickadate/lib',
        src: 'picker.js',
        dest: '<%= buildDir %>'
      },
      annotator: {
        expand: true,
        cwd: 'node_modules/annotator/pkg',
        src: 'annotator.min.js',
        dest: '<%= buildDir %>/themes/_global/js/'
      }
    },
    symlink: {
      baggyfonts: {
        files: [
          {
            expand: true,
            overwrite: true,
            cwd: "<%= appDir %>/lib/icomoon-bower/",
            src: "fonts",
            dest: "<%= buildDir %>/themes/baggy/"
          },
          {
            expand: true,
            overwrite: true,
            cwd: "<%= appDir %>/lib/bower-pt-sans/fonts",
            src: "*",
            dest: "<%= buildDir %>/themes/baggy/fonts"
          }
        ]
      },
      materialfonts: {
        files: [
          {
            expand: true,
            overwrite: true,
            cwd: '<%= appDir %>/themes/material/',
            src: "font",
            dest: '<%= buildDir %>/themes/material/'
          }
        ]
      },
      pics: {
        files: [
          {
            expand: true,
            overwrite: true,
            cwd: '<%= appDir %>/themes/_global/',
            src: 'img',
            dest: '<%= buildDir %>/themes/_global/'
          }
        ]
      }
    },
    clean: {
      css: {
        src: [ '<%= buildDir %>/**/*.css' ]
      },
      js: {
        src: ['<%= buildDir %>/**/*.js', '<%= buildDir %>/**/*.map']
      },
      all: {
        src: ['./<%= buildDir %>']
      }
    }
  });

  grunt.registerTask(
    'fonts',
    'Install fonts',
    ['symlink']
    );
  grunt.registerTask(
    'js',
    'Build and install js files',
    ['clean:js', 'concat:js', 'browserify', 'uglify']
    );
  grunt.registerTask(
    'default',
    'Build and install everything',
    ['clean', 'copy:pickerjs', 'concat', 'browserify', 'uglify', 'cssmin', 'symlink']
    );
  grunt.registerTask(
    'css',
    'Compiles the stylesheets.',
    ['clean:css', 'concat:css', 'cssmin']
  );

  grunt.registerTask(
    'scripts',
    'Compiles the JavaScript files.',
    [ 'uglify', 'clean:js' ]
  );

grunt.registerTask(
    'baggy',
    'Do everything for baggy',
    ['clean', 'copy:pickerjs', 'concat', 'browserify', 'uglify:baggy', 'cssmin:baggy', 'symlink']
  );

}
