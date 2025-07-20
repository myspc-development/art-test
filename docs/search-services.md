---
title: Search Service Evaluation
category: developer
role: developer
last_updated: 2025-07-20
status: draft
---
# Search Service Evaluation

This project evaluated two hosted search solutions for WordPress content: **Algolia** and **ElasticPress**. Both integrate with custom post types and can offload queries from the database.

## Algolia
- Fast SaaS search with typo tolerance.
- Excellent developer documentation and a mature WordPress plugin.
- Requires an external account and API keys.

## ElasticPress
- Self‑hosted Elasticsearch powered search.
- Works well with large datasets when an Elasticsearch cluster is available.
- Open source and integrates with WP _Query via the ElasticPress plugin.

For smaller installs Algolia is easier to set up, while ElasticPress offers more control when you already run Elasticsearch.

## Custom Indexes

Artists and organizations are indexed with the following metadata to support faceted search:

- **Medium** – derived from the `artist_specialties` or `ead_org_type` fields.
- **Location** – stored in the `address_components` meta.
- **Tags** – any taxonomy terms attached to the post.

External search services may use these fields to build facets or filters.