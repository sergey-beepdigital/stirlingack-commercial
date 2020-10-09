'use strict';

/**
 * Dependencies and other variables should be listed out here.
 */
const gulp = require('gulp'),
    sass = require('gulp-sass'),
    sourcemaps = require('gulp-sourcemaps'),
    imagemin = require('gulp-imagemin'),
    args = require('yargs').argv,
    gulpif = require('gulp-if'),
    webpack = require('webpack-stream'),
    autoprefixer = require('autoprefixer'),
    postcss = require('gulp-postcss'),
    entryPlus = require('webpack-entry-plus'),
    glob = require('glob'),
    rename = require('gulp-rename'),
    changed = require('gulp-changed'),
    debug = require('gulp-debug'),
    replace = require('gulp-replace'),
    theme = 'crowd-base-build', // Define the theme name for packaging
    paths = {
        sass: {
            essential: {
                src: 'src/sass/essential/**/*.scss',
                name: 'main.css',
                dist: 'dist/styles'
            },
            deferred: {
                src: 'src/sass/deferred/**/*.scss',
                name: 'deferred.css',
                dist: 'dist/styles'
            }
        },
        js: {
            src: [
                'src/js/**/*.js', // Place js here that is essential to the site, will be returned in the <head>.
            ],
            dist: 'dist/js',
            entries: [
                {
                    entryFiles: glob.sync('./src/js/essential/**/*.js'),
                    outputName: 'essential'
                },
                {
                    entryFiles: glob.sync('./src/js/deferred/**/*.js'),
                    outputName: 'deferred'
                },
            ]
        },
        images: {
            src: 'src/images/**/*',
            dist: 'dist/images'
        },
        fonts: {
            src: 'src/fonts/**/*',
            dist: 'dist/fonts'
        },
        cache: {
            src: './includes/cache_bust.php',
            dest: './includes/'
        },
        packageWhitelist: [ //Customise to your own folder structure
            '*.{php,png,css,zip}',
            'acf-json/**/*.json',
            'includes/**/*.php',
            'includes/plugins/advanced-custom-fields-pro.zip',
            'dist/**/*',
            'components/**/*.twig',
            'templates/**/*.twig',
            'login/**/*'
        ],
        acf: {
            src: 'includes/toggle_acf_edit.php',
            dest: 'includes/'
        }
    };

const styleFunctions = [];

/*
 * For each item in paths.sass, creates a function that runs all the gulp pipelines
 */
for (let [key, value] of Object.entries(paths.sass)) {
    value.function = () => {
        return gulp.src(value.src)
            .pipe(gulpif(!args.production, sourcemaps.init()))
            .pipe(sass())
            .on('error', sass.logError)
            .pipe(postcss([autoprefixer()]))
            .pipe(gulpif(!args.production, sourcemaps.write('.')))
            .pipe(rename(value.name))
            .pipe(gulp.dest(value.dist));
    }
    Object.defineProperty(value.function, 'name', {value: key, writable: false});
}

/*
*   Styles
*
*   Runs all style functions
*/
function styles(done) {
    for (let [key, value] of Object.entries(paths.sass)) {
        value.function();
    }
    done();
}

/**
 *  JavaScript
 * 
 *  Runs the JS bundler.
 *  To separate JS bundles, add paths to the 'entries' array in paths->js
 */
function scripts() {
    let buildMode = 'development';
    if (args.production) {
        buildMode = 'production';
    }
    return gulp.src(paths.js.src)
        .pipe(sourcemaps.init())
        .pipe(webpack({
            mode: buildMode,
            entry: entryPlus(paths.js.entries),
            output: {
                filename: '[name].js'
            },
            module: {
                rules: [
                    {
                        test: /\.js$/,
                        exclude: /(node_modules|bower_components)/,
                        use: {
                            loader: "babel-loader",
                            options: {
                                presets: ["@babel/preset-env"],
                            }
                        }
                    },
                ]
            }
        }))
        .pipe(sourcemaps.write('.'))
        .pipe(gulp.dest(paths.js.dist));
}

/**
 * `gulp images`
 *
 * Pipes changed/new images.
 * Optimises files for filesize, including SVGs.
 */
function images() {
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
}

/**
 * `gulp fonts`
 *
 * Pipes changed source fonts -> dist
 */
function fonts() {
    return gulp.src(paths.fonts.src)
        .pipe(changed(paths.fonts.dist))
        .pipe(debug({title: 'fonts'}))
        .pipe(gulp.dest(paths.fonts.dist));
}

/**
 *  Cache Bust
 * 
 *  Changes the php variable for cache versions to the current timestamp
 */
function cacheBust() {
    let cbString = new Date().getTime();
    return gulp.src(paths.cache.src)
        .pipe(replace(/\$cache_ver=\d+/g, () => {
            return '\$cache_ver=' + cbString;
        }))
        .pipe(gulp.dest(paths.cache.dest));
}

function watch() {
    // Watch each entry in paths.sass separately
    for (let [key, value] of Object.entries(paths.sass)) {
        gulp.watch(value.src, gulp.series(value.function, cacheBust));
    }
    gulp.watch(paths.js.src, gulp.series(scripts, cacheBust));
    gulp.watch(paths.images.src, gulp.series(images));
}

/**
 *  Deploy
 * 
 *  Runs the build tasks, with minification. Then moves eveything into a package folder.
 */
function deploy() {
    return gulp.src(paths.packageWhitelist, { base: './' })
        .pipe(gulpif(args.pipeline, gulp.dest('pipeline/'), gulp.dest('../' + theme + '-package/')));
}

function disableAcf() {
    let src = '';
    let dest = '';
    if (args.pipeline) {
        src = `pipeline/${paths.acf.src}`;
        dest = `pipeline/${paths.acf.dest}`;
    } else {
        src = `../${theme}-package/${paths.acf.src}`
        dest = `../${theme}-package/${paths.acf.dest}`;
    }
    return gulp.src(src)
        .pipe(replace(/\$showacf=\w+/g, () => {
            return '\$showacf=false';
        })).pipe(gulp.dest(dest));
}

gulp.task('default', gulp.series(fonts, images, styles, scripts, watch));

gulp.task('package', gulp.series(fonts, images, styles, scripts, deploy, disableAcf));