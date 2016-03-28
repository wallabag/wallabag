module.exports = function (grunt) {
  require('load-grunt-tasks')(grunt);

  grunt.initConfig({
    appDir: 'app/Resources/static',
    buildDir: 'web/bundles/wallabagcore',

    postcss: {
      material: {
        options: {
          map: {
              inline: false
          },

          processors: [
            require('pixrem')(),
            require('autoprefixer')({browsers: 'last 2 versions'}),
            require('cssnano')()
          ]
        },
        src: '<%= buildDir %>/material.css',
        dest: '<%= buildDir %>/themes/material/css/style.min.css'
      },
      baggy: {
        options: {
          map: {
              inline: false
          },

          processors: [
            require('pixrem')(),
            require('autoprefixer')({browsers: 'last 2 versions'}),
            require('cssnano')()
          ]
        },
        src: '<%= buildDir %>/baggy.css',
        dest: '<%= buildDir %>/themes/baggy/css/style.min.css'
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
          '<%= appDir %>/themes/_global/js/restoreScroll.js',
          '<%= appDir %>/themes/material/js/init.js'
        ],
        dest: '<%= buildDir %>/material.js'
      },
      jsBaggy: {
        src: [
          'node_modules/jquery/dist/jquery.js',
          'node_modules/jquery-ui/jquery-ui.js',
          '<%= appDir %>/themes/baggy/js/init.js',
          '<%= appDir %>/themes/_global/js/restoreScroll.js',
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
          '<%= appDir %>/themes/material/css/*.css'
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
        },
      },
      baggy: {
        files: {
          '<%= buildDir %>/themes/baggy/js/baggy.min.js':
            ['<%= buildDir %>/baggy.browser.js']
        },
        options: {
          sourceMap: true,
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
            cwd: "<%= appDir %>/lib/icomoon-bower/",
            src: "fonts",
            dest: "<%= buildDir %>/themes/material/"
          },
          {
            expand: true,
            overwrite: true,
            cwd: "node_modules/materialize-css/",
            src: "font",
            dest: "<%= buildDir %>/themes/material"
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
    ['symlink:baggyfonts', 'symlink:materialfonts']
    );

  grunt.registerTask(
    'js',
    'Build and install js files',
    ['clean:js', 'copy:pickerjs', 'concat:jsMaterial', 'concat:jsBaggy', 'browserify', 'uglify']
    );

  grunt.registerTask(
    'default',
    'Build and install everything',
    ['clean', 'copy:pickerjs', 'concat', 'browserify', 'uglify', 'postcss', 'symlink']
    );

  grunt.registerTask(
    'css',
    'Compiles the stylesheets.',
    ['clean:css', 'concat:cssMaterial', 'concat:cssBaggy', 'postcss']
  );
}
