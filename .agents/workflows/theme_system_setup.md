---
description: Setup and maintain the Role-Based Theme Engine and Frontend Upgrade
---

This workflow outlines the steps to implement or rebuild the dynamic theme system across the platform.

### 1. Database Schema Preparation
Ensure the `vendors` table has the `vendor_type` column for role identification.
// turbo
```bash
php artisan migrate
```

### 2. Implementation of Centralized Theme Engine
The `App\Services\ThemeService` must define the configurations for all roles.
- Ensure `ThemeService.php` contains the `themes` array with roles: `doctor`, `salon`, `sports`, `training`, `consultancy`.
- Each theme should define `primary`, `hero_gradient`, `icon`, and role-specific labels.

### 3. CSS Design System Setup
Compile the global CSS with role-based variables.
- Update `resources/css/app.css` to include the `@theme` blocks and the `body.theme-{role}` classes.
- Ensure CSS properties are bound to `--theme-primary` and related variables.

### 4. Layout Integration
Inject the theme data into the global Blade layout.
- Update `resources/views/components/app-layout.blade.php` to accept a `vendorTheme` prop.
- Use `\App\Services\ThemeService::getCssVars($vendorTheme)` to inject styles into the `<head>`.

### 5. Frontend View Refactoring
Refactor the customer-facing views to use the theme system.
- **Listing Page**: Update `customer/vendors.blade.php` to use role badges and themed cards.
- **Detail Page**: Update `customer/vendor-details.blade.php` to adapt terminology and layout based on the active role.

### 6. Booking System Adaptive Logic
Ensure the UI adapts to the vendor's chosen booking method.
- Use conditional Blade/Alpine.js templates to switch between **Time Slots** and **Token Queue** displays.

### 7. Validation
Run the automated test suite to ensure no breakage.
// turbo
```bash
php artisan test tests/Feature/ThemeSystemTest.php
```
