# Oregon Tires Auto Care — Monorepo Project Instructions

See main project instructions at `/Users/hiphop/CLAUDE.md` for full details.

## Quick Reference
- **Stack**: Static HTML + Tailwind CSS v4 + PHP API + MySQL
- **Live**: https://oregon.tires
- **Site type**: `client` (independent mode)
- **Deploy**: `./deploy.sh` (builds CSS, stages changed files, SCPs to server)
- **Server**: `ssh hiphopworld` → `/home/hiphopwo/public_html/---oregon.tires/`

## Kit Usage
- **form-kit** — contact form (`FORM_KIT_PATH`)
- **commerce-kit** — checkout, payments (`COMMERCE_KIT_PATH`)
- **member-kit** — customer accounts, auth (`MEMBER_KIT_PATH`)
- **engine-kit** — 1vsM network integration (`ENGINE_KIT_PATH`)

## Key Paths
- Local: `public_html/` prefix
- Server: flat at `---oregon.tires/` level (strip `public_html/` when SCPing)
- CLI scripts: `cli/` (bootstrap path on server: `__DIR__ . '/../includes/bootstrap.php'`)
- SQL migrations: `sql/` (outside public_html)
- Uploads: `uploads/inspections/{ro_number}/` (inspection photos)

## Shop Management System (Phase 1 — Feb 2026)
- **RO lifecycle**: intake → diagnosis → estimate_pending → pending_approval → approved → in_progress → waiting_parts → ready → completed → invoiced
- **VIN decode**: NHTSA vPIC API with permanent DB cache (`oretir_vin_cache`)
- **Tire fitment**: API with 90-day DB cache (`oretir_tire_fitment_cache`)
- **DVI**: Traffic light system (green/yellow/red), photo capture, customer view via token
- **Estimates**: Per-item approve/decline, token-based bilingual approval page
- **Kanban board**: Drag-and-drop RO status management (`admin/js/kanban.js`)
- **Reference numbers**: `RO-XXXXXXXX` (repair orders), `ES-XXXXXXXX` (estimates)

## Admin JS Files
- `admin/js/admin.js` — main admin panel logic (also at `js/admin.js`)
- `admin/js/repair-orders.js` — RO tab, inspection, estimate management
- `admin/js/kanban.js` — kanban board view (self-injects toggle button)
- `admin/js/charts.js`, `enhancements.js`, `features.js`, `navigation.js`

## .env Kit Vars (server)
```
FORM_KIT_PATH=/home/hiphopwo/shared/form-kit
COMMERCE_KIT_PATH=/home/hiphopwo/shared/commerce-kit
MEMBER_KIT_PATH=/home/hiphopwo/shared/member-kit
ENGINE_KIT_PATH=/home/hiphopwo/shared/engine-kit
```
