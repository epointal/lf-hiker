var version = '0.3';
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
	'assets/css/lfh-style.css'])
	.pipe(concat('lfh-style.css'))
	.pipe(cssmin())
	.pipe(rename({suffix: '-min.'+version}))
	.pipe(gulp.dest('dist'));
	
	gulp.src(['assets/css/*.css', '!assets/css/lfh-style.css'])
	.pipe(rename({suffix: '.'+version}))
    .pipe(gulp.dest('dist'));
	//all js in the same file
	gulp.src([
	'lib/awesome-marker/leaflet.awesome-markers.js',
	'assets/js/leaflet-gpx.js',
	'assets/js/lfh-plugin.js'])
	.pipe(concat('lfh-front.js'))
	.pipe(minify())
	.pipe(rename({suffix: '.'+version}))
	.pipe(gulp.dest('dist'));
	
	gulp.src(['assets/js/tinymce-lfh-hiker.js',
	          'assets/js/lfh-post-editor.js'])
	.pipe(rename({suffix: '.'+version}))
    .pipe(gulp.dest('dist'));
	
	
	//copy images
	//from awesome-marker
	gulp.src('lib/awesome-marker/images/*')
	.pipe(gulp.dest('dist/images'));
	//copy images
	//from assets
	gulp.src('assets/images/**/*.*')
	.pipe(gulp.dest('images/'));

});
