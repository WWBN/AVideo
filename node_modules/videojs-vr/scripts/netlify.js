const shell = require('shelljs');
const path = require('path');
const BASE_DIR = path.join(__dirname, '..');
const DEPLOY_DIR = path.join(BASE_DIR, 'deploy');

shell.mkdir('-p', path.join(DEPLOY_DIR, 'node_modules'));

['dist', 'index.html', 'samples', 'examples'].forEach(function(f) {
  shell.cp('-R', path.join(BASE_DIR, f), DEPLOY_DIR);
});
shell.cp('-R', path.join(BASE_DIR, 'node_modules', 'video.js'), path.join(DEPLOY_DIR, 'node_modules'));
shell.cp('-R', path.join(BASE_DIR, 'node_modules', 'omnitone'), path.join(DEPLOY_DIR, 'node_modules'));

