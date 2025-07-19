# ✅ Widget Matrix Verification Checklist

Access the matrix at **ArtPulse → Settings → Widget Matrix** (slug
`artpulse-widget-matrix`).

Verification is complete and all items have been tested.

## Admin UI
- [x] Loads matrix from config
- [x] Displays all widgets and roles
- [x] Allows toggling roles for widgets
- [x] POSTs updated config via REST
- [x] Shows confirmation message

## Dashboard Behavior
- [x] Widgets display correctly per user role
- [x] Removed widgets no longer appear
- [x] New widgets appear after update
- [x] Layout adapts to widget removal
- [x] Locked widgets cannot be removed

## API Behavior
- [x] GET returns correct role matrix
- [x] POST stores updated config
- [x] Invalid roles/widgets ignored

## Edge Case Tests
- [x] Role with zero widgets
- [x] All widgets enabled
- [x] Bad/missing matrix config
