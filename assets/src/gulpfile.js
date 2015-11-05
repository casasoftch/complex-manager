var gulp = require('gulp');
var less = require('gulp-less');
var autoprefixer = require('gulp-autoprefixer');
var minifyCSS = require('gulp-minify-css');
var uglify = require('gulp-uglify');


gulp.task('less', function() {
  gulp.src('./bower_components/featherlight/release/featherlight.min.css').pipe(gulp.dest('./../js'));
  gulp.src('./bower_components/featherlight/release/featherlight.gallery.min.css').pipe(gulp.dest('./../js'));
});

gulp.task('js', function(){
	gulp.src('js/complex-manager-front.js').pipe(uglify()).pipe(gulp.dest('./../js'));
	gulp.src('js/complex-manager-options.js').pipe(uglify()).pipe(gulp.dest('./../js'));
	gulp.src('js/complexmanager-meta-box.js').pipe(uglify()).pipe(gulp.dest('./../js'));
	gulp.src('js/jquery.canvasAreaDraw.min.js').pipe(uglify()).pipe(gulp.dest('./../js'));
});

gulp.task('default', ['less', 'js']);