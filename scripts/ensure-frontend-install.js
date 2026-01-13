const { existsSync } = require('fs');
const { spawnSync } = require('child_process');
const path = require('path');

const nodeModulesPath = path.join(__dirname, '..', 'frontend', 'node_modules');

if (existsSync(nodeModulesPath)) {
  process.exit(0);
}

const result = spawnSync('npm', ['--prefix', 'frontend', 'install'], {
  stdio: 'inherit',
  shell: process.platform === 'win32',
});

if (result.error) {
  console.error(result.error);
  process.exit(result.status ?? 1);
}

process.exit(result.status ?? 0);
