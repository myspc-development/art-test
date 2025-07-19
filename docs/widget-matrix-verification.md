# ✅ Widget Matrix Verification Checklist

## Admin UI
- [ ] Loads matrix from config
- [ ] Displays all widgets and roles
- [ ] Allows toggling roles for widgets
- [ ] POSTs updated config via REST
- [ ] Shows confirmation message

## Dashboard Behavior
- [ ] Widgets display correctly per user role
- [ ] Removed widgets no longer appear
- [ ] New widgets appear after update
- [ ] Layout adapts to widget removal
- [ ] Locked widgets cannot be removed

## API Behavior
- [ ] GET returns correct role matrix
- [ ] POST stores updated config
- [ ] Invalid roles/widgets ignored

## Edge Case Tests
- [ ] Role with zero widgets
- [ ] All widgets enabled
- [ ] Bad/missing matrix config
