"use strict";

var gulp   = require("gulp");
var util   = require("gulp-util");
var data   = require("gulp-data");
var noop   = require("gulp-noop");
var rename = require("gulp-rename");
var header = require("gulp-header");
var jshint = require("gulp-jshint");
var uglify = require("gulp-uglify");
var concat = require("gulp-concat-util");
var pkg    = require("./package.json");
var nl     = require("os").EOL;



/*
** CONFIG & PATHS
*/



var config = {
    header  : "/*! jQuery & Zepto Lazy <%= info %> - <%= pkg.homepage %> - MIT&GPL-2.0 license - Copyright 2012-<%= year %> <%= pkg.author.name %> */",
    main    : pkg.main,
    plugins : [
        "plugins/jquery.lazy.*.js",
        "!plugins/jquery.lazy.*.min.js"
    ]
};



/*
** PIPES
*/



var pipes  = {};

// check files with jshint
pipes.validateFiles = function(files) {
    return gulp.src(files, {base: "./"})
               .pipe(jshint())
               .pipe(jshint.reporter("jshint-stylish"));
};

// check all files
pipes.validate = function() {
    return pipes.validateFiles([config.main].concat(config.plugins));
};

// build main project file
pipes.buildMain = function() {
    return pipes.validateFiles(config.main)
                .pipe(uglify())
                .pipe(header(config.header + nl, {
                    pkg  : pkg,
                    info : "v" + pkg.version,
                    year : new Date().getFullYear()
                }))
                .pipe(rename(function(path) {
                    path.extname = ".min" + path.extname;
                }))
                .pipe(gulp.dest("./"));
};

// build plugin files
pipes.buildPlugins = function() {
    return pipes.validateFiles(config.plugins)
                .pipe(data(function(file) {
                    var string = String(file.contents).split("\n")[1];
                    var matches = string.match(/\s-\s([a-z ]+)\s-\sv([0-9.]+)/i);

                    return {
                        info: "- " + matches[1] + " v" + matches[2]
                    };
                }))
                .pipe(uglify())
                .pipe(header(config.header + nl, {
                    pkg  : pkg,
                    year : new Date().getFullYear()
                }))
                .pipe(rename(function(path) {
                    path.extname = ".min" + path.extname;
                }))
                .pipe(gulp.dest("./"));
};

// concat plugin files 
pipes.concatPlugins = function() {
    return gulp.src(config.plugins)
               .pipe(concat(config.main.replace(".js", ".plugins.js"), {
                   sep: nl + nl
               }))
               .pipe(gulp.dest("./"))
               .pipe(uglify())
               .pipe(header(config.header + nl, {
                   pkg  : pkg,
                   info : "- All Plugins v" + pkg.version,
                   year : new Date().getFullYear()
               }))
               .pipe(rename(function(path) {
                   path.extname = ".min" + path.extname;
               }))
               .pipe(gulp.dest("./"));
};



/*
** TASKS
*/



// check & build everything
gulp.task("build", ["build-main", "build-plugins", "concat-plugins"]);

// check & build main project file
gulp.task("build-main", pipes.buildMain);

// check & build single plugin files
gulp.task("build-plugins", pipes.buildPlugins);

// build concatenated plugins file
gulp.task("concat-plugins", pipes.concatPlugins);

// check all files
gulp.task("validate", pipes.validate);

// check, build & watch live changes
gulp.task("watch", ["build"], function() {
    // watch main file
    gulp.watch(config.main, function() {
        var task = pipes.buildMain();
        util.log("updated", "'" + util.colors.red("main file") + "'");
        return task;
    });

    // watch plugins
    gulp.watch(config.plugins, function() {
        var task = pipes.buildPlugins();
        util.log("updated", "'" + util.colors.red("plugins") + "'");
        pipes.concatPlugins();
        util.log("updated", "'" + util.colors.red("concatenated plugins file") + "'");
        return task;
    });
});