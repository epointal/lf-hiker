//need to be very carefull with the replace version number
//there are in files readme others version numbers: for releases, tested browsers, and the most important wordpress

var old_version = '1.13.0';
var version = '2.0.0';
var gulp = require('gulp');
var less = require('gulp-less');
var minify = require('gulp-minify');
var cssmin = require('gulp-cssmin');
var rename = require('gulp-rename');
var concat = require('gulp-concat');
var uncss = require('gulp-uncss');
var replace = require('gulp-replace');

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
	 gulp.src('css/lfh-map-editor.less')
    .pipe(less())
    .pipe(gulp.dest('css'));
    return gulp.src('css/lfh-style.less')
    .pipe(less())
    .pipe(gulp.dest('css'));
	
});

gulp.task('rename', function(){
	gulp.src([ 'readme.txt', 'readme.md', 'lf-hiker.php'])
	.pipe(rename({suffix: '-back'}))
	    .pipe(gulp.dest(''));
});
gulp.task('new', /* ['rename'] ,*/ function(){
	if(old_version){
		//replace version by the new version in files
		//readme.txt
		// javascript does not supported negative lookahead
		//rename the old file
		
		var pattern = new RegExp( old_version+'(?! +\=)', 'g');
		gulp.src(['readme-back.txt'])
		.pipe(replace(pattern,  version))
		.pipe(rename({basename: 'readme'}))
		.pipe(gulp.dest(''));
		
		//readme (add "###" after version number for the release not be changed)
		var pattern = new RegExp( old_version+'(?! +###)', 'g');
		gulp.src(['readme-back.md'])
		.pipe(replace(pattern,  version))
		.pipe(rename({basename: 'readme'}))
		.pipe(gulp.dest(''));
		
		//lf-hiker.php
		var pattern = new RegExp( old_version, 'g');
		gulp.src(['lf-hiker-back.php'])
		.pipe(replace(pattern,  version))
		.pipe(rename({basename: 'lf-hiker'}))
		.pipe(gulp.dest(''));
		
		//svg file
		var pattern = new RegExp( old_version, 'g');
		gulp.src(['assets/svg/version'+old_version+'.svg'])
		.pipe(replace(pattern,  version))
		.pipe(rename({basename: 'version'+version}))
	    .pipe(gulp.dest('assets/svg/'));
	
	}
});
gulp.task('versioning', ['lessify'], function(){
	
	//front my editing css
	gulp.src(['css/lfh-style.css'])
	.pipe(cssmin())
	.pipe(rename({suffix: '-min.'+version}))
	.pipe(gulp.dest('dist'));
	
	// back style
	gulp.src(['css/lfh-map-editor.css', 'css/lfh-gpx-editor.css'])
	.pipe(cssmin())
	.pipe(rename({suffix: '.'+version}))
    .pipe(gulp.dest('dist'));
	
	// front script: all js in the same file 
	gulp.src([
	'lib/awesome-marker/leaflet.awesome-markers.js',
	'lib/leaflet-gpx.js',
	'js/lfh-plugin.js'])
	.pipe(concat('lfh-front.js'))
	.pipe(minify())
	.pipe(rename({suffix: '.'+version}))
	.pipe(gulp.dest('dist'));
	// map editor concat files
	gulp.src([
		'js/leaflet-gpx.js',
		'js/lfh-map-editor-dev.js'])
	.pipe(concat('lfh-map-editor.js'))
	.pipe(minify())
	.pipe(rename({suffix: '.' + version}))
	.pipe(gulp.dest('dist'));

	// back scripts
	gulp.src(['js/lfh-tinymce-helper.js',
			  'js/lfh-gpx-editor.js',
			  'js/lfh-helper.js'])
	.pipe(minify())
	.pipe(rename({suffix: '.'+version}))
    .pipe(gulp.dest('dist'));
	
	
	//copy images
	//from awesome-marker
	gulp.src('lib/awesome-marker/images/*')
	.pipe(gulp.dest('dist/images'));
	
	gulp.src('lib/awesome-marker/images/*')
	.pipe(gulp.dest('css/images'));
	//copy images
	

});
