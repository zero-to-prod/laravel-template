# CLAUDE.md

## Commands

All commands run inside the Sail container via `./vendor/bin/sail` (aliased below as `sail`).

```bash
sail up -d
sail down
sail composer dev # start development server
sail composer check # validation
sail composer fix # automated fixes
```

Run `sail composer check` at the end of each turn.

## API endpoints

Building or changing one: follow `docs/api-endpoint-convention.md` — the
6-artifact module shape (route case → request DTO → response DTO → schema →
controller → test). Read it instead of copying an existing module.