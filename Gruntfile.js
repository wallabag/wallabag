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
          'web/built/min.css': [
            '.tmp/css/**/*.css'
          ]
        }
      }
    }, //end cssmin
    uglify: {
      options: {
        mangle: false,
        sourceMap: true,
        sourceMapName: 'web/built/app.map'
      },
      dist: {
        files: {
          'web/built/app.min.js':[
            'app/Resources/lib/jquery/jquery.js',
            'app/Resources/lib/',
            '.tmp/js/**/*.js'
          ]
        }
      }
    }
  });

  grunt.registerTask('css', ['cssmin']);
  grunt.registerTask('javascript', ['uglify']);
}