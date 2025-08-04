#!/usr/bin/env bash
# Restore core page and post capabilities to the administrator role.
# Usage: ./bin/repair-admin-caps.sh
wp cap add administrator \
    edit_pages publish_pages edit_others_pages edit_published_pages \
    delete_pages delete_others_pages \
    edit_posts publish_posts edit_others_posts delete_posts
