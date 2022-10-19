# color Plugin

This is a plugin for [dokuwiki](https://www.dokuwiki.org/start)..

See the plugin  [homepage](https://www.dokuwiki.org/plugin:color) for detailed documentation. Here we provide a brief summary of the required syntax:

```
<color ⟨fg color⟩[/⟨bg color⟩[/⟨ignored text⟩]]>⟨text⟩</color>
<color ⟨fg color⟩:⟨bg color⟩[:⟨ignored text⟩]>⟨text⟩</color>
```

- Square brackets indicates optional parts.

- `⟨fg color⟩` and `⟨bg color⟩` are CSS color specifications. Either one may be empty, in which case it is ignored.

- In the first syntax, the color specification(s) MUST NOT include a slash (`/`).
- In the second syntax, the color specification(s) MUST NOT include a colon character (`:`). We do not know any legal CSS color specification that does include a colon, nor do we anticipate that one will appear in the future.
- The resulting HTML is a `<span>` element containing `⟨text⟩`, colorized with the given foreground and background colors.
