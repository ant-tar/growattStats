# growattStats

Getting and displaying energy generation statistics via [Growatt](https://en.growatt.com/) for frontend and backend (widget).

## Installation
Install the extra via MODX package manager

## Requirements
The package depends on `CronManager` (`cronmanager` namespace).

## Get Token ID and Plant ID
Signup for an account on [Growatt](https://en.growatt.com/) and ask provider for more details.

## System settings
Before using this extra:
- `growattstats_api_url` is prefilled with the Growatt endpoint
- `growattstats_token_id` stores your API token
- `growattstats_plant_id` is requested during installation

Optional settings:
- `growattstats_plant_name`
- `growattstats_price`

The chart history is stored locally in `assets/components/growattstats/data/chart-data.json` and refreshed by the cron snippet.
