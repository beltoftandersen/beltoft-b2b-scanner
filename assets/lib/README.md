# Vendored, not fetched

(The directory is `lib/` and not `vendor/` on purpose: the repository ignores
`**/vendor/`, so a folder by that name would have left this file untracked and
the iOS fallback missing from any fresh deploy - with nothing failing until
somebody opened the scanner on an iPhone.)

`zxing-browser.min.js` is @zxing/browser 0.1.5, copied here from npm and served
from this plugin. It is never loaded from a CDN: a scanner that stops working
because someone else's host is down, and that tells a third party which shop a
partner is standing in, is not worth the bandwidth saved.

It is also not loaded on every phone. The panel uses the browser's own
`BarcodeDetector` where there is one - current Android Chrome has it - and only
fetches this file when there is not, which in practice means iOS. Update it by
replacing the file and bumping B2B_SCANNER_VERSION.
