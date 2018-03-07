'use strict';

/**
 * Dependencies and other variables should be listed out here.
 */
var gulp = require('gulp'),
    sass = require('gulp-sass'),
    sourcemaps = require('gulp-sourcemaps'),
    imagemin = require('gulp-imagemin'),
    args = require('yargs').argv,
    changed = require('gulp-changed'),
    ngAnnotate = require('gulp-ng-annotate'),
    debug = require('gulp-debug'),
    autoprefixer = require('gulp-autoprefixer'),
    modernizr = require('gulp-modernizr'),
    gulpif = require('gulp-if'),
    uglify = require('gulp-uglify'),
    concat = require('gulp-concat'),
    jshint = require('gulp-jshint'),
    wpcachebust = require('gulp-wp-cache-bust'),
    theme = 'crowd-base-build', // Define the theme name for packaging
    paths = {
        sass: {
            src: 'src/sass/**/*.scss',
            dist: 'dist/styles'
        },
        js: {
            src: [
                'src/js/**/*.js',
            ],
            srcDir: 'src/js',
            dist: 'dist/js'
        },
        images: {
            src: 'src/images/**/*',
            dist: 'dist/images'
        },
        fonts: {
            src: 'src/fonts/**/*',
            dist: 'dist/fonts'
        },
        packageWhitelist: [ //Customise to your own folder structure
            '*.{php,png,css,zip}',
            'acf-json/**/*.json',
            'includes/**/*.php',
            'includes/advanced-custom-fields-pro.zip',
            'dist/**/*',
            'components/**/*.twig',
            'templates/**/*.twig'
        ]
    };

/**
 * `gulp sass`
 *
 * Compiles SCSS -> CSS.
 *
 * Also prefixes css properties for legacy browsers, as defined in the
 * autoprefixer options object.
 */
gulp.task('sass', function() {
    return gulp.src(paths.sass.src)
        .pipe(gulpif(!args.production, sourcemaps.init()))
        .pipe(gulpif(args.production, sass({
            outputStyle: 'compressed'
        }).on('error', sass.logError)))
        .pipe(gulpif(!args.production, sass().on('error', sass.logError)))
        .pipe(autoprefixer({
            browsers: ['last 2 versions'],
            flexbox: 'no-2009',
            cascade: false
        }))
        .pipe(gulpif(!args.production, sourcemaps.write()))
        .pipe(gulp.dest(paths.sass.dist));
});

/**
 * `gulp js`
 *
 * Pipes changed source JS -> dist file named bundle.min.js
 *
 * By default all files are concat together and are sourcemapped.
 *
 * JSHint runs over all javascript, and is configured by the .jshintrc file in
 * the repo. Global variables will need to be explicitly declared in this file.
 * [Find out how to do that here](http://jshint.com/docs/options/#globals).
 *
 * JSHint can also be configured to ignore files in the same way .gitignore
 * works, in the .jshintignore file.
 *
 * When running tasks with the --production flag, sourcemaps are removed and the
 * bundle.min.js file is compressed.
 */
gulp.task('js', function() {
    return gulp.src(paths.js.src)
        .pipe(jshint())
        .pipe(jshint.reporter('jshint-stylish'))
        .pipe(gulpif(!args.production, sourcemaps.init()))
        .pipe(concat('bundle.min.js'))
        .pipe(gulpif(args.production, uglify({
            mangle: false
        })))
        .pipe(gulpif(!args.production, sourcemaps.write()))
        .pipe(gulp.dest(paths.js.dist));
});

/**
 * `gulp images`
 *
 * Pipes changed/new images.
 * Optimises files for filesize, including SVGs.
 */
gulp.task('images', function() {
    return gulp.src(paths.images.src)
        .pipe(changed(paths.images.dist))
        .pipe(debug({title: 'images'}))
        .pipe(imagemin([
            imagemin.gifsicle(),
            imagemin.jpegtran(),
            imagemin.optipng(),
            imagemin.svgo({plugins: [
                {mergePaths: false},
                {removeAttrs: false},
                {convertShapeToPath: false},
                {sortAttrs: true}
            ]})
        ]))
        .pipe(gulp.dest(paths.images.dist));
});

/**
 * `gulp fonts`
 *
 * Pipes changed source fonts -> dist
 */
gulp.task('fonts', function() {
    return gulp.src(paths.fonts.src)
        .pipe(changed(paths.fonts.dist))
        .pipe(debug({title: 'fonts'}))
        .pipe(gulp.dest(paths.fonts.dist));
});

/**
 * `gulp modernizr`
 *      Runs `gulp sass` task.
 *
 * Pipes to source JS.
 *
 * Takes compiled CSS and scans for modernizr "feature detects" classes.
 * Generates custom build of modernizr from this information.
 */
gulp.task('modernizr', ['sass'], function() {
    return gulp.src(paths.sass.dist + '/*.css')
        .pipe(modernizr())
        .pipe(gulp.dest(paths.js.srcDir));
});

/**
 * `gulp assets`
 *
 * Process all the assets and send to the package folder
 */
gulp.task('assets', ['sass', 'js', 'images', 'fonts'], function(){
    return gulp.src(paths.packageWhitelist, { base: './' })
      .pipe(gulp.dest('../' + theme + '-package/'));
});

/**
 * `gulp package`
 *
 * Runs the assets task that compiles all assets and sends them to the package
 * directory, then busts the shit out of the cache with a hash.
 */
gulp.task('package', ['assets'], function(){
    return gulp.src('./enqueues.php', {base: './'})
        .pipe(wpcachebust({
            themeFolder: './',
            rootPath: './'
        }))
        .pipe(gulp.dest('../' + theme + '-package/'));
});

gulp.task('default', ['sass', 'js', 'images', 'fonts'], function() {
    gulp.watch(paths.sass.src, ['sass']);
    gulp.watch(paths.js.src, ['js']);
    gulp.watch(paths.images.src, ['images']);
    gulp.watch(paths.fonts.src, ['fonts']);
});
