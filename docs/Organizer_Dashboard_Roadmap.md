# 🧭 Organizer Dashboard Roadmap

---

## PHASE 1: Setup & Permissions

### ✅ 1. Organization Profiles
- [ ] Custom post type updates for CRM fields
- [ ] REST endpoints secured for managers only

### ✅ 2. Dashboard Skeleton
- [ ] Sidebar menu with tabs for CRM, Donations, Analytics
- [ ] Basic widgets container using existing registry

---

## PHASE 2: CRM & Contacts
- [ ] `CrmContactsWidget.php` lists member interactions
- [ ] `/crm/contacts` REST route with filtering
- [ ] Tagging interface for notes and follow ups

---

## PHASE 3: Donor & Payment Tools
- [ ] `DonationsSummaryWidget.php` displays recent gifts
- [ ] `/donations/overview` REST route
- [ ] Export to CSV for grants reporting

---

## PHASE 4: Analytics & Reports
- [ ] Attendance trends chart
- [ ] Ticket sales and conversion metrics
- [ ] Custom report builder with saved filters

---

## PHASE 5: QA and Documentation
- [ ] PHPUnit coverage for new REST endpoints
- [ ] Widget docs and admin guide updates

## Completion Plan

1. Establish organization permissions and base dashboard widgets.
2. Integrate CRM contact management in Phase 2.
3. Add donor tools and exports in Phase 3.
4. Build analytics and reporting screens during Phase 4.
5. Finalize tests and documentation to wrap up Phase 5.
