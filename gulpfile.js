var gulp = require('gulp');
var wpPot = require('gulp-wp-pot');

gulp.task('pot', function () {
    return gulp.src(["./*.php", "./**/*.php"])
        .pipe(wpPot({
            domain: 'custom-css-for-elementor',
            package: 'Custom CSS for Elementor'
        }))
        .pipe(gulp.dest('languages/custom-css-for-elementor.pot'));
});