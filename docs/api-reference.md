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

### `GET /artpulse/v1/filter`

Retrieve directory items (events, artists, artworks or orgs) with optional filters.

Parameters mirror the shortcode filters:
`type`, `limit`, `event_type`, `medium`, `style`, `org_type`, `location`,
`city`, `region`, `for_sale` and `keyword`.

Results are cached for five minutes using a transient keyed by the request
parameters. The cache is flushed whenever related posts are updated.

### `GET /artpulse/v1/event/{id}/comments`

Retrieve approved comments for an event.
Parameters:
- `id` (int, required) – event post ID

Example request:

```bash
curl '/wp-json/artpulse/v1/event/42/comments'
```

### `POST /artpulse/v1/event/{id}/comments`

Add a new comment to the event. Requires authentication.
Parameters:
- `id` (int, required)
- `content` (string, required)

Example request:

```bash
curl -X POST -H 'X-WP-Nonce: <nonce>' -d 'content=Great+show!' \
  '/wp-json/artpulse/v1/event/42/comments'
```

### `POST /artpulse/v1/event/comment/{comment_id}/moderate`

Change comment status to `approve`, `spam` or `trash`.
Parameters:
- `comment_id` (int, required)
- `status` (`approve`|`spam`|`trash`)

Example request:

```bash
curl -X POST -H 'X-WP-Nonce: <nonce>' \
  -d 'status=approve' '/wp-json/artpulse/v1/event/comment/7/moderate'
```

See [Event Comments Codex](event-comments-codex.md) for details.

### `POST /artpulse/v1/favorite`

Add or remove a favorite.
Parameters:
- `object_id` (int, required)
- `object_type` (string, required)
- `action` (`add`|`remove`, required)

Example request:

```bash
curl -X POST -H 'X-WP-Nonce: <nonce>' \
  -d 'object_id=10&object_type=artpulse_event&action=add' \
  '/wp-json/artpulse/v1/favorite'
```

Favorite metrics are described in the [Analytics & Insights Codex](analytics-insights-codex.md).

### `POST /artpulse/v1/follows`

Follow an artist, event or organization.
Parameters:
- `post_id` (int, required)
- `post_type` (string, required)

Example request:

```bash
curl -X POST -H 'X-WP-Nonce: <nonce>' \
  -d 'post_id=5&post_type=artpulse_artist' \
  '/wp-json/artpulse/v1/follows'
```

### `DELETE /artpulse/v1/follows`

Unfollow the specified post.
Parameters are the same as the POST route.

### `GET /artpulse/v1/follows`

List the current user's followed items.
Optional query parameter `post_type` filters the type returned.

### `GET /artpulse/v1/followers/{user_id}`

Retrieve followers of a user by ID.

Follower messaging workflows are covered in the [Messaging & Communication Codex](messaging-communication-codex.md).

### `GET /artpulse/v1/event/{id}/tickets`

List available ticket tiers for an event.
Parameters:
- `id` (int, required)

Example request:

```bash
curl '/wp-json/artpulse/v1/event/42/tickets'
```

### `POST /artpulse/v1/event/{id}/buy-ticket`

Purchase a ticket tier. Requires authentication.
Parameters:
- `id` (int, required)
- `ticket_id` (int, required)
- `quantity` (int, default `1`)

Example request:

```bash
curl -X POST -H 'X-WP-Nonce: <nonce>' \
  -d 'ticket_id=3&quantity=2' \
  '/wp-json/artpulse/v1/event/42/buy-ticket'
```

For detailed purchase flows see the [Monetization & Ticketing Codex](monetization-ticketing-codex.md).

### `GET /artpulse/v1/user/payouts`

Return payout history and current balance for the logged in artist.

Example request:

```bash
curl -H 'X-WP-Nonce: <nonce>' '/wp-json/artpulse/v1/user/payouts'
```

### `POST /artpulse/v1/user/payouts/settings`

Update the payout method for the logged in user.
Parameters:
- `method` (string, required)

Example request:

```bash
curl -X POST -H 'X-WP-Nonce: <nonce>' -d 'method=stripe' \
  '/wp-json/artpulse/v1/user/payouts/settings'
```

Refer to the [Financial Overview & Payout Codex](financial-overview-codex.md) for reporting and payout processing.

### `GET /artpulse/v1/org/{id}/webhooks`

List registered webhooks for the organization.

### `POST /artpulse/v1/org/{id}/webhooks`

Create a new webhook. Parameters include `url`, `events[]` and optional `active`.

### `PUT /artpulse/v1/org/{id}/webhooks/{hid}`

Update an existing webhook. Parameters mirror the POST route.

### `DELETE /artpulse/v1/org/{id}/webhooks/{hid}`

Remove a webhook.

Example create request:

```bash
curl -X POST -H 'X-WP-Nonce: <nonce>' \
  -d 'url=https://example.com/webhook&events[]=ticket_sold' \
  '/wp-json/artpulse/v1/org/2/webhooks'
```

See the [Webhook Automations Codex](webhook-automation-codex.md) for event payloads and retry logic.


### `GET /artpulse/v1/recommendations`

Provide recommended events, artists or organizations for a user.

Parameters:
- `type` (string, required) – item category to recommend
- `user_id` (int, required)
- `limit` (int, default `5`)

Example request:

```bash
curl '/wp-json/artpulse/v1/recommendations?type=events&user_id=7&limit=3'
```

Example response:

```json
[
  {
    "id": 15,
    "title": "Emerging Artists Exhibit",
    "permalink": "https://example.com/events/emerging-artists-exhibit"
  },
  {
    "id": 27,
    "title": "Pop Art Retrospective",
    "permalink": "https://example.com/events/pop-art-retrospective"
  }
]
```
