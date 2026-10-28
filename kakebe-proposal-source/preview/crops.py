# Cuts the screenshot library (shots/) into the pieces used by build3.js -> img3/. Run: python crops.py
from PIL import Image
import os
S, O = 'shots/', 'img3/'
os.makedirs(O, exist_ok=True)

def crop(src, name, y0, y1, x0=0, x1=1440, outw=1100, q=82):
    im = Image.open(S + src + '.jpg').convert('RGB')
    im = im.crop((x0, y0, x1, min(y1, im.height)))
    h = round(im.height * outw / im.width)
    im.resize((outw, h), Image.LANCZOS).save(O + name + '.jpg', quality=q, optimize=True)
    print(name, im.width, im.height)

# website (1440 wide pages)
crop('site-home', 'w-home-hero', 0, 790)
crop('site-home', 'w-home-lang', 1950, 2620)
crop('site-home', 'w-home-contrib', 5300, 5900)
for src, name in [('site-history', 'w-history'), ('site-districts', 'w-districts'), ('site-clans', 'w-clans'), ('site-heritage', 'w-heritage'),
                  ('site-dictionary', 'w-dictionary'), ('site-translator', 'w-translator'), ('site-assistant', 'w-assistant'), ('site-contribute', 'w-contribute')]:
    crop(src, name, 0, 1000, outw=900)
for src, name in [('site-events', 'w-events'), ('site-donate', 'w-donate'), ('site-transparency', 'w-transparency')]:
    crop(src, name, 0, 900, outw=760)
crop('site-heritage-item', 'w-heritage-item', 0, 760, outw=900)

# admin (1440 x 1440 screens)
crop('admin-dashboard', 'a-dashboard', 0, 1140, outw=1300)
crop('admin-contributions', 'a-list', 0, 470, outw=1200)
crop('admin-contribution-review', 'a-review', 0, 930, outw=1200)
crop('admin-contribution-review', 'a-review-s', 0, 780, outw=900)
crop('admin-dictionary', 'a-dict', 0, 420, x0=290, outw=1100)
crop('admin-translations', 'a-trans', 0, 420, x0=290, outw=1100)
crop('admin-clan-quick', 'a-clan', 0, 420, x0=290, outw=1100)

# phones (780 x 1688)
for n in ['app-onboarding-1', 'app-home', 'app-explore', 'app-learn', 'app-dictionary', 'app-translate', 'app-clans', 'app-events', 
          'app-in-contribute', 'app-in-form-oral', 'app-in-form-word', 'app-in-me']:
    im = Image.open(S + n + '.jpg').convert('RGB')
    im.resize((640, round(im.height * 640 / im.width)), Image.LANCZOS).save(O + n + '.jpg', quality=84, optimize=True)
    print(n)
