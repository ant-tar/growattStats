# growattStats

Getting and displaying energy generation statistics via [Growatt](https://en.growatt.com/) for frontend and backend (widget).

## Installation
Install the extra via MODX package manager

## Requirements
The package depends on `CronManager` (`cronmanager` namespace).
Tested with MODX 2.8.x and PHP 8.1.34.

## Growatt access
Sign up for an account on [Growatt](https://en.growatt.com/) and get the plant details from your provider.

## System settings
Before using this extra:
- `growattstats_api_url` is prefilled with the Growatt endpoint
- `growattstats_token` is requested during installation
- `growattstats_plant_id` is requested during installation

Optional settings:
- `growattstats_plant_name`
- `growattstats_price`

The chart history is stored locally in `assets/components/growattstats/data/chart-data.js` and refreshed by the cron snippet.

The cron update snippet can be run in two modes:
- through CronManager, where it returns a success or failure message for the cron log
- manually on a page, where it still updates the JS cache file and returns the same status text

Use the `growattCronDataUpdate` snippet for the scheduled job.

The PHP service also exposes a generic Growatt request layer:
- `requestApi()` for raw GET/POST/PUT calls
- `requestGrowattCommand()` for named Growatt endpoints
- `requestPagedGrowattCommand()` for iterative endpoints that must be loaded page by page
