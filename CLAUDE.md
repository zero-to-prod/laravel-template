# CLAUDE.md

## Commands

All commands run inside the Sail container via `./vendor/bin/sail` (aliased below as `sail`).

```bash
sail up -d
sail down
sail composer dev
sail test # all suites
sail test --testsuite=Behavior
sail test --testsuite=Unit
sail test --filter=ApiLoginTest
sail test --filter=test_method
sail pint # fix all files
sail pint --test
sail artisan <command>
sail composer <command>
sail composer ide # runs ide-helpers
```
