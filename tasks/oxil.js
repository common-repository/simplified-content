var gulp = require('gulp');
var shell = require('gulp-shell');
var bump = require('gulp-bump');
var filter = require('gulp-filter');
var tag = require('gulp-tag-version');
var semver = require('semver');        // Used by bump.
var fs = require('fs');        // Used by bump.
var using = require('gulp-using');
var rsync = require('gulp-rsync');
var replace = require('replace-in-file');
var notify = require('gulp-notify');
var runSequence = require('run-sequence');
var del = require('del');
var argv = require('yargs').argv;

// eg. gulp commit --message "This is a test message"


var tagFiles = [
    './**',
    '!bower_components',
    '!bower_components/**',
    '!node_modules',
    '!node_modules/**',
    '!package.json',
    '!gulpfile.js',
    '!tasks',
    '!tasks/**'
];

var ooasvnrepo = 'https://pl3.projectlocker.com/OxfordInformationLabs/OOAWPFramework/svn/src/';

// Parses the package.json file. We use this because its values
// change during execution.
var getPackageJSON = function () {
    return JSON.parse(fs.readFileSync('./package.json', 'utf8'));
};

function inc(importance) {

    var pkg = getPackageJSON();
    var newversion = 'Version: ' + semver.inc(pkg.version, importance);

    //var banner = ['/*',
    //'Theme Name: ' + pkg.description,
    //'Theme URI: '+ pkg.homepage,
    //'Author: '+ pkg.author,
    //'Version: '+ newversion,
    //'License: '+ pkg.license,
    //'*/',
    //''].join('\n');
    //
    //process.stdout.write('New version: ' + newversion + '\n ' + banner + '\n');
    //
    //fs.writeFile('./style.css', banner);

    // get all the files to bump version in
    return gulp.src(['./package.json'])
        // bump the version number in those files
        .pipe(bump({type: importance}))
        // save it back to filesystem
        .pipe(gulp.dest('./'));
}


function pluginversion() {
    var pkg = getPackageJSON();
    var newVersion = 'Version: ' + pkg.version;
    //console.log(newVersion);

    return replace({
        files: ['./simplified-content.php'],
        replace: /Version:.*/g,
        with: newVersion
    }, function (error, changedFiles) {

        //Catch errors
        if (error) {
            return console.error('Error occurred:', error);
        }

        //List changed files
        console.log('Modified files:', changedFiles.join(', '));
    });

}

gulp.task('export-ooawpframework',
    shell.task([
        'svn remove --force ooawpframework',
        'svn export https://pl3.projectlocker.com/OxfordInformationLabs/OOAWPFramework/svn/src ooawpframework',
        'svn export https://pl3.projectlocker.com/OxfordInformationLabs/OOACore/svn/dev/src/common ooawpframework/ooacore/common',
        'svn export https://pl3.projectlocker.com/OxfordInformationLabs/OOACore/svn/dev/src/transfer/framework ooawpframework/ooacore/transfer/framework',
        'svn export https://pl3.projectlocker.com/OxfordInformationLabs/OOACore/svn/dev/src/transfer/json ooawpframework/ooacore/transfer/json',
        'svn add ooawpframework'
    ]));


gulp.task('bump-patch', function () {
    return inc('patch');
})
gulp.task('bump-feature', function () {
    return inc('minor');
})
gulp.task('bump-release', function () {
    return inc('major');
})

gulp.task('update-plugin-version', function () {
    return pluginversion();
})


gulp.task('patch', function (cb) {
    runSequence(
        'bump-patch',
        'update-plugin-version',
        cb
    );

});

gulp.task('build',  function () {
    gulp.start('export-ooawpframework');
});

gulp.task('tag', function () {

    var pkg = getPackageJSON();

    return gulp.src(tagFiles).pipe(rsync({destination: "../tags/" + pkg.version}));

});





