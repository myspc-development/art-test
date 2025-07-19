# REST Endpoints for Dashboard Settings

## GET /wp-json/artpulse/v1/dashboard-config
Returns:
{
  roles: { widgetId: [roles] },
  locked: [widgetIds]
}

## POST /wp-json/artpulse/v1/dashboard-config
Requires:
- X-WP-Nonce
- manage_options capability

Payload:
{
  roles: { widgetId: [roles] },
  locked: [widgetIds]
}
