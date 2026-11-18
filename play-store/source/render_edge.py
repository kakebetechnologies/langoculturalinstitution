"""Renders the Google Play graphics with the Edge command line (puppeteer cannot launch this Edge): python make_assets.py, then python render_edge.py"""
import os
import subprocess
import tempfile
import time
from PIL import Image

HERE = os.path.dirname(os.path.abspath(__file__))
OUT = os.path.normpath(os.path.join(HERE, '..'))
EDGE = r'C:\Program Files (x86)\Microsoft\Edge\Application\msedge.exe'
PROFILE = os.path.join(tempfile.gettempdir(), 'edge-play-profile')


def shot(html, png, w, h):
    url = 'file:///' + html.replace(os.sep, '/')
    raw = png + '.raw.png'   # Edge can finish writing its file after the command returns, so it is cropped into the real file only once it has stopped growing
    if os.path.exists(raw):
        os.remove(raw)
    subprocess.run([EDGE, '--headless=new', '--disable-gpu', '--no-sandbox', '--hide-scrollbars', '--user-data-dir=' + PROFILE, '--virtual-time-budget=8000',
                    '--window-size=%d,%d' % (w, h + 140), '--screenshot=' + raw, url], check=True, capture_output=True, timeout=120)
    last = -1
    for _ in range(40):
        size = os.path.getsize(raw) if os.path.exists(raw) else -1
        if size > 0 and size == last:
            break
        last = size
        time.sleep(1)
    Image.open(raw).convert('RGB').crop((0, 0, w, h)).save(png, optimize=True)
    os.remove(raw)


os.makedirs(os.path.join(OUT, 'screenshots'), exist_ok=True)
tmp = os.path.join(HERE, '_one.html')
src = open(os.path.join(HERE, 'screens.html'), encoding='utf-8').read()
for i in range(1, 9):
    page = src.replace('</style>', '.shot{margin:0 !important}body{margin:0;background:#fff}.shot:not(#s%d){display:none}</style>' % i, 1)
    open(tmp, 'w', encoding='utf-8').write(page)
    shot(tmp, os.path.join(OUT, 'screenshots', 'phone-%02d.png' % i), 1080, 1920)
feat = open(os.path.join(HERE, 'feature.html'), encoding='utf-8').read().replace('</style>', 'body{margin:0}.feature{margin:0 !important}</style>', 1)
open(tmp, 'w', encoding='utf-8').write(feat)
shot(tmp, os.path.join(OUT, 'feature-graphic-1024x500.png'), 1024, 500)
os.remove(tmp)
print('rendered 8 screenshots and the feature graphic')
