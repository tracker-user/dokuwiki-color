# color plugin for DokuWiki — local fork

Adds a `<color>` syntax for writing text with custom foreground and background colors:

```
<color red>red text</color>
<color blue/yellow>blue on yellow</color>
<color #FF0000>hex color</color>
<color /lightgrey>just a background</color>
<color rgb(80%,0%,0%):rgb(100%,80%,100%)>RGB function</color>
<color hsl(120,100%,30%):hsl(180,50%,90%)>HSL function</color>
```

Foreground or background can be any CSS color spec — the plugin doesn't validate against the spec, it just rejects characters that could break out of the `style` attribute (`"`, `'`, `<`, `>`, `&`, `;`).

Use `:` instead of `/` as the fg/bg separator if either color value itself contains a slash (e.g. CSS 4 alpha syntaxes like `hsl(120, 100%, 30% / 50%)`).

Original plugin: [github.com/hanche/dokuwiki_color_plugin](https://github.com/hanche/dokuwiki_color_plugin). This is a local fork tracking upstream `2022-10-19`.

## What changed in the local fork

| Change | Why |
| --- | --- |
| Added `public` visibility modifiers on every method in `syntax.php` | PSR-2/PSR-12 conformance; makes the API contract explicit. No behavioral change. |
| Dropped the trailing `?>` closing tag | PSR-12. Prevents stray whitespace after the tag from breaking header output. |
| Standardised on `[]` short array syntax | Consistency. PHP 7+ feature, has been required everywhere we run for years. |
| `script.js` wrapped in an IIFE with `'use strict'`; uses `toolbar.push()` instead of `toolbar[toolbar.length] = ...` | Stops leaking the `color_icobase` variable to global scope. Matches modern JS conventions. Same behavior — 13 swatch picker still appears in the toolbar. |
| `plugin.info.txt` `date` set to `2077-10-19` | Suppresses the **Update** button in the Extension Manager. Original day/month preserved, year bumped. See "Update suppression" below. |

No changes to `images/`, no changes to the on-disk filename layout, no changes to the wiki syntax. Existing pages render identically.

## Update suppression

`plugin.info.txt` has `date: 2077-10-19`. The Extension Manager's `isUpdateAvailable()` compares the installed date (as a string) against the upstream `lastupdate` from dokuwiki.org. With a year-2077 date, the comparison `"2077-10-19" < "<any-real-date>"` is always false, so the Update button never appears. This matches the convention used by our other forked plugins.

Note that this *only* hides the Update button. Reinstall (if upstream is found) and Uninstall are still shown — those require a deliberate click. If another admin needs to reinstall the upstream version, they can still do so explicitly; we're protecting against accidental updates, not deliberate reinstalls.

## Install

Drop the folder into `lib/plugins/color/`, or use Admin → Extension Manager → Manual Install to upload the zip.

## Compatibility

Tested on DokuWiki `2025-05-14b "Librarian"`. No interactions with our other table-related plugins (cellbg/sortablejs/searchtablejs/edittable) — the color plugin operates on inline text spans, while those operate on table cells / wrappers.

## License

GPL 2, matching the original plugin.
