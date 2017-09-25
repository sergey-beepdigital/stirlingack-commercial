# Crowd base build

- Uses Timber

## Now includes Crowd's gulpfile.js!

Crowd uses [Gulp](http://gulpjs.com/) for managing front-end build tooling and
workflow. Find out [more about
gulp](https://github.com/gulpjs/gulp/blob/master/docs/getting-started.md) and
the [plugins available for it](http://gulpjs.com/plugins/).

### Setup

1. Install NPM (Node).
2. Run `npm install -g gulp` to install gulp globally.
3. `cd` into your themes directory.
4. Run `npm install`.
5. Have a kitkat 🍫

### Updating Node and NPM

If you installed Node with homebrew then just run `brew upgrade node`. If you
used a binary installer then download the [latest version of
Node](https://nodejs.org/en/) from the website.

To update NPM run `npm install npm@latest -g`.

To update packages from old gulpfiles run `npm outdated` to see local packages
that are out of date. Then run `npm install packageName@latest` to bring that
package up to date.

### Crowd's standard tasks

`gulp` Will run and then watch sass, js, and images tasks.

`gulp modernizr` for generating a single distributable CSS file with inline
sourcemaps and a custom build of modernizr.

`gulp js` for piping JS from src to a distribution bundle. *Now with added
hinting!*

`gulp images` for piping source images in src to dist, and optimising thier
size.

`gulp fonts` will pipe source fonts to dist

`gulp package` will product a package in the directory above that contains
whitelisted files. This is used to generate a 'package' version of the theme
that can be deployed to developement environments.

#### NPM scripts

Npm scripts are also setup, if you want to use them.

`npm start` basically just runs `gulp` (the standard dev / watch task).

`npm run build` runs `gulp package`.

### Flags

`--production` will run tasks configured to output for production. This often
means output files will be compressed.

`gulp package --production` will run the packaging task while also compressing
outputted files. The generated package should be deployed to development
environments and live environments.
