const gulp = require('gulp');
const sass = require('gulp-sass');
const cleanCSS = require('gulp-clean-css');
const rename = require("gulp-rename");
const uglify = require('gulp-uglify');
const autoprefixer = require('gulp-autoprefixer');
const browserSync = require('browser-sync');
const plumber = require('gulp-plumber');
const sourcemaps = require('gulp-sourcemaps');

// Set assets paths.
const paths = {
	'css': {
		'src': ['./assets/css/*.css', '!./assets/css/*.min.css'],
		'dest': 'assets/css'
	},
	'sass': {
		'src': ['assets/sass/*.scss'],
		'dest': 'assets/css',
	},
	'js': {
		'src': ['assets/js/*.js', '!assets/js/*.min.js'],
		'dest': 'assets/js',
	},
	'php': ['./*.php', './**/*.php']
};

// Compiles SCSS files from /scss into /css
gulp.task('sass', function () {
	return gulp.src(paths.sass.src)
		.pipe(plumber({
			errorHandler: function (error) {
				console.log(error.message);
				this.emit('end');
			}
		}))
		.pipe(sourcemaps.init())
		.pipe(sass())
		.pipe(autoprefixer({
			'browsers': ['last 2 version']
		}))
		.pipe(sourcemaps.write('./'))
		.pipe(gulp.dest(paths.sass.dest))
		.pipe(browserSync.reload({
			stream: true
		}));
});

// Minify compiled CSS
gulp.task('minify-css', ['sass'], function () {
	return gulp.src(paths.css.src)
		.pipe(cleanCSS({
			compatibility: 'ie8'
		}))
		.pipe(rename({
			suffix: '.min'
		}))
		.pipe(gulp.dest(paths.css.dest));
});

// Minify custom JS
gulp.task('minify-js', function () {
	return gulp.src(paths.js.src)
		.pipe(plumber({
			errorHandler: function (error) {
				console.log(error.message);
				this.emit('end');
			}
		}))
		.pipe(uglify())
		.pipe(rename({
			suffix: '.min'
		}))
		.pipe(gulp.dest(paths.js.dest));
});

// Dev task with watch
gulp.task('watch', function () {

	// Kick off BrowserSync.
	browserSync({
		'open': false,             // Open project in a new tab?
		'injectChanges': true,     // Auto inject changes instead of full reload.
		'proxy': 'https://woo.dev',    // Use http://_s.com:3000 to use BrowserSync.
		'watchOptions': {
			'debounceDelay': 1000  // Wait 1 second before injecting.
		}
	});

	gulp.watch(paths.sass.src, ['sass']);
	gulp.watch(paths.css.src, ['minify-css']);
	gulp.watch(paths.js.src, ['minify-js']).on('change', browserSync.reload);
});

// Default task
gulp.task('default', ['minify-css', 'minify-js']);