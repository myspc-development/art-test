# ArtPulse API Reference

This guide documents the REST API endpoints available for third-party integration.

## Authentication

Use WordPress nonce authentication for logged-in users or application passwords for external services.
Include the `X-WP-Nonce` header when calling endpoints from JavaScript.

## Endpoints

### `GET /artpulse/v1/filtered-posts`

Retrieve a list of posts filtered by taxonomy terms.
Parameters:
- `post_type` (string, required)
- `taxonomy` (string, required)
- `terms` (comma separated slugs)
- `per_page` (int, default `5`)
- `page` (int, default `1`)

Example request:

```bash
curl '/wp-json/artpulse/v1/filtered-posts?post_type=artpulse_artist&taxonomy=artist_specialty'
```

The response includes an array of post IDs, titles and permalinks along with pagination information.

