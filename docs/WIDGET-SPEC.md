# Widget Specification

Each widget must include:
- id: unique string identifier
- title: display name
- component: React component
- roles: array of roles allowed to see the widget

Example:
{
  id: "rsvp-stats",
  title: "RSVP Statistics",
  component: RSVPStats,
  roles: ["organization"]
}
