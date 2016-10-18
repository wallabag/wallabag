module.exports = function (grunt) {
  require('load-grunt-tasks')(grunt);

  grunt.initConfig({
    appDir: 'app/Resources/static',
    buildDir: 'app/Resources/build',
    modulesDir: 'node_modules',
    releaseDir: 'web/bundles/wallabagcore',

    postcss: {
      material: {
        options: {
          processors: [
            require('pixrem')(),
            require('autoprefixer')({ browsers: 'last 2 versions' }),
            require('cssnano')(),
          ],
        },
        src: '<%= buildDir %>/material.css',
        dest: '<%= releaseDir %>/themes/material/css/style.min.css',
      },
      baggy: {
        options: {
          processors: [
            require('pixrem')(),
            require('autoprefixer')({ browsers: 'last 2 versions' }),
            require('cssnano')(),
          ],
        },
        src: '<%= buildDir %>/baggy.css',
        dest: '<%= releaseDir %>/themes/baggy/css/style.min.css',
      },
    },
    concat: {
      options: {
        separator: ';',
      },
      cssMaterial: {
        src: [
          'node_modules/materialize-css/bin/materialize.css',
          '<%= appDir %>/themes/material/css/*.css',
        ],
        dest: '<%= buildDir %>/material.css',
      },
      cssBaggy: {
        src: [
          '<%= appDir %>/themes/baggy/css/*.css',
        ],
        dest: '<%= buildDir %>/baggy.css',
      },
    },
    browserify: {
      dist: {
        files: {
          '<%= buildDir %>/material.browser.js': ['<%= appDir %>/themes/material/js/init.js'],
          '<%= buildDir %>/baggy.browser.js': ['<%= appDir %>/themes/baggy/js/init.js']
        }
      },
      options: {
        sourceType: "module",
        transform: [
          ["babelify", {
          presets: ["es2015"]
        }], ["browserify-shim", {
            "jquery": {
              "exports": "$"
            },
            "materialize": "materialize",
            "jquery-ui": {
              "depends": "jquery",
              "exports": null
            }
          }]
        ],
        browserifyOptions: {
          browser: {
            "jQuery": "./node_modules/jquery/dist/jquery.js",
            "jquery.tinydot": "./node_modules/jquery.tinydot/src/jquery.tinydot.js",
            "jquery.ui": "./node_modules/jquery-ui-browserify/dist/jquery-ui.js"
          }
        }
      }

    },
    uglify: {
      material: {
        files: {
          '<%= releaseDir %>/themes/material/js/material.min.js':
            ['<%= buildDir %>/material.browser.js'],
        }
      },
      baggy: {
        files: {
          '<%= releaseDir %>/themes/baggy/js/baggy.min.js':
            ['<%= buildDir %>/baggy.browser.js'],
        }
      },
    },
    copy: {
      pickerjs: {
        expand: true,
        cwd: '<%= modulesDir %>/pickadate/lib',
        src: 'picker.js',
        dest: '<%= buildDir %>',
      },
      annotator: {
        expand: true,
        cwd: '<%= modulesDir %>/annotator/pkg',
        src: 'annotator.min.js',
        dest: '<%= buildDir %>/themes/_global/js/',
      },
      baggyfonts: {
        files: [
          {
            expand: true,
            cwd: '<%= modulesDir %>/icomoon-free-npm/Font',
            src: 'IcoMoon-Free.ttf',
            dest: '<%= releaseDir %>/themes/baggy/fonts/',
          },
          {
            expand: true,
            cwd: '<%= modulesDir %>/ptsans-npm-webfont/fonts',
            src: 'ptsansbold.woff',
            dest: '<%= releaseDir %>/themes/baggy/fonts/',
          },
          {
            expand: true,
            cwd: '<%= modulesDir %>/material-design-icons-iconfont/dist/fonts/',
            src: ['MaterialIcons-Regular.eot', 'MaterialIcons-Regular.woff2', 'MaterialIcons-Regular.woff', 'MaterialIcons-Regular.ttf'],
            dest: '<%= releaseDir %>/themes/baggy/fonts/',
          },
        ],
      },
      materialfonts: {
        files: [
          {
            expand: true,
            overwrite: true,
            cwd: '<%= modulesDir %>/icomoon-free-npm/Font',
            src: 'IcoMoon-Free.ttf',
            dest: '<%= releaseDir %>/themes/material/fonts',
          },
          {
            expand: true,
            overwrite: true,
            cwd: '<%= modulesDir %>/roboto-fontface/fonts/Roboto',
            src: '*',
            dest: '<%= releaseDir %>/themes/material/font/roboto',
          },
          {
            expand: true,
            overwrite: true,
            cwd: '<%= modulesDir %>/material-design-icons-iconfont/dist/fonts/',
            src: ['MaterialIcons-Regular.eot', 'MaterialIcons-Regular.woff2', 'MaterialIcons-Regular.woff', 'MaterialIcons-Regular.ttf'],
            dest: '<%= releaseDir %>/themes/material/fonts/',
          },
        ],
      },
    },
    symlink: {
      pics: {
        files: [
          {
            expand: true,
            overwrite: true,
            cwd: '<%= appDir %>/themes/_global/',
            src: 'img',
            dest: '<%= releaseDir %>/themes/_global/',
          },
        ],
      },
    },
    clean: {
      css: {
        src: ['<%= buildDir %>/**/*.css'],
      },
      js: {
        src: ['<%= buildDir %>/**/*.js', '<%= buildDir %>/**/*.map'],
      },
      all: {
        src: ['./<%= buildDir %>'],
      },
      release: {
        src: ['./<%= releaseDir %>/*'],
      }
    },
    eslint: {
      target: ['<%= appDir %>/themes/material/js/init.js', '<%= appDir %>/themes/baggy/js/init.js']
    },
    stylelint: {
      target: ['<%= appDir %>/themes/material/css/*.css', '<%= appDir %>/themes/baggy/css/*.css']
    }

  });

  grunt.registerTask(
    'fonts',
    'Install fonts',
    ['copy:baggyfonts', 'copy:materialfonts']
    );

  grunt.registerTask(
    'js',
    'Build and install js files',
    ['clean:js', 'copy:pickerjs', 'browserify', 'uglify']
    );

  grunt.registerTask(
    'default',
    'Build and install everything',
    ['clean', 'copy:pickerjs', 'concat', 'browserify', 'uglify', 'postcss', 'copy', 'symlink']
    );

  grunt.registerTask(
    'css',
    'Compiles the stylesheets.',
    ['clean:css', 'concat:cssMaterial', 'concat:cssBaggy', 'postcss']
  );

  grunt.registerTask(
      'tests',
      'Test css and js style conformity',
      ['eslint', 'stylelint', 'default']
  )
};
