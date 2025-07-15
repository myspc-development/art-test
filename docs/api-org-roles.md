# Org Roles REST API

Routes exposed under `artpulse/v1` allow managing organization user roles.

| Route | Method | Description |
|-------|--------|-------------|
| `/orgs/{id}/roles` | GET | List user roles for an organization |
| `/orgs/{id}/roles` | POST | Assign a role to a user (`user_id`, `role`) |
| `/orgs/{id}/roles/{user_id}` | DELETE | Remove a user's role from the org |
| `/users/me/orgs` | GET | List all org assignments for the current user |

Example request:

```bash
curl -X POST -H "X-WP-Nonce: <nonce>" \
  -d 'user_id=5&role=editor' \
  /wp-json/artpulse/v1/orgs/12/roles
```
