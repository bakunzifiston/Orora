# OroraFarm Documentation Source

Editable sources for the OroraFarm manuals.

| Folder | Content |
|---|---|
| `end-user/` | End-User Guide chapters (Markdown) and `shots-wanted.txt` |
| `admin/` | Administrator Guide chapters |
| `technical/` | Technical Documentation chapters (diagrams are Mermaid code blocks) |
| `screenshots/` | PNG screenshots captured from the running app (demo workspace) |
| `assets/` | Logo and cover images |
| `build/` | `build.mjs` (Markdown to PDF), `style.css`, `shots.mjs` (screenshot capture), `annotations.json` |
| `CONVENTIONS.md` | Writing conventions |

Output PDFs are written to `../output/` and copied to `resources/documentation/` in the app (used by the in-app Help Center), together with `manifest.json` (version, date, pages).

## Rebuild

```bash
cd build
npm install
node build.mjs all            # or: end-user | admin | technical | complete
DOC_VERSION=1.1 node build.mjs all
```

Requires Node 18+ and Google Chrome (set `CHROME=/path/to/chrome` if not at the macOS default path).

## Recapture screenshots

```bash
php artisan orora:seed-demo           # demo workspace (demo@ororafarm.rw / password), if not already present
php artisan serve --port=8765
cd build && BASE=http://127.0.0.1:8765 node shots.mjs [name ...]
```

Screenshots listed in each `shots-wanted.txt` (`name|path|note`) are captured; numbered red markers come from `annotations.json`.
Platform-admin screenshots (`ad-platform-*`) need a platform admin: `SHOT_EMAIL=... SHOT_PASSWORD=... node shots.mjs ad-platform-dashboard`.
