var gulp = require('gulp');
var sass = require('gulp-sass');
var tar = require('gulp-tar');
var gzip = require('gulp-gzip');

gulp.task('copy', function() {
    gulp.src('src/**/*')
        .pipe(gulp.dest('./dist/'));
});

gulp.task('sass', function() {
    gulp.src('src/**/*.scss')
        .pipe(sass().on('error', sass.logError))
        .pipe(gulp.dest('./dist/'));
});

gulp.task('tarball', function() {
    gulp.src('./dist/*')
    .pipe(tar('tb_megamenu.tar'))
    .pipe(gzip())
    .pipe(gulp.dest('.'));
});

gulp.task('default', [ 'copy', 'tarball' ]);