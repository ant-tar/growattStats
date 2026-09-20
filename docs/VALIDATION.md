# MODX 2 validation ? 2026-09-20

Environment: Laragon `MODX-2.8.6.test`, actually MODX 2.8.8, PHP 8.1.34.
A separate installation/database was used for destructive lifecycle tests.

Passed:

- PSR-12 (PHP_CodeSniffer) and PHP syntax checks; PSR-4 service and MODX 2 class alias.
- New transport installation rejects missing credentials and accepts both installer fields.
- Stored token is not embedded in installer HTML; new token setting uses a password field.
- Clean uninstall removes package snippets; reinstallation succeeds.
- Blank upgrade credentials preserve existing values and optional settings.
- Legacy token_id is migrated; existing chart history survives installation unchanged.
- Structured JavaScript history migrates; same-day refresh does not duplicate a point.
- Revenue calculation, snippet property overrides and inline JSON escaping.
- Installed frontend snippet and Manager widget render.
- Real Growatt request and installed cron snippet refresh succeed.
- Chrome frontend: HTTP 200, chart with 1,241 points after refresh, no JavaScript exceptions.
- Chrome rendering of the generated Manager widget: 1,241 points, no JavaScript exceptions.

The Manager widget was exercised through MODX's widget renderer and its generated HTML in Chrome;
a full authenticated Manager navigation was not automated.

Limits / publication gate:

- MODX 3 is not targeted or tested. Other MODX/PHP combinations need separate validation.
- Highstock redistribution rights must be confirmed or the library replaced before public release.
- This is a beta validation package. No MODX Extras submission or GitHub release was published.
- One chart per page and UTC daily timestamps remain implementation limits.

Local backup and browser screenshots are under ignored `_build/local/`; they are not release assets.
Reproduce source checks with `_build/test-runtime.php`; run `_build/test-package.php` only against
an explicitly designated disposable database (see README).

## Beta4: scheduled refresh and retained history example

- Original Date.UTC history is retained under `core/components/growattstats/docs/examples/`.
  It is packaged as documentation and never overwrites the live data directory.
- Clean install creates an active CronManager job with a 15-minute interval.
- Upgrade preserves edited interval/disabled state and does not duplicate the job.
- Clean uninstall removes the job owned by this package, including when custom properties are added.
- CronManager receives explicit JSON success/error results; both outcomes tested.
- Windows task `growattStats-Laragon-CronManager` invokes the selected site's CronManager every minute.
  It uses PHP without a console window and an interactive user logon; Laragon/MySQL must be running.
- Actual task execution on 2026-09-20 refreshed the API data at 16:12:01 UTC;
  the CronManager job log reports `[growattStats] Readings updated` with error=false.
- Direct CLI package installations must refresh the MODX cache afterward, as the Package Manager
  processor does. This avoids executing an older compiled version of the cron snippet.
