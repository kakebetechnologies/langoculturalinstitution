// Lists every English phrase the app shows in lib/strings.json.
// The app sends that list to the website once per version, so language experts can translate the phrases into Leb Lango in
// the admin panel (Admin → Translations). Run it after adding or changing text:  npm run strings
//
//  - the text inside t('...') calls is always included;
//  - files marked `// i18n:all` keep their text in data arrays (tiles, slides, option lists) rather than inline, so every
//    quoted string in them that reads like a phrase (starts with a capital, no code characters) is included too.
const fs = require('fs');
const path = require('path');

const ROOT = path.join(__dirname, '..');
const DIRS = ['app', 'components', 'lib', 'constants'];
const found = new Set();

function walk(dir) {
  for (const entry of fs.readdirSync(dir, { withFileTypes: true })) {
    const full = path.join(dir, entry.name);
    if (entry.isDirectory()) walk(full);
    else if (/\.(jsx?|tsx?)$/.test(entry.name)) scan(fs.readFileSync(full, 'utf8'));
  }
}

const unescape = (s) => s.replace(/\\(['"\\])/g, '$1').replace(/\\n/g, '\n');

function scan(source) {
  const quoted = /(['"])((?:\\.|(?!\1)[^\\\n])*)\1/g;
  let m;

  if (source.includes('// i18n:all')) {
    while ((m = quoted.exec(source))) {
      const text = unescape(m[2]);
      const phrase = /^[A-Z][^_/<>{}=@#$%^*|\\]*$/.test(text) && text.length >= 5 && !/\.(png|jpg|jsx?|json)$/.test(text) && (/\s/.test(text) || /^[A-Z][a-z]+$/.test(text));
      if (phrase) found.add(text);
    }
  }

  // t('Some phrase')  t("Some phrase")  — also inside {t('…')} and attributes.
  const call = /\bt\(\s*(['"])((?:\\.|(?!\1)[^\\\n])*)\1/g;
  while ((m = call.exec(source))) {
    const text = unescape(m[2]);
    if (text.trim()) found.add(text);
  }
}

for (const d of DIRS) if (fs.existsSync(path.join(ROOT, d))) walk(path.join(ROOT, d));
const list = [...found].sort((a, b) => a.localeCompare(b));
fs.writeFileSync(path.join(ROOT, 'lib', 'strings.json'), JSON.stringify(list, null, 1) + '\n');
console.log(`${list.length} phrases written to lib/strings.json`);
