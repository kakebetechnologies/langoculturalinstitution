"""Builds briefing.html from briefing.template.html: converts the screenshots to JPEG, draws the phone and browser frames, the icons and the constellation.
   python build.py <folder with the screenshots (PNG)>"""
import os, re, sys
from PIL import Image

HERE = os.path.dirname(os.path.abspath(__file__))
SRC = sys.argv[1] if len(sys.argv) > 1 else os.path.join(HERE, 'shots-src')
SHOTS = os.path.join(HERE, 'shots')
os.makedirs(SHOTS, exist_ok=True)

PHONE_SCREENS = ['welcome', 'home', 'dictionary', 'learn', 'clan', 'heritage', 'quizResult', 'contribute', 'events']
BROWSER_SCREENS = ['site_home']

for n in PHONE_SCREENS:
    im = Image.open(os.path.join(SRC, n + '.png')).convert('RGB')
    im = im.resize((780, round(780 * im.height / im.width)), Image.LANCZOS)
    im.save(os.path.join(SHOTS, n + '.jpg'), quality=88, optimize=True)
for n in BROWSER_SCREENS:
    im = Image.open(os.path.join(SRC, n + '.png')).convert('RGB')
    im = im.resize((1700, round(1700 * im.height / im.width)), Image.LANCZOS)
    im.save(os.path.join(SHOTS, n + '.jpg'), quality=86, optimize=True)

# the app's logo mark for the cover
logo = os.path.join(HERE, '..', 'app', 'assets', 'logo-mark.png')
art = os.path.join(HERE, 'art')
if os.path.isfile(logo):
    Image.open(logo).convert('RGBA').save(os.path.join(art, 'logo-mark.png'))

def stroke(inner):
    return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">' + inner + '</svg>'

ICONS = {
    'book': stroke('<path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/>'),
    'users': stroke('<path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>'),
    'headphones': stroke('<path d="M3 18v-6a9 9 0 0 1 18 0v6"/><path d="M21 19a2 2 0 0 1-2 2h-1a2 2 0 0 1-2-2v-3a2 2 0 0 1 2-2h3zM3 19a2 2 0 0 0 2 2h1a2 2 0 0 0 2-2v-3a2 2 0 0 0-2-2H3z"/>'),
    'camera': stroke('<path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/><circle cx="12" cy="13" r="4"/>'),
    'shield': stroke('<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><path d="m9 12 2 2 4-4"/>'),
    'globe': stroke('<circle cx="12" cy="12" r="10"/><path d="M2 12h20"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/>'),
    'grad': stroke('<path d="M22 10 12 5 2 10l10 5 10-5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/>'),
    'database': stroke('<ellipse cx="12" cy="5" rx="9" ry="3"/><path d="M21 12c0 1.7-4 3-9 3s-9-1.3-9-3"/><path d="M3 5v14c0 1.7 4 3 9 3s9-1.3 9-3V5"/>'),
    'pin': stroke('<path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/>'),
    'phone': stroke('<rect x="5" y="2" width="14" height="20" rx="2.5"/><path d="M12 18h.01"/>'),
    'heart': stroke('<path d="M20.8 4.6a5.5 5.5 0 0 0-7.8 0L12 5.7l-1-1.1a5.5 5.5 0 0 0-7.8 7.8l1 1.1L12 21.2l7.8-7.7 1-1.1a5.5 5.5 0 0 0 0-7.8z"/>'),
    'arrow': stroke('<path d="M5 12h14"/><path d="m12 5 7 7-7 7"/>'),
    'message': stroke('<path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>'),
    'mic': stroke('<rect x="9" y="2" width="6" height="12" rx="3"/><path d="M19 10v1a7 7 0 0 1-14 0v-1"/><path d="M12 18v4"/>'),
    'check': stroke('<circle cx="12" cy="12" r="10"/><path d="m8.5 12 2.5 2.5 4.5-5"/>'),
    'award': stroke('<circle cx="12" cy="8" r="6"/><path d="M15.5 13.5 17 22l-5-3-5 3 1.5-8.5"/>'),
}

STATUS = ('<div class="sb"><span>9:41</span><span class="r">'
          '<svg viewBox="0 0 18 12"><rect x="0" y="8" width="3" height="4" rx=".6"/><rect x="5" y="5.5" width="3" height="6.5" rx=".6"/><rect x="10" y="3" width="3" height="9" rx=".6"/><rect x="15" y="0" width="3" height="12" rx=".6"/></svg>'
          '<svg viewBox="0 0 16 12"><path d="M8 11.4 5.7 9a3.3 3.3 0 0 1 4.6 0zM3.7 6.9a6.3 6.3 0 0 1 8.6 0l-1.3 1.4a4.4 4.4 0 0 0-6 0zM1.2 4.3a9.8 9.8 0 0 1 13.6 0l-1.3 1.4a7.9 7.9 0 0 0-11 0z"/></svg>'
          '<svg viewBox="0 0 27 12"><rect x=".5" y=".5" width="22" height="11" rx="3.2" fill="none" stroke="currentColor" opacity=".45"/><rect x="2" y="2" width="19" height="8" rx="2" /><rect x="24" y="4" width="2" height="4" rx="1" opacity=".45"/></svg>'
          '</span></div>')


def phone(name, width, mode):
    cls = 'phone dark' if mode == 'dark' else 'phone'
    bar = '' if mode == 'nobar' else STATUS
    return ('<div class="%s" style="--w:%smm"><div class="phone__s"><img src="shots/%s.jpg" alt="">%s<span class="isl"></span><span class="hi"></span></div></div>'
            % (cls, width, name, bar))


def browser(name, width, url):
    return ('<div class="browser" style="--w:%smm"><div class="browser__bar"><i></i><i></i><i></i><span>%s</span></div><img src="shots/%s.jpg" alt=""></div>'
            % (width, url, name))


def hub():
    import math
    cx = cy = 200
    cultures = [('Lango', True), ('Acholi', False), ('Buganda', False), ('Ankole', False), ('Busoga', False), ('Teso', False), ('Karamoja', False), ('Bunyoro', False)]
    cols = ['#ee1a26', '#fddd16', '#00adf1', '#1e9e5a', '#fddd16', '#00adf1', '#ee1a26', '#1e9e5a']
    s = ['<svg viewBox="0 0 400 400" xmlns="http://www.w3.org/2000/svg" font-family="Clarity City, sans-serif">']
    s.append('<circle cx="200" cy="200" r="168" fill="none" stroke="#e8e4da" stroke-width="1.5" stroke-dasharray="3 6"/>')
    s.append('<circle cx="200" cy="200" r="110" fill="#fff" stroke="#f0ece0" stroke-width="1.5"/>')
    nodes = []
    for i, (name, live) in enumerate(cultures):
        a = -math.pi / 2 + i * 2 * math.pi / len(cultures)
        x, y = cx + 150 * math.cos(a), cy + 150 * math.sin(a)
        nodes.append((name, live, x, y, cols[i]))
        s.append('<line x1="%d" y1="%d" x2="%.1f" y2="%.1f" stroke="%s" stroke-width="%s" %s opacity="%s"/>' % (
            cx, cy, x, y, '#ee1a26' if live else '#cfc9b8', 3 if live else 2, '' if live else 'stroke-dasharray="4 5"', 1 if live else .9))
    s.append('<circle cx="200" cy="200" r="52" fill="#16181d"/>')
    s.append('<text x="200" y="196" text-anchor="middle" fill="#fddd16" font-weight="800" font-size="15" letter-spacing="1.5">UGANDA</text>')
    s.append('<text x="200" y="213" text-anchor="middle" fill="#fff" font-weight="700" font-size="9.5" opacity=".85">National hub</text>')
    for name, live, x, y, col in nodes:
        r = 37 if live else 30
        if live:
            s.append('<circle cx="%.1f" cy="%.1f" r="%d" fill="#ee1a26"/>' % (x, y, r))
            s.append('<text x="%.1f" y="%.1f" text-anchor="middle" fill="#fff" font-weight="800" font-size="13">%s</text>' % (x, y - 1, name))
            s.append('<text x="%.1f" y="%.1f" text-anchor="middle" fill="#ffe9a0" font-weight="800" font-size="7" letter-spacing=".8">READY NOW</text>' % (x, y + 11))
        else:
            s.append('<circle cx="%.1f" cy="%.1f" r="%d" fill="#fff" stroke="%s" stroke-width="2.4" stroke-dasharray="5 4"/>' % (x, y, r, col))
            s.append('<text x="%.1f" y="%.1f" text-anchor="middle" fill="#16181d" font-weight="700" font-size="10.5">%s</text>' % (x, y + 4, name))
    s.append('<text x="200" y="392" text-anchor="middle" fill="#7b8390" font-weight="600" font-size="9.5">Examples of cultures that could follow, each with its own editors</text>')
    s.append('</svg>')
    return ''.join(s)


html = open(os.path.join(HERE, 'briefing.template.html'), encoding='utf-8').read()
html = re.sub(r'\{\{PHONE:(\w+)\|(\d+(?:\.\d+)?)\|(\w*)\}\}', lambda m: phone(m.group(1), m.group(2), m.group(3)), html)
html = re.sub(r'\{\{BROWSER:(\w+)\|(\d+(?:\.\d+)?)\|([^}]*)\}\}', lambda m: browser(m.group(1), m.group(2), m.group(3)), html)
html = re.sub(r'\{\{ICON:(\w+)\}\}', lambda m: ICONS[m.group(1)], html)
html = html.replace('{{HUB}}', hub())
left = re.findall(r'\{\{[^}]*\}\}', html)
if left:
    raise SystemExit('unfilled placeholders: %s' % left)
open(os.path.join(HERE, 'briefing.html'), 'w', encoding='utf-8').write(html)
print('briefing.html written')
