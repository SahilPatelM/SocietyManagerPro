const fs = require('fs');
const path = require('path');
const { execSync } = require('child_process');

const root = path.join(__dirname, '..');
const publicDir = path.join(root, 'public');

fs.mkdirSync(publicDir, { recursive: true });
fs.writeFileSync(path.join(publicDir, '.vercel-build'), 'ok');

try {
    execSync('php artisan livewire:publish --assets --no-interaction', {
        cwd: root,
        stdio: 'inherit',
    });
} catch (error) {
    console.warn('[vercel-postbuild] Could not publish Livewire assets via artisan; using committed public/vendor/livewire if present.');
}
