const fs = require('fs');
const path = require('path');
const root = path.resolve(__dirname, '..');

const files = {
  vendorCss: [
    'public/digiboard/assets/vendor/css/all.min.css',
    'public/digiboard/assets/vendor/css/OverlayScrollbars.min.css',
    'public/digiboard/assets/vendor/css/bootstrap.min.css',
  ],
  coreCss: [
    'public/digiboard/assets/css/style.css',
    'public/digiboard/assets/css/blue-color.css',
    'public/digiboard/assets/css/custom-overrides.css',
  ],
  vendorJs: [
    'public/digiboard/assets/vendor/js/jquery-3.6.0.min.js',
    'public/digiboard/assets/vendor/js/jquery.overlayScrollbars.min.js',
    'public/digiboard/assets/vendor/js/bootstrap.bundle.min.js',
  ],
  coreJs: [
    'public/digiboard/assets/js/main.js',
  ],
};

function readAll(paths) {
  return paths.map((filePath) => fs.readFileSync(path.join(root, filePath), 'utf8')).join('\n');
}

function minifyCss(contents) {
  return contents
    .replace(/\/\*[\s\S]*?\*\//g, '')
    .replace(/\s+/g, ' ')
    .replace(/\s*([{}:;,])\s*/g, '$1')
    .replace(/;}/g, '}')
    .trim();
}

function minifyJs(contents) {
  const withoutBlockComments = contents.replace(/\/\*[\s\S]*?\*\//g, '');

  return withoutBlockComments
    .split('\n')
    .map((line) => line.trim())
    .filter((line) => line !== '' && !line.startsWith('//'))
    .join('\n');
}

function ensureDir(filePath) {
  const dir = path.dirname(filePath);
  fs.mkdirSync(dir, { recursive: true });
}

function writeFile(targetPath, contents) {
  const fullPath = path.join(root, targetPath);
  ensureDir(fullPath);
  fs.writeFileSync(fullPath, contents);
}

async function build() {
  const vendorCss = readAll(files.vendorCss);
  const coreCss = readAll(files.coreCss);

  writeFile('public/digiboard/assets/vendor/css/admin-vendor.min.css', minifyCss(vendorCss));
  writeFile('public/digiboard/assets/css/admin-core.min.css', minifyCss(coreCss));

  const vendorJs = readAll(files.vendorJs);
  const coreJs = readAll(files.coreJs);

  writeFile('public/digiboard/assets/vendor/js/admin-vendor.min.js', minifyJs(vendorJs));
  writeFile('public/digiboard/assets/js/admin-core.min.js', minifyJs(coreJs));

  console.log('Admin assets bundled and minified.');
}

build().catch((error) => {
  console.error(error);
  process.exit(1);
});
