Growatt energy generation statistics for frontend and backend.

The package depends on `CronManager` (`cronmanager` namespace).

Required system settings:
- `growattstats_api_url` prefilled with the Growatt endpoint
- `growattstats_plant_id` requested during installation
- `growattstats_token_id` for API authentication

Optional system settings:
- `growattstats_plant_name`
- `growattstats_price`

The package stores chart history in `assets/components/growattstats/data/chart-data.json` and updates it via the cron snippet.
