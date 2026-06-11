# TODO - Fix refused to connect when navigating home/comments

- [ ] Inspect `js/tool.js` around `scrollToComment()` logic for URL handling/proxy navigation.
- [x] Update navigation so iframe always loads via `proxy.php?url=`.
- [x] Ensure `SITE_URL` is set to the real (non-proxy) URL extracted from comment data.

- [ ] Make URL comparisons in `renderPins()`/`renderSidebar()` resilient to proxy vs real URLs.
- [ ] Sanity test workflow: Home -> Features -> All Pages -> click Home comment.

