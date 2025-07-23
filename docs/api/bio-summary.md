---
title: Bio Summary Endpoint
category: api
role: developer
last_updated: 2025-07-20
status: complete
---
# Bio Summary Endpoint

The `GET /artpulse/v1/bio-summary/{id}` route returns a short AI-generated summary of an artist biography.

## Authentication

Any logged-in user with the `read` capability may call this endpoint. Include a valid `X-WP-Nonce` header when making the request from JavaScript or cookie-authenticated contexts.

## Parameters

| Parameter | Type | Description |
|-----------|------|-------------|
| `id` | integer | ID of the `artpulse_artist` post to summarize. |

## Example

```bash
curl -H 'X-WP-Nonce: <nonce>' '/wp-json/artpulse/v1/bio-summary/42'
```

Successful responses return JSON with a `summary` field:

```json
{ "summary": "Award-winning painter based in NYC." }
```

A `404` error is returned if the artist does not exist. A `500` error indicates OpenAI is not configured.

> 💬 *Found something outdated? [Submit Feedback](../feedback.md)*

