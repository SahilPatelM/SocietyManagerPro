const fs = require('fs');
const path = require('path');

const publicDir = path.join(__dirname, '..', 'public');

fs.mkdirSync(publicDir, { recursive: true });
fs.writeFileSync(path.join(publicDir, '.vercel-build'), 'ok');
