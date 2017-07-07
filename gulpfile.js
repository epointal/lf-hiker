var version = '1.3';
var gulp = require('gulp');
var less = require('gulp-less');
var minify = require('gulp-minify');
var cssmin = require('gulp-cssmin');
var rename = require('gulp-rename');
var concat = require('gulp-concat');
var uncss = require('gulp-uncss');

gulp.task('default', function() {
  console.log("Ne fait rien");
});

// for see if there is unused style
gulp.task('cleancss', function(){
    gulp.src(['css/lfh-style.css' ])
      .pipe(uncss({
          html: [ 'http://rancs.com/blog/fr/presentation/']
      }))
      .pipe(gulp.dest('clean'));
});
gulp.task('lessify', function(){
    return gulp.src('css/lfh-style.less')
    .pipe(less())
    .pipe(gulp.dest('css'));
});

gulp.task('versioning', ['lessify'], function(){
	//all my editing css
	gulp.src([
	'lib/awesome-marker/leaflet.awesome-markers.css',
	'css/lfh-style.css'])
	.pipe(concat('lfh-style.css'))
	.pipe(cssmin())
	.pipe(rename({suffix: '-min.'+version}))
	.pipe(gulp.dest('dist'));
	
	gulp.src(['css/*.css', '!css/lfh-style.css'])
	.pipe(cssmin())
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
	          'js/lfh-post-editor.js',
			  'js/helper.js'])
	.pipe(minify())
	.pipe(rename({suffix: '.'+version}))
    .pipe(gulp.dest('dist'));
	
	
	//copy images
	//from awesome-marker
	gulp.src('lib/awesome-marker/images/*')
	.pipe(gulp.dest('dist/images'));
	//copy images
	

});
