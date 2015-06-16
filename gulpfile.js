var gulp = require('gulp');
var uglify = require('gulp-uglify');
var concat = require('gulp-concat');
var clean = require('gulp-clean');

gulp.task('clean', function() {
    return gulp.src([
            'public/client/build/*',
            'public/client/release/all.min.js'
        ], {read: false})
        .pipe(clean());
});

gulp.task('uglify', function() {
    return gulp.src([
            'public/client/js/**/*js'
        ])
        .pipe(uglify())
        .pipe(gulp.dest("public/client/build/js"));
});

gulp.task('concat', function() {
    var fs = require("fs");
    var loadJsFiles = fs.readFileSync("public/client/index.html").toString();
    var files = loadJsFiles.match(/js\/.*?\.js/g);

    files = files.map(function (file) {
        return "public/client/build/" + file;
    });

    return gulp.src(files)
        .pipe(concat("all.min.js"))
        .pipe(gulp.dest('public/client/release'));
});

gulp.task('build', gulp.series('clean', 'uglify', 'concat'));
