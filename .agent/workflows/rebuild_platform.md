---
description: Rebuild and Fix Platform Functionality
---

# Platform Rebuild Workflow

### 1. Fix Authentication & Registration
- [ ] Implement `CustomerRegistrationController@create` and `store`.
- [ ] Create `resources/views/auth/register.blade.php`.
- [ ] Debug and Fix `VendorRegistrationController` flow.

### 2. Redesign User Experience (Home Page)
- [ ] Set `/` to use `CustomerDiscoveryController@index`.
- [ ] Redesign `welcome.blade.php` (or move discovery logic there) to look like an e-commerce vendor listing page.
- [ ] Ensure "Register as Vendor" and "Login" are easily accessible.

### 3. Implement Admin Panel Modules
- [ ] **Vendors Module**: List, view, approve/reject vendors.
- [ ] **Plans Module**: CRUD for `SubscriptionPlan`.
- [ ] **Settlement Module**: List and manage payouts.
- [ ] Fix Edit/Delete buttons on Admin Dashboard.

### 4. Vendor Dashboard Verification
- [ ] Check `Vendor\DashboardController`.
- [ ] Ensure navigation between employees, bookings, and profile works.

### 5. Full Testing
- [ ] Test Customer registration & login.
- [ ] Test Vendor registration & activation.
- [ ] Test Booking flow (Normal & Emergency).
- [ ] Test Admin management tasks.
