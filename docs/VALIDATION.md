# MODX 2 validation

## Beta12: Apache ECharts migration (2026-09-22)

- Replaced Highstock with pinned Apache ECharts 5.6.0 in both default frontend and dashboard views.
- PHP runtime, English/Russian lexicons, PSR-12 and JavaScript syntax checks pass.
- Disposable transport lifecycle passes: required credentials, clean install/uninstall,
  password setting migration, preserved settings/history/schedule, and no duplicate cron job.
- Upgraded the Laragon site to beta12; saved settings and history hashes were unchanged.
- The known original Highstock file was removed; the archive contains no Highstock,
  experimental Dygraphs/Chart.js renderers, runtime history or credentials.
- Chrome frontend and generated Manager widget: 1,243 points, one historical gap,
  Russian dates, six working period buttons, no overlap at 1,280px and 390px.
- Empty history, single reading, independent instances, dashed gap with a null separator,
  and disposal after DOM removal pass `_build/test-charts.cjs`.
- No JavaScript exceptions or Highstock requests were observed.
- Manager testing uses MODX's actual widget renderer with Manager CSS in Chrome;
  full authenticated Manager navigation is still a manual user check.
- MODX is actually 2.8.8 / PHP 8.1.34, despite the site's MODX-2.8.6 directory name.
- Package installed locally; publication to MODX Extras remains pending user validation.

## Historical validation: 2026-09-20

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

## Beta7: lexicons and scheduler instructions

- English/Russian key parity, card rendering, chart labels/date locale, installer translations
  and English fallback pass `_build/test-lexicons.php`.
- Installer translations are embedded in transported scripts; clean-install validation works
  before the namespace's files exist.
- Disposable MODX transport lifecycle passes: required credentials, 15-minute job creation,
  uninstall cleanup, upgrade preservation and no duplicate jobs.
- PSR-12 and PHP syntax checks pass.
- Local upgrade preserves settings and history. Chrome renders 1,242 chart points on both
  frontend and generated Manager widget, with no JavaScript errors. Dashboard cards have
  two SVG icons and no overflow at 1,100px and 390px viewports.
- Existing CronManager job remains active every 15 minutes with a successful latest log.
- README/package readme explain service startup, crontab setup and Windows scheduling,
  with CronManager, crontab, Ubuntu and Red Hat documentation links.
