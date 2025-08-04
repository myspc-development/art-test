#!/usr/bin/env bash
# Add the dashboard capability to the member role and all existing member users.
# Usage: ./bin/repair-member-cap.sh
wp cap add member view_artpulse_dashboard
wp user list --role=member --field=ID | xargs -I % wp user add-cap % view_artpulse_dashboard
