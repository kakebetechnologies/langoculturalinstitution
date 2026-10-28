# Kakebe proposal: Digital Platform for the Lango Cultural Foundation

Source of `../Kakebe-Proposal-Lango-Cultural-Foundation.pdf` (**edition 2.0**, A4, 17 pages, light design modelled on the CSAM concept note, two-month delivery, no pricing: the Financial Proposal is a separate file; screenshots and mockups are also a separate file). The editable master of the same text is the Claude Doc
"Proposal: Digital Platform for the Lango Cultural Foundation" (https://claude.ai/artifact/BpEtC3JJkjjGb8DwJGWgzS). Edit the text in either place; `edition2/` rebuilds the PDF.

## Rebuild the PDF

Needs Node 20+ with `puppeteer-core` (this folder's `package.json` has it), and Microsoft Edge (path is set in `render2.js`, line 13).
The fonts (Montserrat, Inter) load from Google Fonts, so an internet connection is needed.

```bash
npm install            # once, in kakebe-proposal-source
cd edition2
node render2.js        # figs2.js -> build2.js -> proposal2.html -> proposal2.pdf, and prints the spare space on each page
```

* **Text:** `edition2/content2.js` holds all 15 content pages (pages 3 to 17) as block lists: `p`, `sub`, `theme`, `checks`, `key`, `chips`, `tiles`, `table`, `fig`, `team`, `steps`, `contact`. Contact details are the `CONTACT` constant at the top.
* **Layout:** every page is a fixed A4 section with its own footer, so page count = number of sections. `render2.js` prints how much room is left on each page; add text only where it shows spare space. The contents page numbers are static, in `build2.js`.
* **Design:** `style2.css` (navy `#164574`, blue `#1F5FA8`, red `#E01E26`; Montserrat headings, Inter 11 pt body). The cover and contents page are built in `build2.js`.
* **Figures:** `figs2.js` draws `figures/fig-platform2.svg` and `figures/fig-road.svg`. Photos and logos are in `img2/`.

## Other folders

* `content17/` is the markdown source of the superseded 17-page v1.1 (three-month plan, black cover).
* `archive/` holds the superseded PDFs (85-page full edition, 17-page v1.1) and the long-edition sources.

## Platform Preview (screenshots and mockups)

`../Kakebe-Platform-Preview-Lango-Cultural-Foundation.pdf` is the separate companion file (A4, 13 pages) with the screenshots and device mockups, kept out of the proposal on purpose. It shows the restyled build as of 28 October 2026, with starter and sample content (an earlier set from 21 October showed the previous design and was replaced).

```bash
cd preview
node render3.js        # build3.js -> preview.html -> preview.pdf, and prints the spare space on each page
```

* **Layout and text:** `preview/build3.js` (page list, captions, numbered dots on the dashboard; the dot positions are percentages of the dashboard crop, so re-check them if the dashboard changes) and `preview/preview.css` on top of `style2.css`.
* **Images:** `preview/img3/` holds the cropped pieces used by the layout. `python crops.py` rebuilds them from the screenshots in `preview/shots/` (only the ones used are kept here).
* **Recapturing after a redesign** (all against the local XAMPP site, one browser at a time, Edge via puppeteer-core):
  1. `npm run pwa` in `app/` exports the web app into `web/pwa` (git-ignored); the phone shots are taken from there.
  2. `php admin_user.php create` (a script that makes a throw-away admin on the local database) and put the credentials it writes in `preview/capture/creds.json`; `ONLY=dashboard node capture.js admin` limits it to some screens.
  3. `node seed_member.js` registers a throw-away member and three clearly marked "Sample:" contributions through the API (writes `member.json`), needed for the review screen and the signed-in app screens.
  4. `node capture/capture.js site`, `node capture/capture.js admin`, `node capture/capture_app.js` and `node capture_member.js` (copy the scripts next to `shots/` first; they write to `./shots`).
  5. Copy the images into `preview/shots/`, run `python crops.py`, then `node render3.js`.
  6. Clean up: delete the throw-away member, admin, their contributions, notifications, tokens, audit rows and `web/uploads/<year>` files from the local database so it is left as found.
