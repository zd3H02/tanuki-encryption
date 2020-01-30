'use strict';

const gulp        = require('gulp');
const browserSync = require('browser-sync').create();
const sass        = require('gulp-sass');
const del         = require('del');
const plumber     = require('gulp-plumber');

gulp.task('delHtml',function() {
  del(['html/*.html']);
});
gulp.task('delJs',function() {
  del(['html/js/*.js']);
});
gulp.task('delSass',function() {
  del(['html/css/*.css']);
});

gulp.task('copyHtml',function() {
  return gulp.src(['assets/**/*.html'])
  .pipe(plumber())
  .pipe(gulp.dest('html/'))
  .pipe(browserSync.stream());
});

gulp.task('copyJs',function() {
  return gulp.src(['assets/js/*.js'])
  .pipe(plumber())
  .pipe(gulp.dest('html/js/'))
  .pipe(browserSync.stream());
});

gulp.task('sass',function() {
  return gulp.src(['assets/scss/*.scss'])
  .pipe(plumber(
    {
      errorHandler: function(err) {
        console.log(err.messageFormatted);
        this.emit('end');
      }
    }))
  .pipe(sass({outputStyle: 'compressed'}))
  .pipe(gulp.dest('html/css/'))
  .pipe(browserSync.stream());
});

gulp.task('default',['delHtml','delJs','delSass','copyHtml','copyJs','sass'], function() {
  browserSync.init({
    server: {
      baseDir: 'html'
    }
  });
  gulp.watch(['assets/**/*.html'],['delHtml','copyHtml']);
  gulp.watch(['assets/js/*.js'],['delJs','copyJs']);
  gulp.watch(['assets/scss/*.scss'],['delSass','sass']);
})
