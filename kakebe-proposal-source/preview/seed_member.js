// Seeds the LOCAL dev site with one throw-away member and three clearly marked sample contributions (for the admin and app screenshots).
// Writes member.json (token) next to this file. Removal: cleanup_db.sql (see the report). Prints no credentials.
const fs = require('fs');
const path = require('path');
const crypto = require('crypto');

const API = 'http://localhost/langoculturalinstitution/api/v1';
const PHOTO = 'C:/xampp/htdocs/langoculturalinstitution/web/assets/img/photos/hero-site.jpg';
const EMAIL = 'preview-member@example.test';

(async () => {
  const opts = (await (await fetch(API + '/clans/options')).json()).data;
  const clan = opts.find((c) => c.name === 'Otengoro Olang') || opts[0];
  const password = 'Pv-' + crypto.randomBytes(9).toString('base64url');
  const reg = await (await fetch(API + '/auth/register', { method: 'POST', headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ name: 'Sample Member', email: EMAIL, password, clan_id: clan.id }) })).json();
  if (!reg.token) throw new Error('register failed: ' + JSON.stringify(reg).slice(0, 300));
  fs.writeFileSync(path.join(__dirname, 'member.json'), JSON.stringify({ token: reg.token, email: EMAIL }));

  const post = async (fields, file) => {
    const fd = new FormData();
    for (const [k, v] of Object.entries(fields)) fd.append(k, v);
    if (file) fd.append('media_image', new Blob([fs.readFileSync(file)], { type: 'image/jpeg' }), 'sample.jpg');
    const r = await fetch(API + '/contributions', { method: 'POST', headers: { Authorization: 'Bearer ' + reg.token }, body: fd });
    const j = await r.json();
    console.log(r.status, j.code || JSON.stringify(j).slice(0, 200));
  };
  const common = { clan_id: String(clan.id), rights: 'keep' };
  await post({ ...common, type: 'natural', title: 'Sample: grey crowned crane nesting site', client_ref: 'pv-' + crypto.randomUUID(),
    description: 'Illustrative sample submission. A pair of cranes seen nesting at the edge of a wetland, with two photographs attached.',
    lat: '2.2499', lng: '32.8996', location_text: 'Lira City', observed_on: '2026-10-21' }, PHOTO);
  await post({ ...common, type: 'oral_history', title: 'Sample: elder recounts a clan gathering', client_ref: 'pv-' + crypto.randomUUID(),
    description: 'Illustrative sample submission. An elder describes how the clan gathered, in his own words.', location_text: 'Lira' });
  await post({ ...common, type: 'heritage_site', title: 'Sample: old palace site photographs', client_ref: 'pv-' + crypto.randomUUID(),
    description: 'Illustrative sample submission. Photographs of a palace site and the place it stands.', lat: '2.2490', lng: '32.9010', location_text: 'Lira City' });
})().catch((e) => { console.error(e); process.exit(1); });
