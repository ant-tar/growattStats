# growattStats for MODX 2

Growatt solar generation statistics, a frontend history chart, and a MODX Manager dashboard widget.

## Status and requirements

Version **1.0.1-beta7** targets MODX Revolution **2.8.x**, PHP **7.4+** with cURL and JSON,
and CronManager 1.2.2 or later (tested with 1.5.1). Tested locally on MODX **2.8.8 / PHP 8.1.34**. MODX 3 is a separate future stage.

This is a validation build, not yet cleared for submission to MODX Extras.
The bundled Highstock library has separate licensing: redistribution rights must be confirmed
or the chart implementation replaced before public release. See [third-party notices](docs/THIRD-PARTY.md).

## Installation

1. Install CronManager.
2. Upload `growattstats-1.0.1-beta7.transport.zip` through Extras > Installer.
3. The installer requests **growattstats_token** (Growatt API token) and
   **growattstats_plant_id** (plant identifier). New installations require both.
4. Add `[[!growattShowChart]]` to a resource, or use the `growattStats` alias.
5. The package creates an active `growattCronDataUpdate` job every 15 minutes.
   Configure the external scheduler described below so the job actually runs.
6. Add the growattStats widget to the desired Manager dashboard.

Obtain a token and Plant ID from your Growatt account/provider. Tokens are stored as system
settings; restrict Manager access accordingly. The installer never embeds the stored token in HTML.

On upgrade, blank credential fields preserve existing values. Legacy `growattstats_token_id`
is accepted and migrated to `growattstats_token`. Other settings and historical files are preserved.

## Settings and snippets

| Setting | Default | Purpose |
| --- | --- | --- |
| `growattstats_api_url` | Growatt plant-data endpoint | API endpoint |
| `growattstats_token` | empty | Growatt API token |
| `growattstats_plant_id` | empty | Plant identifier |
| `growattstats_plant_name` | empty | Display label |
| `growattstats_price` | `1.20` | Revenue calculation per kWh |

`growattShowChart` accepts `tpl` (default `growattShowChart`), `toPlaceholder`,
`plantName`, and `price`. `growattStats` is its compatibility alias.
`growattCronDataUpdate` refreshes readings and returns success/failure when called directly.
When CronManager calls it, it returns the required JSON error/message pair for job logging.
The Manager uses the separate `growattShowWidget` chunk. Copy bundled chunks to customize them;
package upgrades replace bundled chunks. The default markup displays energy in kWh.

Existing integrations retain the Growatt request helper methods (`requestPlantData`,
`requestPlantEnergy`, `requestPlantPower`, `requestPlantDetails`, and inverter helpers).
An existing `growattstats_api_url` setting is still honored for plant-data requests.

## History and errors

History is stored in `assets/components/growattstats/data/chart-data.json` and is public chart data.
Both older Date.UTC JavaScript histories and `growattStatsData` JavaScript payloads can be read
and migrated. The transport archive contains no live history or credentials.
The original history file is retained as `core/components/growattstats/docs/examples/chart-data.example.js`;
it is an explicit example and never replaces or initializes live history automatically. Keep backups of your history
before upgrades or uninstalling. A failed request leaves the previous readings available.

Fresh readings are obtained by the cron snippet; viewing a page is not a periodic refresh mechanism.
API failures are logged with a `[growattStats]` prefix without raw response bodies or tokens.
A local site's timezone and plant timezone may differ: daily points currently use UTC dates.
Only one chart per page is supported (`growattstats-container`).

## Automatic refresh

There are two layers: the package-owned **CronManager job** (15-minute interval) and
an **external scheduler** that invokes CronManager. Installing a MODX Extra cannot configure
the hosting account's crontab automatically. CronManager is declared as a required dependency;
its installation must be completed before growattStats is installed.

The **cron service must be running**: a crontab entry alone does not start it.
On a Debian/Ubuntu server using the standard `cron` package and systemd, an administrator can run:

```sh
sudo apt-get install cron
sudo systemctl enable --now cron
systemctl is-active cron
```

On RHEL-compatible systems, install `cronie` with the distribution package manager,
then run `sudo systemctl enable --now crond` and check `systemctl is-active crond`.
On shared hosting, use the control panel's Cron Jobs page; ask the host to enable the scheduler
if that feature is unavailable. These service commands require server administrator access.

As the site's operating-system user, run `crontab -e` and add the following line,
using absolute paths to your CLI PHP executable and MODX installation:

```cron
* * * * * /usr/bin/php /path/to/modx/assets/components/cronmanager/cron.php
```

Save the file, then use `crontab -l` to confirm the entry. This is a user crontab:
do not add a username column. The scheduler's user needs access to MODX configuration/database
and write access to the component's data directory and MODX cache. CLI PHP needs cURL and JSON.
Run `/usr/bin/php /path/to/modx/assets/components/cronmanager/cron.php` manually once
with the same user and inspect the CronManager log to diagnose execution errors.

On Windows/Laragon, use Task Scheduler with a one-minute trigger. For this development site:

```powershell
& .\_build\register-local-cron.ps1 `
    -ModxPath 'D:\laragon\www\MODX-2.8.6' `
    -PhpPath 'D:\laragon\bin\php\php-8.1.34-nts-Win32-vs16-x64\php-win.exe'
```

This creates a task for the currently logged-in Windows user. Laragon/MySQL must be running;
it does not run while that user is logged out or while the computer is off. `php-win.exe`
keeps background runs from opening a console window. The script refuses to overwrite an
existing task with a different command. The hosting version of the Extra does not register Windows tasks.

The package reuses a job already pointing to `growattCronDataUpdate`; it does not duplicate it
or reset the administrator's interval or active state on upgrade. Clean uninstall removes the
job created by the package. Keep its `growattstats_managed_job` property to retain automatic cleanup.
Jobs created manually by an administrator are left alone. CronManager logs each success/failure.

To check the chain: inspect the job's Last run / Next run columns and log, then the JSON file's
`updated_at`. A successful request can have the same readings as the previous request, especially
at night. The API used here reports current plant values; cron only accumulates readings from
successful runs and does not reconstruct missed historical dates.

Documentation: [CronManager setup and usage](https://jako.github.io/CronManager/usage/),
[crontab syntax](https://man7.org/linux/man-pages/man5/crontab.5.html),
[Ubuntu cron guide](https://help.ubuntu.com/community/CronHowto),
and [Red Hat cron service setup](https://docs.redhat.com/en/documentation/red_hat_enterprise_linux/7/html/system_administrators_guide/ch-automating_system_tasks).

## Languages

English and Russian lexicons cover the component's cards, chart controls, settings,
runtime messages, and installer. MODX selects the runtime language; setup uses the Manager
language with English fallback. Installer translations are embedded in the transport archive,
so they also work before the component's files are installed.

## Development

```sh
composer install
composer check-style
# Point to a configured MODX 2.x installation:
export MODX_CORE_PATH=/path/to/modx/core/
php _build/test-runtime.php
php _build/test-runtime.php --live
php _build/build.php
```

In PowerShell set `$env:MODX_CORE_PATH = 'D:/path/to/modx/core/'` instead of `export`.
The builder writes `_build/dist/` and does not automatically install the package.
Runtime classes use PSR-4 (`GrowattStats\`); a small class alias keeps MODX 2 `getService()` working.
All maintained PHP is checked against PSR-12 without excluded sniffs. Composer dependencies
are development tools and are not needed by end users.

`_build/test-package.php` is a destructive lifecycle test for a disposable MODX database only.
Set `MODX_CORE_PATH` to a disposable installation and `GROWATTSTATS_TEST_DB` to its database name,
which must start with `growattstats_qa_`. It removes component records, tests installation,
preservation and removal. Never point it at a real site. See [validation report](docs/VALIDATION.md).

## License and support

Component PHP/templates: GNU GPL v2 or later. Bundled third-party libraries keep their own licenses.
Source and issues: <https://github.com/ant-tar/growattStats>.
