var gulp = require('gulp');
var sass = require('gulp-sass');
var tar = require('gulp-tar');
var gzip = require('gulp-gzip');
var clean = require('gulp-clean');
var replace = require('gulp-replace');

gulp.task('clean', function() {
    gulp.src(['dist', 'build', 'tb_megamenu.tar.gz'], {read: false})
        .pipe(clean());
});

gulp.task('timestamp', function() {
    gulp.src('src/tb_megamenu.info.yml')
        .pipe(replace('1489029834', (new Date()).getTime()))
        .pipe(gulp.dest('./dist'));
});

gulp.task('copy', function() {
    gulp.src(['src/**/*', '!src/**/*.sass'])
        .pipe(gulp.dest('./dist'));
});

gulp.task('sass', function() {
    gulp.src('src/**/*.sass')
        .pipe(sass({outputStyle: 'compressed'}).on('error', sass.logError))
        .pipe(gulp.dest('./dist'));
});

gulp.task('tarball', function() {
    gulp.src('./dist/*')
    .pipe(tar('tb_megamenu.tar'))
    .pipe(gzip())
    .pipe(gulp.dest('.'));
});

gulp.task('default', [ 'sass', 'copy', 'tarball' ]);