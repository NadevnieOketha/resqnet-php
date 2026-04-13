# Discharge Baseline Thresholds

This folder contains a standalone script to compute river discharge baselines and flood thresholds for active hydrostations.

## What It Computes

- Historical window: `2020-01-01` to `2024-12-31`
- Source stations: ArcGIS hydrostations, filtered to active gauges
- Source discharge: Open-Meteo Flood API (`daily=river_discharge`)
- Threshold formula:
  - `alert = 2 x mean`
  - `minor = 5 x mean`
  - `major = 10 x mean`

## Run

```bash
php tools/discharge_baselines/compute.php
```

## Generated Files

- `tools/discharge_baselines/output/baselines.json`
  - Raw computation output per station.
- `modules/forecast/discharge_thresholds.php`
  - Saved thresholds for the forecast module.

## Re-run Notes

- Re-run when station list changes or if baseline policy changes.
- The script is self-contained and does not import forecast module code.
