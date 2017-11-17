var gulp = require('gulp');
var less = require('gulp-less');
var postcss = require('gulp-postcss');
const autoprefixer = require('autoprefixer');
var path = require("path");

var gutil = require('gulp-util');
var rename = require('gulp-rename');

gulp.task('less', function () {
    var processors = [
        autoprefixer
    ];

    // gutil.log("the path",path.join(__dirname, 'less', 'includes'))

    return gulp.src('./less/style.less')
        .pipe(less({
                paths: [
                    path.join(__dirname, 'less','bootstrap'),
                    path.join(__dirname, 'less','plugins'),
                    path.join(__dirname, 'less'),
                ]
            })
                .on('error', gutil.log)
                .on('error', gutil.beep)
        )
        .pipe(postcss(processors))
        .pipe(rename('./style.css'))
        .pipe(gulp.dest(''))
});

// Watch task
gulp.task('watch', function () {
    gulp.watch('./less/**/*.less', ['less']);
});

// Default task
gulp.task('default', ['watch','less']);

