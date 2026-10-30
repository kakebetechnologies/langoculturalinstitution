# Drawing helpers for the pitch document: the hand-held phone, the data flywheel, the "one platform, many editions" hub, and icons
# (the same Ionicons the app uses). Everything is plain inline SVG / text so the PDF stays sharp at any size.
import json
import math

GLYPHS = json.load(open('C:/xampp/htdocs/langoculturalinstitution/app/node_modules/@expo/vector-icons/build/vendor/react-native-vector-icons/glyphmaps/Ionicons.json'))

RED, RED_D, YELLOW, SKY, GREEN, AMBER, INK = '#EE1A26', '#C9111C', '#FDDD16', '#00ADF1', '#1E9E5A', '#E8A317', '#1B1E25'


def icon(name, size='1em', color='currentColor'):
    """An Ionicons glyph as inline text."""
    return f'<i class="ion" style="font-size:{size};color:{color}">&#{GLYPHS[name]};</i>'


def hand_phone(img, rot=-5, uid='a', width='100%', sleeve=RED):
    """A hand holding a phone whose screen shows `img` (a screenshot, 780x1688). Flat illustration, brown skin, red sleeve."""
    skin, shade, light = '#8A5530', '#6B4226', '#9C6740'
    return f'''
<svg class="hand" viewBox="52 14 424 790" width="{width}" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
  <defs>
    <clipPath id="scr{uid}"><rect x="139" y="38" width="240" height="520" rx="26"/></clipPath>
    <linearGradient id="skin{uid}" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="{light}"/><stop offset="1" stop-color="{shade}"/></linearGradient>
    <linearGradient id="glass{uid}" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="#fff" stop-opacity=".16"/><stop offset=".45" stop-color="#fff" stop-opacity="0"/></linearGradient>
    <filter id="sh{uid}" x="-30%" y="-30%" width="160%" height="160%"><feGaussianBlur stdDeviation="14"/></filter>
  </defs>
  <ellipse cx="270" cy="772" rx="190" ry="16" fill="#000" opacity=".28" filter="url(#sh{uid})"/>
  <g transform="rotate({rot} 260 420)">
    <!-- forearm and sleeve (behind everything) -->
    <path d="M168 560 C150 640 150 700 160 800 L368 800 C380 700 392 640 372 560 Z" fill="url(#skin{uid})"/>
    <path d="M150 700 L386 700 L408 800 L130 800 Z" fill="{sleeve}"/>
    <path d="M150 700 L386 700" stroke="#fff" stroke-opacity=".35" stroke-width="5"/>
    <!-- palm behind the phone -->
    <path d="M122 420 C112 500 130 560 170 600 L372 600 C410 560 420 500 404 420 Z" fill="url(#skin{uid})"/>
    <!-- four fingers wrapping the right edge -->
    <g fill="url(#skin{uid})">
      <rect x="352" y="318" width="86" height="31" rx="15.5" transform="rotate(-3 352 318)"/>
      <rect x="352" y="358" width="92" height="31" rx="15.5" transform="rotate(-2 352 358)"/>
      <rect x="352" y="398" width="88" height="31" rx="15.5" transform="rotate(0 352 398)"/>
      <rect x="352" y="438" width="74" height="30" rx="15" transform="rotate(2 352 438)"/>
    </g>
    <g stroke="{shade}" stroke-opacity=".45" stroke-width="2" fill="none" stroke-linecap="round">
      <path d="M418 326 q8 6 0 16"/><path d="M424 366 q8 6 0 16"/><path d="M420 406 q8 6 0 16"/><path d="M406 445 q8 6 0 16"/>
    </g>
    <!-- the phone -->
    <rect x="131" y="30" width="256" height="536" rx="36" fill="#14161B"/>
    <rect x="131" y="30" width="256" height="536" rx="36" fill="none" stroke="#3A3F4A" stroke-width="2"/>
    <rect x="387" y="150" width="4" height="62" rx="2" fill="#2B2F38"/>
    <image href="{img}" xlink:href="{img}" x="139" y="38" width="240" height="520" preserveAspectRatio="xMidYMin slice" clip-path="url(#scr{uid})"/>
    <rect x="139" y="38" width="240" height="520" rx="26" fill="url(#glass{uid})"/>
    <rect x="236" y="46" width="46" height="12" rx="6" fill="#0B0C0F"/>
    <!-- thumb over the left edge -->
    <g transform="translate(118 520) rotate(-58)">
      <rect x="-24" y="-18" width="150" height="36" rx="18" fill="url(#skin{uid})"/>
      <rect x="86" y="-11" width="34" height="22" rx="10" fill="#D8AE8E" opacity=".92"/>
      <path d="M40 -10 q10 10 0 20" stroke="{shade}" stroke-opacity=".4" stroke-width="2" fill="none" stroke-linecap="round"/>
    </g>
  </g>
</svg>'''


def flywheel(size=520):
    """Six steps in a circle: from a contribution on a phone to tools that come back to the community."""
    steps = [
        ('mic', 'Community contributes', 'Words, proverbs, stories, audio, photos', RED),
        ('shield-checkmark', 'Experts review', "The community's language experts check every entry", AMBER),
        ('key', 'Rights are recorded', 'Each contributor chooses how it may be used', SKY),
        ('layers', 'Datasets are built', 'Clean text and speech, owned by the institution', GREEN),
        ('sparkles', 'AI is trained', 'Translation, speech recognition, voices', RED),
        ('heart', 'Tools return home', 'Better learning, search and voice tools for all', AMBER),
    ]
    c, R, r = size / 2, size * 0.325, size * 0.074
    out = [f'<svg class="flywheel" viewBox="-150 -6 {size + 300} {size + 36}" xmlns="http://www.w3.org/2000/svg">']
    out.append(f'<defs><marker id="arr" viewBox="0 0 10 10" refX="7" refY="5" markerWidth="5.5" markerHeight="5.5" orient="auto-start-reverse"><path d="M0 0 L10 5 L0 10 z" fill="#fff"/></marker></defs>')
    out.append(f'<circle cx="{c}" cy="{c}" r="{R}" fill="none" stroke="#fff" stroke-opacity=".12" stroke-width="2" stroke-dasharray="3 7"/>')
    for i in range(6):
        a0 = math.radians(-90 + i * 60 + 17)
        a1 = math.radians(-90 + (i + 1) * 60 - 17)
        x0, y0, x1, y1 = c + R * math.cos(a0), c + R * math.sin(a0), c + R * math.cos(a1), c + R * math.sin(a1)
        out.append(f'<path d="M{x0:.1f} {y0:.1f} A{R} {R} 0 0 1 {x1:.1f} {y1:.1f}" fill="none" stroke="#fff" stroke-opacity=".7" stroke-width="2.6" marker-end="url(#arr)"/>')
    for i, (ic, title, text, col) in enumerate(steps):
        a = math.radians(-90 + i * 60)
        x, y = c + R * math.cos(a), c + R * math.sin(a)
        out.append(f'<circle cx="{x:.1f}" cy="{y:.1f}" r="{r}" fill="{col}"/><circle cx="{x:.1f}" cy="{y:.1f}" r="{r + 5}" fill="none" stroke="{col}" stroke-opacity=".4" stroke-width="2"/>')
        out.append(f'<text x="{x:.1f}" y="{y + r * 0.36:.1f}" text-anchor="middle" font-family="Ionicons" font-size="{r * 1.05:.1f}" fill="#fff">{chr(GLYPHS[ic])}</text>')
        # label outside the ring
        lx, ly = c + (R + r + 14) * math.cos(a), c + (R + r + 14) * math.sin(a)
        anchor = 'middle' if abs(math.cos(a)) < 0.3 else ('start' if math.cos(a) > 0 else 'end')
        dy = -18 if math.sin(a) < -0.5 else (14 if math.sin(a) > 0.5 else 0)
        ly += dy
        out.append(f'<text x="{lx:.1f}" y="{ly:.1f}" text-anchor="{anchor}" font-family="Clarity City, Inter" font-weight="700" font-size="14.5" fill="#fff">{i + 1}. {title}</text>')
        words, line, lines = text.split(), '', []
        for w in words:
            if len(line) + len(w) > 24 and line:
                lines.append(line); line = w
            else:
                line = (line + ' ' + w).strip()
        lines.append(line)
        for j, ln in enumerate(lines):
            out.append(f'<text x="{lx:.1f}" y="{ly + 16 + j * 13.5:.1f}" text-anchor="{anchor}" font-family="Clarity City, Inter" font-size="10.8" fill="#fff" fill-opacity=".78">{ln}</text>')
    out.append(f'<circle cx="{c}" cy="{c}" r="{R * 0.52}" fill="#fff" fill-opacity=".07"/><circle cx="{c}" cy="{c}" r="{R * 0.52}" fill="none" stroke="{YELLOW}" stroke-width="2"/>')
    out.append(f'<text x="{c}" y="{c - 6}" text-anchor="middle" font-family="Clarity City, Inter" font-weight="700" font-size="17" fill="{YELLOW}">Community-</text><text x="{c}" y="{c + 14}" text-anchor="middle" font-family="Clarity City, Inter" font-weight="700" font-size="17" fill="{YELLOW}">owned data</text>')
    out.append('</svg>')
    return ''.join(out)


def hub(size=470):
    """One shared platform in the middle, a ring of community editions around it."""
    nodes = [('Lango', 'Testing edition', RED), ('Buganda', '', None), ('Acholi', 'Prototype', AMBER), ('Teso', '', None), ('Ankole', '', None),
             ('Your community', 'Next edition', YELLOW), ('Busoga', '', None), ('Karamoja', '', None), ('Alur', '', None)]
    c, R = size / 2, size * 0.385
    out = [f'<svg class="hub" viewBox="0 0 {size} {size}" xmlns="http://www.w3.org/2000/svg">']
    out.append(f'<circle cx="{c}" cy="{c}" r="{R}" fill="none" stroke="#1B1E25" stroke-opacity=".12" stroke-width="1.6" stroke-dasharray="3 6"/>')
    n = len(nodes)
    for i, (name, tag, col) in enumerate(nodes):
        a = math.radians(-90 + i * 360 / n)
        x, y = c + R * math.cos(a), c + R * math.sin(a)
        out.append(f'<line x1="{c}" y1="{c}" x2="{x:.1f}" y2="{y:.1f}" stroke="{col or "#B9BEC8"}" stroke-opacity="{.7 if col else .45}" stroke-width="{2.4 if col else 1.4}"/>')
    out.append(f'<circle cx="{c}" cy="{c}" r="{size * 0.155}" fill="#14161B"/><circle cx="{c}" cy="{c}" r="{size * 0.155}" fill="none" stroke="{YELLOW}" stroke-width="3"/>')
    for k, (txt, dy, fs, fill) in enumerate([('One shared', -14, 14, '#fff'), ('platform', 3, 14, '#fff'), ('app · admin · API', 22, 9.8, YELLOW)]):
        out.append(f'<text x="{c}" y="{c + dy}" text-anchor="middle" font-family="Clarity City, Inter" font-weight="700" font-size="{fs}" fill="{fill}">{txt}</text>')
    for i, (name, tag, col) in enumerate(nodes):
        a = math.radians(-90 + i * 360 / n)
        x, y = c + R * math.cos(a), c + R * math.sin(a)
        w = max(74, len(name) * 9.4 + 26, len(tag) * 5.0 + 22)
        fill = col or '#fff'
        txt = '#fff' if col in (RED, AMBER) else INK
        stroke = col or '#C9CED8'
        out.append(f'<rect x="{x - w / 2:.1f}" y="{y - 18:.1f}" width="{w:.1f}" height="36" rx="18" fill="{fill}" stroke="{stroke}" stroke-width="1.6"/>')
        out.append(f'<text x="{x:.1f}" y="{y + (-2 if tag else 5):.1f}" text-anchor="middle" font-family="Clarity City, Inter" font-weight="700" font-size="14" fill="{txt}">{name}</text>')
        if tag:
            out.append(f'<text x="{x:.1f}" y="{y + 11:.1f}" text-anchor="middle" font-family="Clarity City, Inter" font-weight="600" font-size="8.6" fill="{txt}" fill-opacity=".9">{tag}</text>')
    out.append('</svg>')
    return ''.join(out)
