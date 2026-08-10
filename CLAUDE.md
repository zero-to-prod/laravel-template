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

### While you work

`composer check` runs pint, rector, phpstan, two openapi commands and the whole
suite in series, so it is the wrong tool for an inner loop — a rector nit on a
test file costs a full run to find. Use the narrow commands until the work is
done:

```bash
sail pest --filter=UpdateUserName    # the tests you are writing
sail composer openapi-validate       # the document, seconds, no suite
sail composer fix                    # apply what rector and pint would demand
```

Then `sail composer check` once, at the end of each turn. Running `fix` before
`check` is what keeps the gate from failing on a rewrite it was going to apply
anyway.

## API endpoints

Building or changing one: follow `docs/api-endpoint-convention.md` — the
6-artifact module shape (route case → request DTO → response DTO → schema →
controller → test). Read it instead of copying an existing module.