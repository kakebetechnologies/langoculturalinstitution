"""Crops the demonstrator screenshots used in the proposal into img/: browser windows (top 1440x960) and phone screens."""
import os
import re
import glob
import sys

from PIL import Image

HERE = os.path.dirname(os.path.abspath(__file__))
SHOTS = os.path.join(HERE, 'shots')   # only needed to re-crop: put the demonstrator screenshots here (img/ already holds the crops)
OUT = os.path.join(HERE, 'img')
os.makedirs(OUT, exist_ok=True)

used = set()
for f in glob.glob(os.path.join(HERE, 'content', '*.md')):
    for m in re.finditer(r'!\[(browser|phone)\|[^\]]*\]\(shots/([a-z0-9-]+)\.jpg\)', open(f, encoding='utf-8').read()):
        used.add((m.group(1), m.group(2)))

for kind, name in sorted(used):
    im = Image.open(os.path.join(SHOTS, name + '.jpg')).convert('RGB')
    if kind == 'browser':
        h = min(im.size[1], 960)
        im = im.crop((0, 0, im.size[0], h))
        im = im.resize((1200, round(1200 * h / im.size[0])), Image.LANCZOS)
        im.save(os.path.join(OUT, name + '.jpg'), quality=84, optimize=True)
    else:
        im = im.resize((520, round(520 * im.size[1] / im.size[0])), Image.LANCZOS)
        im.save(os.path.join(OUT, name + '.jpg'), quality=86, optimize=True)
print(len(used), 'images written to', OUT)
