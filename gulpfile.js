// ## Globals
var gulp         = require('gulp');

// Include OXIL gulp tasks
require('require-dir')('./tasks');


// ### Gulp
// `gulp` - Run a complete build. To compile for production run `gulp --production`.
gulp.task('default',['clean'],function() {
  gulp.start('sync-to-wordpress');
});
