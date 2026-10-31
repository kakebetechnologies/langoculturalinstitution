// Bible.com's reader for one chapter of the Lango Bible. It opens at the text itself (with its own book and chapter menu), not at the page that offers to download
// the app. `version` is the edition number the server gives for each link (GET /api/v1/bible); `code` is the book code (GEN, EXO, MAT ...).
export const readerUrl = (version, code, chapter) => `https://www.bible.com/bible/${Number(version)}/${String(code).toUpperCase()}.${Math.max(1, Number(chapter) || 1)}`;
