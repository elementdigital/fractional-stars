module.exports = function(grunt) {
  require('jit-grunt')(grunt);

  grunt.initConfig({

    copy: {
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
        jquery: {
          expand: true, 
          cwd: 'node_modules/jquery/dist', 
          src: 'jquery.min.js', 
          dest: 'dist/js',
        },
        index: {
          expand: true, 
          cwd: 'src', 
          src: 'index.php', 
          dest: 'dist'
        },
        jrating: {
          expand: true, 
          cwd: 'src', 
          src: 'jrating.php', 
          dest: 'dist'
        },
        //coppies to src env
        jquerysrc: {
          expand: true, 
          cwd: 'node_modules/jquery/dist', 
          src: 'jquery.min.js', 
          dest: 'src/js'
        },
    },
  });

  grunt.loadNpmTasks('grunt-contrib-copy');

  grunt.registerTask('default', ['copy:phpconfig', 'copy:phplib', 'copy:images', 'copy:css', 'copy:jquery', 'copy:jquerysrc', 'copy:jrating', 'copy:index']);

  grunt.registerTask('dist', ['copy:images', 'copy:phplib', 'copy:css', 'copy:jquery', 'copy:jrating', 'copy:index']);

  grunt.registerTask('dev', ['copy:jquery']);
};