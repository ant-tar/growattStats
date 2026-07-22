GrowattStats is a MODX extra for solar-production monitoring.

It fetches production data from the Growatt Open API, stores chart history in a JavaScript cache file, and renders the same data on the frontend and inside the MODX dashboard widget.

Features:
- frontend chart based on Highcharts
- dashboard widget for MODX Manager
- local JS cache in `assets/components/growattstats/data/chart-data.js`
- cron-driven refresh via `CronManager`
- manual refresh by running the snippet directly
- generic API wrapper for named Growatt endpoints and paged requests

Requirements:
- MODX Revolution 2.8.x
- PHP 8.1.x or compatible
- `CronManager` package installed for scheduled refreshes

Installation settings:
- `growattstats_api_url` prefilled with the Growatt endpoint
- `growattstats_token` requested during installation and sent as a request header
- `growattstats_plant_id` requested during installation
- `growattstats_plant_name` optional
- `growattstats_price` optional

Usage:
- use `[[!growattShowChart]]` for the frontend chart
- add the `growattStats` dashboard widget for the manager
- run `[[!growattCronDataUpdate]]` in CronManager or manually to refresh the cache

The PHP service exposes:
- `requestApi()` for raw GET/POST/PUT calls
- `requestGrowattCommand()` for named Growatt endpoints
- `requestPagedGrowattCommand()` for endpoints that must be loaded page by page
