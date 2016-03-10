module.exports = function (grunt) {
  require('load-grunt-tasks')(grunt);

  grunt.initConfig({
    cssmin: {
      combine: {
        options:{
          report: 'gzip',
          keepSpecialComments: 0
        },
        files: {
          'web/bundles/wallabagcore/themes/material/css/style.min.css': [
            'web/built/concat.css'
          ]
        }
      }
    },
    concat: {
      options: {
        separator: ';'
      },
      js: {
        src: [
          'node_modules/jquery/dist/jquery.js',
          'node_modules/jquery-ui/jquery-ui.js',
          'node_modules/materialize-css/bin/materialize.js',
          'web/bundles/wallabagcore/themes/material/js/*.js'
        ],
        dest: 'web/built/app.js'
      },
      css: {
        src: [
          'node_modules/materialize-css/bin/materialize.css',
          'app/Resources/static/themes/material/css/main.css',
          'app/Resources/lib/icomoon-bower/style.css'
        ],
        dest: 'web/built/concat.css'
      }
    },
    browserify: {
      'web/built/app.browser.js': ['web/built/app.js']
    },
    uglify: {
      options: {
        sourceMap: true,
        sourceMapName: 'web/bundles/wallabagcore/themes/_global/js/app.map'
      },
      dist: {
        files: {
          'web/bundles/wallabagcore/themes/_global/js/app.min.js':
            ['web/built/app.browser.js']
        }
      }
    },
    copy: {
        baggyfonts: {
            files: [
            {
                cwd: 'app/Resources/lib/icomoon-bower/font',
                src: '**/*',
                dest: 'web/bundles/wallabagcore/themes/baggy/font',
                expand: true
            }
          ]
        },
        materialfonts: {
            files: [
            {
                cwd: 'app/Resources/static/themes/material/font',
                src: '**/*',
                dest: 'web/bundles/wallabagcore/themes/material/font',
                expand: true
            }
          ]
        },
        materialize: {
          files: [
            {
              cwd: 'node_modules/pickadate/lib/',
              src: 'picker.js',
              dest: 'web/built',
              expand: true
            }
          ]
        },
        annotator: {
          files: [
            {
              cwd: 'node_modules/annotator/pkg',
              src: 'annotator.min.js',
              dest: 'web/bundles/wallabagcore/themes/_global/js',
              expand: true
            }
          ]
        }
    },
    clean: {
      css: {
        src: [ 'web/built/**/*.css' ]
      },
      js: {
        src: ['web/built/**/*.js', 'web/built/**/*.map']
      }
    }
  });

  grunt.registerTask(
    'fonts',
    'Install fonts',
    ['copy']
    );
  grunt.registerTask(
    'js',
    'Build and install js files',
    ['clean:js', 'concat:js', 'copy:materialize', 'browserify', 'uglify']
    );
  grunt.registerTask(
    'default',
    'Build and install everything',
    ['clean', 'concat', 'copy', 'browserify', 'uglify', 'cssmin']
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

}
