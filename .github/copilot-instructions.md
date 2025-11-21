# Copilot Instructions for `resto`

## Project Overview
- `resto` is a STAC-compliant geospatial search engine and catalog, primarily for Earth Observation data but extensible to any spatiotemporal metadata.
- The API follows the [STAC v1.1.0 spec](https://github.com/radiantearth/stac-spec) and OGC conventions ("feature" == "item").
- Main service is deployed via Docker, with configuration in `config.env` and variants in `config-dev.env`, `config-dev-pgbouncer.env`.
- Database is PostgreSQL (>=11) with required extensions: postgis, postgis_topology, unaccent, uuid-ossp, pg_trgm.

## Architecture & Key Components
- Core PHP code is in `app/resto/core/` (e.g., `RestoRouter.php` defines all API routes and their handlers).
- API endpoints cover users, groups, rights, collections, features/items, catalogs, authentication, and STAC search.
- Data model SQL is in `resto-database-model/` (split into extensions, functions, common/target models, triggers).
- Example data and API payloads are in `examples/collections/`, `examples/catalogs/`, `examples/items/`, `examples/users/`.
- Dockerfiles and deployment scripts are in `build/` and root (`deploy`, `undeploy`).

## Developer Workflows
- **Build & Deploy:**
  - Run `./deploy` to build and start the service (uses Docker Compose).
  - Use `./deploy -e config-dev.env` for development mode (debugging/logs enabled).
  - Use `./undeploy` to stop and clean up.
- **Database:**
  - Default DB is managed via Docker Compose (`docker-compose.yml`).
  - For external DB, update `config.env` and ensure required extensions/permissions.
- **API Testing:**
  - Use `curl` with example JSON files to POST/PUT users, collections, items, catalogs (see `docs/USERS_AND_RIGHTS.md`, `docs/COLLECTIONS_CATALOGS_ITEMS.md`).
  - Auth is token-based; see `/auth` endpoints and `scripts/generateAuthToken`.

## Project-Specific Patterns & Conventions
- All API routes and their PHP handler mappings are centralized in `RestoRouter.php`.
- Collections, catalogs, and items are managed via RESTful endpoints; payloads must match example schemas.
- User/group/rights management is built-in; admin user is auto-created on first launch.
- Environment variables control all config; changes require undeploy/redeploy.
- Catalogs are flexible JSON structures for organizing items across collections.
- Aliases for collections must be unique across DB.

## Integration Points & External Dependencies
- Relies on Docker, Docker Compose, and PostgreSQL with specific extensions.
- PHP dependencies are in `app/vendor/` (e.g., PHPMailer, JWT, lexer).
- STAC spec compliance is core; external clients can interact via standard STAC endpoints.

## Key Files & Directories
- `app/resto/core/RestoRouter.php`: API route definitions and handler mapping.
- `resto-database-model/`: SQL schema, functions, triggers.
- `examples/`: Example payloads for API testing.
- `docs/`: Developer and user documentation.
- `deploy`, `undeploy`: Service lifecycle scripts.
- `config.env`: Main environment configuration.

## Example: Adding a Collection
```bash
curl -X POST -d@examples/collections/DummyCollection.json "http://admin:admin@localhost:5252/collections"
curl "http://localhost:5252/collections"
```

---

If any section is unclear or missing, please specify what needs improvement or additional detail.
