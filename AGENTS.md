# AGENTS.md

This file provides guidance to AI coding assistants working with code in this repository.

## About this app

Unattended Upgrades is a small Nextcloud app with a single purpose: a background job that
periodically checks for available app updates and installs them, subject to a configurable
maintenance window and allow/block list. There is no frontend, no database migrations, and
no REST API — it's a background job, a CLI command, and two services.

## Agent autonomy in this repo

The broader Nextcloud AI contribution policy says agents must never autonomously commit,
comment, or open pull requests. In this repo, that's relaxed: an agent may create commits,
push branches, post PR/issue comments, and open pull requests directly, **provided a human
reviews the change before or as it happens** (an interactive session where the human directs
and watches the work counts as that review). This does not relax anything else in the policy:
- Commits still need an `Assisted-by: AGENT_NAME:MODEL_VERSION` trailer.
- An agent may also add `Signed-off-by` using the reviewing human's configured git identity
  (`user.name`/`user.email`), since the human present in the session is the one certifying
  the DCO for that commit — the agent is acting as their hands, not certifying on its own
  behalf.
- Security reports still go through HackerOne with independent human verification, never
  autonomous GitHub issues.

## Commands

There is no build step, package manager install, linter, or test suite in this repo (no
`composer.json`, no JS build tooling beyond a release-automation script). Development is
plain PHP edited in place against a running Nextcloud instance.

- Dry run (reports how many apps would be upgraded, without installing anything):
  `occ unattended-upgrades:dry-run`
- The actual upgrade runs via the background job (`UnattendedUpgradeJob`), once an hour, as
  scheduled by Nextcloud's cron.
- When adding a new PHP file, run `build/autoloaderchecker.sh` (per the server-wide agent
  guidelines) if present in your Nextcloud server checkout — this app itself doesn't ship
  one.

### Versioning

`appinfo/info.xml`'s `<version>` and `package.json`'s `version` are kept in sync
automatically by the release workflow (`build/pre-commit.js`, driven by
`conventional-changelog-action`) based on Conventional Commit messages. Do not hand-edit
version numbers.

`conventional-changelog-action` only bumps the version and cuts a release for `feat`,
`fix`, and breaking-change commits — a `chore:` commit is skipped entirely (no bump, no
release). This matters for `<nextcloud min-version="..." max-version="..."/>` changes in
`appinfo/info.xml`: commit those as `feat(deps): Add/Drop Nextcloud NN support`, not
`chore(deps):`, or the change will silently never ship in an automated release.

## Architecture

- **`lib/BackgroundJob/UnattendedUpgradeJob.php`** — a `TimedJob` running every 3600s that
  calls `Upgrader::upgrade()`. This is the real entry point in production.
- **`lib/Command/DryRun.php`** — the `occ unattended-upgrades:dry-run` command. Calls
  `Upgrader::upgrade(true)` and prints the count of apps that *could* be upgraded, without
  installing anything.
- **`lib/Service/Upgrader.php`** — the only business logic in the app:
  1. Reads the maintenance window from `Config` and aborts if the current time
     (`ITimeFactory::getTime()`) falls outside it.
  2. Iterates `OC_App::getAllApps()`, keeping apps where `Installer::isUpdateAvailable()`
     is true.
  3. Filters by the allow list (if non-empty, only listed apps pass) and the block list (if
     non-empty, listed apps are excluded). Allow and block are both read from `Config` and
     checked independently — not merged into one list.
  4. For a real run, calls `Installer::updateAppstoreApp($appId)` for each surviving app id
     and logs success/failure per app; exceptions are caught and logged rather than
     propagated, so one failing app never stops the others.
  5. Returns the count of apps upgraded (or, in dry-run mode, the count that would be).
- **`lib/Service/Config.php`** — reads the `unattended_upgrades` system config array (via
  `IConfig::getSystemValue`) and exposes three accessors: maintenance window (as a pair of
  `DateTimeImmutable`, defaulting to the full day), allowed app ids, and blocked app ids.
  This is the only place that knows the shape of the config array documented in
  `README.md`.

### Key conventions

- **License headers**: every PHP file uses the pre-REUSE/SPDX GPL-style header seen in the
  existing files (`@copyright`, `@author`, `@license GNU AGPL version 3 or any later
  version`, full license block) rather than the newer two-line SPDX header. Match the
  existing style in this repo rather than switching it file-by-file.
- **Namespace**: `OCA\UnattendedUpgrades\`, matching `<namespace>` in `appinfo/info.xml`.
- **Registration**: background jobs and commands are registered declaratively in
  `appinfo/info.xml` (`<background-jobs>`, `<commands>`) — there's no `Application.php`
  bootstrap class in this app.
- **Config, not settings UI**: all configuration is admin-edited `config/config.php`
  (system config), not an admin settings page. If you add a new config key, update
  `Config.php` and document the shape in `README.md`.
