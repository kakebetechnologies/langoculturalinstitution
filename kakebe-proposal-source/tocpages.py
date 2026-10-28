"""Finds the PDF page (1-based) on which each contents entry starts, by searching the page text after the contents pages.

Usage: tocpages.py in.pdf toc-keys.json toc.json
"""
import json
import re
import sys
import os

from pypdf import PdfReader

src, keys_file, out_file = sys.argv[1:4]
keys = json.load(open(keys_file, encoding='utf-8'))
reader = PdfReader(src)


def norm(s):
    return re.sub(r'\s+', ' ', s.replace(' ', ' ')).strip().lower()


pages = [norm(p.extract_text() or '') for p in reader.pages]
# the contents pages hold every key; start searching after the last page that contains the first chapter title AND many dotted leaders
first = 0
for i, t in enumerate(pages):
    if 'contents' in t[:400] and 'executive summary' in t:
        first = i
# the last contents page is the last one before the page where "chapter 1" appears
start = next((i for i, t in enumerate(pages) if 'chapter 1' in t[:200]), first + 1)

result = {}
cursor = start
for key in keys:
    k = norm(re.sub(r'^Appendix [A-Z]\. ', '', key)) if key.startswith('Appendix') else norm(key)
    k = re.sub(r'^\d+\. ', '', k) if re.match(r'^\d+\. ', k) and not re.match(r'^\d+\.\d+', k) else k
    found = None
    for i in range(cursor, len(pages)):
        if k in pages[i]:
            found = i
            break
    if found is None:
        # try a shorter prefix (long titles wrap)
        short = k[:28]
        for i in range(cursor, len(pages)):
            if short in pages[i]:
                found = i
                break
    if found is not None:
        result[key] = found + 1
        cursor = found
    else:
        print('not found:', key)
json.dump(result, open(out_file, 'w', encoding='utf-8'), ensure_ascii=False, indent=1)
print('toc pages:', len(result), 'of', len(keys), '; first body page', start + 1, '; total pages', len(reader.pages))
