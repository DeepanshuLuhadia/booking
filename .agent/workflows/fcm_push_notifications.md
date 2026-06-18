# FCM Push Notification Integration Workflow

## Overview
Integrate Firebase Cloud Messaging (FCM) to send push notifications across the entire booking platform using the service account stored in `storage/app/fcm.json`.

## Architecture

```
Browser (SW) → FCM Token → DB (users.fcm_token)
                                    ↓
Booking Created → FcmService → FCM HTTP v1 API → Device Push
                      ↑
              storage/app/fcm.json (service account)
```

## Notification Triggers

| Event | Recipients | Message |
|---|---|---|
| New Booking (Customer) | Vendor + Employee | "🔔 New booking from {name}" |
| New Booking (Vendor panel) | Employee | "📋 Manual booking assigned" |
| Appointment Reminder | User/Customer | "⏰ Appointment in 1 hour" |
| Vendor Registration | Admin/Vendor | "Welcome to BookAppointment!" |

## Implementation Steps

### Step 1 — Migration: Add `fcm_token` to `users` table
Create a migration to add a nullable `fcm_token` string column to the `users` table.

### Step 2 — Firebase Service Worker & Token Collection (Frontend)
- Create `public/firebase-messaging-sw.js` (service worker for background push)
- Add Firebase SDK initialization in `app-layout.blade.php`
- On page load, request notification permission and save token via POST `/fcm/token`

### Step 3 — FCM Token Save Route & Controller
- `POST /fcm/token` → `FcmTokenController@save` (works for guests too via session; for auth users stores in DB)
- `POST /register/vendor` (VendorRegistrationController) — store token if present in session

### Step 4 — FcmService (Core Push Logic)
- Located at `app/Services/FcmService.php`
- Uses `storage/app/fcm.json` to obtain a Google OAuth2 access token
- Sends FCM HTTP v1 API messages to single tokens or arrays of tokens
- Methods: `sendToToken($token, $title, $body, $data)`, `sendToTokens($tokens, ...)`, `notifyNewBooking(Booking $booking)`, `notifyAppointmentReminder(Booking $booking)`

### Step 5 — Upgrade NotificationService
- Inject `FcmService` and replace the placeholder `sendWebPush` with real FCM calls
- `notifyVendorNewBooking` → sends to vendor user + assigned employee user
- `notifyUserBookingConfirmed` → sends to customer when booking confirmed

### Step 6 — Hook into BookingController (Customer booking)
- After booking created: notify vendor, employee, and customer (if fcm_token set)

### Step 7 — Hook into Vendor BookingController (Manual booking)
- After vendor creates manual booking: notify assigned employee

### Step 8 — Appointment Reminder Console Command
- `app/Console/Commands/SendAppointmentReminders.php`
- Scheduled hourly in `routes/console.php`
- Finds bookings starting within 60 min from now, sends reminder to customer

### Step 9 — Firebase Messaging Config
- Store Firebase project details in `.env`:
  - `FCM_PROJECT_ID=ebooking-b2c07`
  - `FCM_CREDENTIALS_PATH=storage/app/fcm.json`
- Fetch VAPID key from Firebase Console → Project Settings → Cloud Messaging

## Files Modified / Created

| File | Action |
|---|---|
| `database/migrations/..._add_fcm_token_to_users.php` | CREATE |
| `public/firebase-messaging-sw.js` | CREATE |
| `app/Services/FcmService.php` | CREATE |
| `app/Services/NotificationService.php` | UPDATE |
| `app/Http/Controllers/FcmTokenController.php` | CREATE |
| `app/Http/Controllers/BookingController.php` | UPDATE |
| `app/Http/Controllers/Vendor/BookingController.php` | UPDATE |
| `app/Console/Commands/SendAppointmentReminders.php` | CREATE |
| `routes/web.php` | UPDATE (add /fcm/token route) |
| `routes/console.php` | UPDATE (schedule reminder command) |
| `resources/views/components/app-layout.blade.php` | UPDATE (add FCM JS init) |
| `.env` | UPDATE (add FCM vars) |
| `app/Models/User.php` | UPDATE (add fcm_token fillable) |

## Security Notes
- FCM tokens stored per-user in DB; updated on each login/page load
- Service worker registered only over HTTPS in production (localhost works in dev)
- `fcm.json` is in `storage/app/` (not public-facing) — safe
- Token endpoint is CSRF-protected
