#!/bin/sh

# Duplicate files slated for removal:
# - assets/js/artist-dashboard.js
# - assets/css/ap-dashboard-modern.css
# - assets/css/min/ap-dashboard-modern.css
# - assets/js/Sortable.min.js
# - assets/css/min/ap-style.css
# - templates/salient/archive-artpulse_artist.php
# - templates/salient/archive-artpulse_org.php
# - assets/css/min/frontend.css
# - Sprint_Plan.md
# - docs/admin/admin-permissions.md
# - docs/admin/admin-usage.md

# Run git rm on duplicate files

git rm assets/js/artist-dashboard.js
git rm assets/css/ap-dashboard-modern.css
git rm assets/css/min/ap-dashboard-modern.css
git rm assets/js/Sortable.min.js
git rm assets/css/min/ap-style.css
git rm templates/salient/archive-artpulse_artist.php
git rm templates/salient/archive-artpulse_org.php
git rm assets/css/min/frontend.css
git rm Sprint_Plan.md
git rm docs/admin/admin-permissions.md
git rm docs/admin/admin-usage.md
