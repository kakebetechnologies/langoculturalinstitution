"""Adds the document properties to the rendered proposal PDF and writes the final file. Usage: finish.py in.pdf out.pdf"""
import os
import sys

from pypdf import PdfReader, PdfWriter

src, out = sys.argv[1], sys.argv[2]
w = PdfWriter(clone_from=PdfReader(src))
w.add_metadata({
    '/Title': 'Proposal: Digital Platform for the Lango Cultural Foundation (Website, Admin Control Panel and Mobile App)',
    '/Author': 'Kakebe Technologies Limited',
    '/Subject': 'Proposal for a website, admin control panel and mobile app. Draft for discussion.',
    '/Keywords': 'Lango Cultural Foundation, proposal, website, mobile app, admin panel, Kakebe Technologies, Lira City',
    '/Creator': 'Kakebe Technologies Limited',
    '/Producer': 'Kakebe Technologies Limited',
})
with open(out, 'wb') as f:
    w.write(f)
print('written', out, len(w.pages), 'pages')
