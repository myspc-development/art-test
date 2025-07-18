# WebSocket Server Deployment

The Node server in this directory powers real‑time messaging. For production
use it should run behind a TLS‑terminating proxy such as Nginx or be adapted
to serve over HTTPS with valid certificates. Exposing the server over plain
HTTP is not recommended.

## Configuration

Create a `.env` file in the project root and define at least:

```bash
JWT_SECRET=change_me_to_a_long_random_string
# PORT=3001
```

`JWT_SECRET` must be ten characters or more. If `PORT` is omitted the server
defaults to `3001`.

When using a reverse proxy, configure the proxy to terminate TLS and forward
WebSocket requests to the port specified by `PORT`. If you update
`ws-server.js` to use HTTPS directly, provide the certificate files to the
server and adjust the client WebSocket URL accordingly.
