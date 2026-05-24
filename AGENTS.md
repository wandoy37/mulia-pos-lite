# mulia-pos-lite — Agent Guide

## Stack
- **Backend:** Laravel 13, PHP 8.3+, Pest 4 (testing), Pint (PHP code style)
- **Frontend:** Vue 3 (Composition API, `<script setup>`), Inertia.js 3, Bootstrap 5.3, Tailwind CSS 4, Vite 8
- **No TypeScript, no ESLint, no Prettier, no CI/CD**

## Key Commands
| Action | Command |
|--------|---------|
| Dev servers (all) | `composer dev` |
| Vite only | `npm run dev` |
| Build | `npm run build` |
| Test | `composer test` |
| PHP code style | `./vendor/bin/pint` |
| Full setup | `composer setup` |

## .npmrc has `ignore-scripts=true`
Packages with required `postinstall` hooks (e.g. `esbuild`, `sharp`) must be handled manually.

## Models use PHP 8 attributes, not `$fillable` properties
```php
#[Fillable(['nama_satuan'])]
class Satuan extends Model {}
```

## WIP / known issues
- `SatuanController@store` has `dd($validated)` — not persisting yet
- `TabelSatuan.vue` uses hardcoded rows
- `Tests/Pest.php` has `RefreshDatabase` commented out
- No auth, no login/register routes
- Ziggy static routes file (`resources/js/ziggy.js`) must be updated when routes change

## Testing quirks
- `composer test` runs `php artisan config:clear` first
- Uses SQLite in-memory for tests (`phpunit.xml`)
- Session/Cache/Queue drivers set to `array`/`sync` in test env
- Run single test: `php artisan test --filter=TestName`

## Architecture notes
- **Vue pages** auto-resolved from `resources/js/Pages/**/*.vue` by name
- **Routes** in `routes/web.php` only (no API routes)
- **Database:** sessions table migration not yet added (driver defaults to `database`)
- All business models use `softDeletes()`
- Tables named in Indonesian: `satuans`, `suppliers`, `produks`
- Entrypoint: `resources/js/app.js` bootstraps Inertia + Ziggy
- `.editorconfig` spaces, 4-space indent (2-space for yaml)
