# REST Endpoints for Dashboard Settings

## GET /wp-json/artpulse/v1/dashboard-config
Returns:
{
  widget_roles: { widgetId: [roles] },
  locked: [widgetIds]
}

## POST /wp-json/artpulse/v1/dashboard-config
Requires:
- X-WP-Nonce
- manage_options capability

Payload:
{
  widget_roles: { widgetId: [roles] },
  locked: [widgetIds]
}

## GET /wp-json/artpulse/v1/widgets
Returns an array of registered widgets with settings schema.

## GET /wp-json/artpulse/v1/roles
Returns a list of available WordPress role keys.

## GET /wp-json/artpulse/v1/layout/{role}
Fetch the default layout for a role.

## POST /wp-json/artpulse/v1/layout/{role}
Save the default layout for a role. Requires X-WP-Nonce and `manage_options`.
