"""Renders leaders.html to ../MyCulture-Introduction-for-Lango-Leaders.pdf with the Edge command line (puppeteer cannot launch this Edge).
   python build.py   then   python render.py"""
import os, subprocess, tempfile, time
HERE = os.path.dirname(os.path.abspath(__file__))
EDGE = r'C:\Program Files (x86)\Microsoft\Edge\Application\msedge.exe'
OUT = os.path.normpath(os.path.join(HERE, '..', 'MyCulture-Introduction-for-Lango-Leaders.pdf'))
if os.path.exists(OUT):
    os.remove(OUT)
url = 'file:///' + os.path.join(HERE, 'leaders.html').replace(os.sep, '/')
subprocess.run([EDGE, '--headless=new', '--disable-gpu', '--no-sandbox', '--user-data-dir=' + os.path.join(tempfile.gettempdir(), 'edge-leaders-pdf'), '--no-pdf-header-footer',
                '--virtual-time-budget=15000', '--print-to-pdf=' + OUT, url], check=True, capture_output=True, timeout=240)
last = -1
for _ in range(60):
    size = os.path.getsize(OUT) if os.path.exists(OUT) else -1
    if size > 0 and size == last:
        break
    last = size
    time.sleep(1)
print(OUT, round(os.path.getsize(OUT) / 1e6, 2), 'MB')
