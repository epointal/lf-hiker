var version = '0.5.1';
var gulp = require('gulp');
var less = require('gulp-less');
var minify = require('gulp-minify');
var cssmin = require('gulp-cssmin');
var rename = require('gulp-rename');
var concat = require('gulp-concat');


gulp.task('default', function() {
  console.log("Ne fait rien");
});

gulp.task('versioning', function(){
	//all my editing css
	gulp.src([
	'lib/awesome-marker/leaflet.awesome-markers.css',
	'css/lfh-style.css'])
	.pipe(concat('lfh-style.css'))
	.pipe(cssmin())
	.pipe(rename({suffix: '-min.'+version}))
	.pipe(gulp.dest('dist'));
	
	gulp.src(['css/*.css', '!css/lfh-style.css'])
	.pipe(rename({suffix: '.'+version}))
    .pipe(gulp.dest('dist'));
	//all js in the same file
	gulp.src([
	'lib/awesome-marker/leaflet.awesome-markers.js',
	'lib/leaflet-gpx.js',
	'js/lfh-plugin.js'])
	.pipe(concat('lfh-front.js'))
	.pipe(minify())
	.pipe(rename({suffix: '.'+version}))
	.pipe(gulp.dest('dist'));
	
	gulp.src(['js/tinymce-lfh-plugin.js',
	          'js/lfh-post-editor.js'])
	.pipe(rename({suffix: '.'+version}))
    .pipe(gulp.dest('dist'));
	
	
	//copy images
	//from awesome-marker
	gulp.src('lib/awesome-marker/images/*')
	.pipe(gulp.dest('dist/images'));
	//copy images
	

});
