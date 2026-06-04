# Rwanda location data

Farm registration uses cascading selects for province → district → sector → cell → village.

Download the dataset (about 6 MB):

```bash
php artisan rwanda:download-locations
```

The file is saved as `rwanda_locations.json` in this directory. Source: [jnkindi/rwanda-locations-json](https://github.com/jnkindi/rwanda-locations-json).

This file is **not in Git** (like `public/build/`). For cPanel hosting, either:

```bash
php artisan rwanda:download-locations
```

Or upload from your Mac:

`database/data/rwanda_locations.json` → `ororafarm.com/database/data/rwanda_locations.json`
