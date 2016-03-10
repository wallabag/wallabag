module.exports = function (grunt) {
  require('load-grunt-tasks')(grunt);

  grunt.initConfig({
    appDir: 'app/Resources/static',
    buildDir: 'web/bundles/wallabagcore',

    cssmin: {
      combine: {
        options: {
          report: 'gzip',
          keepSpecialComments: 0
        },
        files: {
          '<%= buildDir %>/themes/material/css/style.min.css': [
            '<%= buildDir %>/concat.css'
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
          '<%= appDir %>/themes/material/js/*.js'
        ],
        dest: '<%= buildDir %>/app.js'
      },
      css: {
        src: [
          'node_modules/materialize-css/bin/materialize.css',
          '<%= appDir %>/themes/material/css/*.css',
          '<%= appDir %>/lib/icomoon-bower/style.css'
        ],
        dest: '<%= buildDir %>/concat.css'
      }
    },
    browserify: {
      '<%= buildDir %>/app.browser.js': ['<%= buildDir %>/app.js']
    },
    uglify: {
      options: {
        sourceMap: true,
        sourceMapName: '<%= buildDir %>/themes/_global/js/app.map'
      },
      dist: {
        files: {
          '<%= buildDir %>/themes/_global/js/app.min.js':
            ['<%= buildDir %>/app.browser.js']
        }
      }
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

}
