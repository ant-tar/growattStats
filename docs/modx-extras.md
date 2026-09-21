# MODX Extras listing copy

Prepared for **1.0.1-beta11**. These are copy-ready English listing texts.
No submission has been made. Release metadata and archive checksum are in
[modx-extras-metadata.json](modx-extras-metadata.json).

## Maintainer note (do not paste into the product description)

The current archive bundles Highstock 12.1.2. Applicable redistribution rights
have not been confirmed. Resolve this before submitting the archive: document
permission covering distribution through MODX Extras, or replace the library
and rebuild/retest. See [third-party notices](THIRD-PARTY.md) and the
[Highcharts OEM FAQ](https://shop.highcharts.com/faq).
The component's GPL license does not license Highstock.

This draft describes the current build. Do not label it stable or claim MODX 3
support. The selected catalog icon is a white solar G on a rounded blue background.
Upload growattstats-logo.png, not the earlier comparison preview.

## Listing data

| Field | Value |
| --- | --- |
| Name | growattStats |
| Suggested slug | growattstats |
| Author | Anton Tarasov (ant-tar) |
| Version | 1.0.1-beta11 |
| Release status | Beta |
| MODX compatibility | MODX Revolution 2.8.x; package constraint >=2.8.0 <3.0.0 |
| Tested environment | MODX 2.8.8, PHP 8.1.34, CronManager 1.5.1, MySQL |
| PHP | 7.4+ with cURL and JSON |
| Required Extra | CronManager 1.2.2+ |
| Database | MySQL |
| Languages | English, Russian |
| Component license | GNU GPL v2 or later; bundled library has separate terms |
| Transport package | growattstats-1.0.1-beta11.transport.zip |
| Icon | docs/assets/growattstats-logo.png; white solar G on blue, 512 x 512 PNG |
| Alternative icon | docs/assets/growattstats-icon-green.png |
| Website / source | https://github.com/ant-tar/growattStats |
| Documentation | https://github.com/ant-tar/growattStats/blob/main/README.md |
| Support / issues | https://github.com/ant-tar/growattStats/issues |

Select the equivalent version/category choices offered by the live submission
form. Do not choose an open-ended MODX range that includes MODX 3. The site's
folder is named MODX-2.8.6, but the version actually tested is MODX 2.8.8.
No GitHub release download URL exists yet; upload the transport ZIP directly.

## Tagline

Growatt solar generation statistics and history charts for MODX 2.

## Short description

Display today's and total solar generation, an interactive history chart, and a
Manager dashboard widget using the Growatt API. Includes English and Russian
translations and automatic refresh through CronManager. For MODX 2.8.x.

## Full description

GrowattStats connects a MODX 2 website to the Growatt API to display solar
power generation. Show today's energy and total energy in separate cards,
explore recorded history with a range selector and navigator, and view the
same plant statistics in the MODX Manager dashboard.

The installer requests a Growatt API token and Plant ID. The token setting uses
a password field in the Manager. Set a display name for your plant and add the
frontend snippet to a resource. The default chunks can be copied and customized.
English and Russian lexicons cover the interface, chart labels, settings and
installer.

CronManager is required. Installation creates an active refresh job with a
15-minute interval. An external scheduler must invoke CronManager every minute;
the Growatt request runs only when the component's job is due. Administrators
can change the job interval in CronManager. Upgrades preserve the existing job
schedule, credentials and recorded history.

History is accumulated from successful API refreshes. It is stored locally as
public chart data, with one point per day updated by later refreshes. Failed
requests leave the previous readings available. The component does not download
missing historical dates automatically.

This beta targets MODX Revolution 2.8.x. It is an independent community
integration and is not affiliated with or endorsed by Growatt.

## Features

- Today's generation and total generation cards.
- Interactive history chart with date ranges and a navigator.
- MODX Manager dashboard widget.
- English and Russian translations, including chart dates.
- Installer fields for the Growatt API token and Plant ID.
- Automatic CronManager job registration without duplicate jobs on upgrade.
- Customizable MODX chunks and a configurable plant display name.
- Preserved credentials and history during upgrades.

## Requirements

- MODX Revolution 2.8.x (MODX 3 is not supported by this release).
- PHP 7.4+ with cURL and JSON, including the CLI PHP used by cron.
- CronManager 1.2.2 or later.
- A valid Growatt API token and Plant ID with access to plant readings.
- An external scheduler and database/filesystem access for its operating-system user.
- Write access to MODX cache and assets/components/growattstats/data/.

## Installation instructions

1. Install CronManager through MODX Package Management.
2. Install the growattStats transport package and enter `growattstats_token`
   and `growattstats_plant_id`. Both are required for a new installation.
3. In System Settings, select the `growattstats` namespace and set
   `growattstats_plant_name` to your plant's display name.
4. Add `[[!growattShowChart]]` to a resource. The `growattStats` snippet is a
   compatibility alias. Add the growattStats dashboard widget to your Manager dashboard.
5. Configure the external scheduler as described below and verify a successful
   run in the CronManager job log.

Optional frontend example:

```modx
[[!growattShowChart? &plantName=`My solar plant`]]
```

For a custom layout, copy the `growattShowChart` chunk to `MySolarChart` and call:

```modx
[[!growattShowChart? &tpl=`MySolarChart`]]
```

Upgrades replace bundled chunks, so keep custom layouts under different names.
Leave credential fields blank during upgrades to retain their current values.
The password field masks the token in the editor; it does not encrypt its storage.

## Scheduler setup

The cron service must be running. On a Debian/Ubuntu server with systemd and
the standard cron package, an administrator can run:

```sh
sudo apt-get install cron
sudo systemctl enable --now cron
systemctl is-active cron
```

As the site's operating-system user, run `crontab -e` and add:

```cron
* * * * * /usr/bin/php /path/to/modx/assets/components/cronmanager/cron.php
```

Replace the PHP and MODX paths. This is a user crontab, without a username column.
Check the saved entry with `crontab -l`. On RHEL-compatible systems use the
`cronie` package and `crond` service. On shared hosting use the Cron Jobs page
or ask the host to enable the scheduler. On Windows use Task Scheduler with a
one-minute trigger; see the repository README for the Laragon example.

CronManager is invoked every minute, while growattStats defaults to every
15 minutes. For hourly readings, change the growattStats job to 60 minutes.
A daily history point is updated, not duplicated, by subsequent refreshes.

Documentation:
- https://jako.github.io/CronManager/usage/
- https://man7.org/linux/man-pages/man5/crontab.5.html
- https://help.ubuntu.com/community/CronHowto

## Release notes / What's new in 1.0.1-beta11

- MODX 2 transport installer with required Growatt credentials.
- CronManager dependency and automatic 15-minute refresh job.
- English/Russian lexicons for cards, charts, settings, logs and setup.
- Distinct generation cards with SVG icons on frontend and dashboard.
- Localized chart dates and corrected range-button layout on first render.
- Existing token settings are migrated to password fields during upgrades.
- Historical readings and administrator-modified schedules survive upgrades.
- PSR-4 service loading with MODX 2 compatibility and PSR-12 source checks.

## Suggested categories and tags

Categories: Integration, Statistics, Dashboard (choose available equivalents).

Tags: Growatt, solar, energy, photovoltaic, generation, statistics, chart,
dashboard, API, CronManager, MODX 2.

## Known limitations

- Beta; tested on MODX 2.8.8 / PHP 8.1.34, not every permitted version combination.
- One chart per page.
- Daily history uses UTC dates, which may differ from the plant's local timezone.
- No automatic recovery of readings for missed historical days.
- Plant/API availability depends on the supplied Growatt account credentials.
- Highstock has separate licensing; see the maintainer note before distribution.

## Submission reference

MODX accepts transport packages and reviews Extra submissions:
https://docs.modx.com/3.x/en/extras#distributing-your-own-extras
The page documents submission, not this component's supported MODX version.
