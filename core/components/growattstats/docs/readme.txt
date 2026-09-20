# growattStats for MODX 2

Growatt solar generation statistics, a frontend history chart, and a MODX Manager dashboard widget.

## Status and requirements

Version **1.0.1-beta2** targets MODX Revolution **2.8.x**, PHP **7.4+** with cURL and JSON,
and CronManager. Tested locally on MODX **2.8.8 / PHP 8.1.34**. MODX 3 is a separate future stage.

This is a validation build, not yet cleared for submission to MODX Extras.
The bundled Highstock library has separate licensing: redistribution rights must be confirmed
or the chart implementation replaced before public release. See [third-party notices](docs/THIRD-PARTY.md).

## Installation

1. Install CronManager.
2. Upload `growattstats-1.0.1-beta2.transport.zip` through Extras > Installer.
3. The installer requests **growattstats_token** (Growatt API token) and
   **growattstats_plant_id** (plant identifier). New installations require both.
4. Add `[[!growattShowChart]]` to a resource, or use the `growattStats` alias.
5. Schedule `growattCronDataUpdate` in CronManager to refresh the readings periodically.
6. Add the growattStats widget to the desired Manager dashboard.

Obtain a token and Plant ID from your Growatt account/provider. Tokens are stored as system
settings; restrict Manager access accordingly. The installer never embeds the stored token in HTML.

On upgrade, blank credential fields preserve existing values. Legacy `growattstats_token_id`
is accepted and migrated to `growattstats_token`. Other settings and historical files are preserved.

## Settings and snippets

| Setting | Default | Purpose |
| --- | --- | --- |
| `growattstats_token` | empty | Growatt API token |
| `growattstats_plant_id` | empty | Plant identifier |
| `growattstats_plant_name` | empty | Display label |
| `growattstats_price` | `1.20` | Revenue calculation per kWh |

`growattShowChart` accepts `tpl` (default `growattShowChart`), `toPlaceholder`,
`plantName`, and `price`. `growattStats` is its compatibility alias.
`growattCronDataUpdate` refreshes readings and returns success/failure.
The Manager uses the separate `growattShowWidget` chunk. Copy bundled chunks to customize them;
package upgrades replace bundled chunks. The default markup displays energy in kWh.

Existing integrations retain the Growatt request helper methods (`requestPlantData`,
`requestPlantEnergy`, `requestPlantPower`, `requestPlantDetails`, and inverter helpers).
An existing `growattstats_api_url` setting is still honored for plant-data requests.

## History and errors

History is stored in `assets/components/growattstats/data/chart-data.json` and is public chart data.
Both older Date.UTC JavaScript histories and `growattStatsData` JavaScript payloads can be read
and migrated. The transport archive contains no history or credentials. Keep backups of your history
before upgrades or uninstalling. A failed request leaves the previous readings available.

Fresh readings are obtained by the cron snippet; viewing a page is not a periodic refresh mechanism.
API failures are logged with a `[growattStats]` prefix without raw response bodies or tokens.
A local site's timezone and plant timezone may differ: daily points currently use UTC dates.
Only one chart per page is supported (`growattstats-container`).

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
