# ArtPulse Management Plugin

## CSV Import/Export

An Import/Export tab is available under **ArtPulse → Settings**. From this tab administrators can:

- Upload a CSV file and map its columns to post fields or meta keys. After selecting the mappings a preview of the first few rows is displayed before importing.
- Import data in chunks via the REST API endpoint `artpulse/v1/import`.
- Download CSV exports for organizations, events, artists and artworks.

Only users with the `manage_options` capability may perform imports. PapaParse is used client side for parsing CSV files.
