module.exports = function(grunt) {
  require('jit-grunt')(grunt);

  grunt.initConfig({

    postcss: {
      //run after copy:cssback, over writes working css
      options: {
        map: false,
        processors: [
          require('autoprefixer')({browsers: ['last 2 version']})
        ]
      },
      dist: {
        //expand: false,
        //flatten: false,
        src: 'src/css/rating-src.css',
        dest: 'src/css/rating.css'
      }
    },

    copy: {
        //coppies to src env, run before copy to dist env.
        cssback: {
          expand: true, 
          cwd: 'src/css/', 
          src: 'rating.css', 
          dest: 'src/css/',
          //creates a backup before we run prefix(postcss) on our working css in src directory.
          rename: function (dest, matchedSrcPath) {
              var splitfile = matchedSrcPath.split('.');
              matchedSrcPath = splitfile[0]+'-src.'+splitfile[1];
              return dest + matchedSrcPath;
          },
        },
        jquerysrc: {
          expand: true, 
          cwd: 'node_modules/jquery/dist', 
          src: 'jquery.min.js', 
          dest: 'src/js'
        },

        //coppies to dist env
        phpconfig: {
          expand: true, 
          cwd: 'src/config',
          src: '**/*',  
          dest: 'dist/config',
        },
        phplib: {
          expand: true, 
          cwd: 'src/lib',
          src: '**/*',  
          dest: 'dist/lib',
        },
        images: {
          expand: true, 
          cwd: 'src/images',
          src: '**/*', 
          dest: 'dist/images',
        },
        css: {
          expand: true, 
          cwd: 'src/css',
          src: '**/*',  
          dest: 'dist/css',
        },
        js: {
          expand: true, 
          cwd: 'src/js',
          src: '**/*',  
          dest: 'dist/js',
        },
        jrating: {
          expand: true, 
          cwd: 'src', 
          src: 'jrating.php', 
          dest: 'dist'
        },
        index: {
          expand: true, 
          cwd: 'src', 
          src: 'index.php', 
          dest: 'dist'
        },
    },
  });

  grunt.loadNpmTasks('grunt-contrib-copy');
  grunt.loadNpmTasks('grunt-postcss');

  grunt.registerTask('default', ['copy:cssback', 'postcss', 'copy:jquerysrc', 'copy:js', 'copy:phpconfig', 'copy:phplib', 'copy:images', 'copy:css', 'copy:jrating', 'copy:index']);

  grunt.registerTask('dist', ['copy:cssback', 'postcss', 'copy:jquerysrc', 'copy:js', 'copy:phpconfig', 'copy:phplib', 'copy:images', 'copy:css', 'copy:jrating', 'copy:index']);

  grunt.registerTask('dev', ['copy:cssback', 'postcss', 'copy:jquerysrc']);

};