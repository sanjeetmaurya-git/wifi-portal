# 📶 WiFi Captive Portal — Full Development Plan
*Updated: April 2026 | Laravel + MikroTik RouterOS API*

---

## ✅ COMPLETED STEPS (Steps 1–26)

Steps 1–26 are **done** and working. These include:
- OTP-based login + 15-day MAC auto-login
- KYC registration (Name, Address, ID Proof, Consent)
- Razorpay payment integration
- MikroTik RouterOS API for user creation, session management, profile limits
- Admin dashboard with analytics, revenue, session management
- Terminate All Sessions button
- Plan queuing (second plan activates after first expires)

---

## 🔧 CURRENT BUG FIX (Step 27)

### Step 27: Fix "User Can't Browse After Payment"
**Root Cause**: After payment, the user is on the correct `/activate-internet` page
but the MikroTik handshake form submits to `wifi.local` which fails on some devices.

**Checklist (do all of these)**:
- [ ] **A** `APP_URL=http://192.168.88.88:8000` in `.env` ✅ Fixed
- [ ] **B** `MIKROTIK_CONNECTED=true` in `.env` ✅ Confirmed
- [ ] **C** In Winbox → **IP → Hotspot → Walled Garden** → Add:
  - Dst. Host: `192.168.88.88` → Action: `allow`
- [ ] **D** In Winbox → **IP → Services** → `api` (port 8728) = **Enabled**
- [ ] **E** In Winbox → **System → Users** → `apiuser`, password = `Typeone@1230`
- [ ] **F** Windows Firewall → Allow port `8000` for **PHP** (both private + public)
- [ ] **G** After all changes: Restart server & run `php artisan config:clear`

---

## 🚀 NEW FEATURES — Step 28 to 35

---

### Step 28: Three Plan Types System ✅ (DB + Model + Controller Done)

Three distinct plan categories:

| Type | Name | Behaviour |
|------|------|-----------|
| `daily` | Daily Data Plan | X MB per day. Resets at midnight. Valid for N days. |
| `unlimited` | Unlimited Plan | No data cap. Speed limited. Valid for N days. |
| `datapack` | Data Pack (Top-Up) | One-time MB boost. Stacks ON an active daily plan. |

**Rules**:
- Two `daily` plans **cannot** run together → second one queues after first expires.
- Two `unlimited` plans **cannot** run together → queued same way.
- A `datapack` **always activates immediately** and adds bonus MB to the current daily plan.
- A `datapack` can only be purchased if a `daily` plan is active.

**DB Changes** (already migrated):
```
wifi_plans.plan_type         ENUM('daily','unlimited','datapack')
wifi_plans.daily_data_mb     INT nullable — MB per day for daily plans
wifi_plans.description       VARCHAR nullable
wifi_sessions.parent_session_id  FK to wifi_sessions (for datapacks)
wifi_sessions.bonus_data_mb  INT — extra MB from datapack
wifi_sessions.used_mb        INT — MB consumed (synced from MikroTik)
```

---

### Step 29: Daily Data Reset Cron (Midnight Reset for Daily Plans)

For `daily` plans, MikroTik's `limit-bytes-total` is set to the **daily** allowance (e.g. `1000M` for 1GB/day).
Each midnight, we must **reset the counter** on MikroTik for all active daily plan users.

**File**: `app/Console/Commands/ResetDailyDataLimits.php`

```php
// Pseudocode
foreach (ActiveDailySessions as $session) {
    $mikrotik->resetUserCounters($session->user->mobile);
    // This resets bytes-used to 0, so the daily allowance refreshes
}conin 
```

**Schedule** (`app/Console/Kernel.php` or `routes/console.php`):
```php
Schedule::command('wifi:reset-daily-data')->dailyAt('00:00');
```

**Status**: ⬜ TODO

---

### Step 30: Data Limit Notification (Browser/Phone Notification)

When a user exhausts their daily data limit, MikroTik cuts off their access and redirects to the captive portal login page. Instead of showing a generic "Login" page, detect this case and show a **"Data Exhausted"** page.

**How MikroTik signals data limit reached**:
When the user hits the limit, MikroTik bounces them back to the login URL with the error parameter. Detect this in `AuthController::loginPage()`.

**Implementation**:
```php
// In loginPage() — detect the MikroTik 'error' param
$error = $request->input('error');
if ($error === 'Traffic limit reached') {
    return view('data-exhausted', [
        'mobile' => $user->mobile,
        'plan'   => $activeSession->plan,
    ]);
}
```

**`data-exhausted.blade.php`**: A premium page that:
- Shows "📵 Your daily data limit is reached!"
- Shows current plan details (remaining days, plan name)
- Shows a **"Buy Data Pack →"** button that goes to plans page filtered for datapacks
- Shows time until midnight reset (countdown timer)

**Status**: ⬜ TODO

---

### Step 31: Usage Dashboard Page (`/usage`)

A data usage tracking page accessible after login showing:

| Section | Details |
|---------|---------|
| Active Plan | Plan name, type, validity countdown |
| Data Used Today | Progress bar: X MB / Y MB used |
| Total Data Used | All-time usage across all sessions |
| Queued Plan | Shows next plan (if queued) |
| Data Pack Add-ons | Any active datapacks |

**Route**: `GET /usage` → `UserController::usagePage()`

**Data source**:
- `wifi_sessions.used_mb` (synced via cron from MikroTik `/ip/hotspot/active` `bytes-in+bytes-out`)
- Show daily progress bar for `daily` plans

**Status**: ⬜ TODO

---

### Step 32: Plans Page — Plan Type Filtering

Update the `/plans` page (`resources/views/plans.blade.php`) to:
- Show plans grouped by type: **Daily Plans | Unlimited | Data Packs**
- Show a **"Data Pack" section only if user has an active daily plan**
- Disable datapack buy button if no active daily plan (show tooltip)

**Status**: ⬜ TODO

---

### Step 33: Admin Plan Creator — Support All Plan Types

Update `admin/plans/create.blade.php` to:
- Add a **Plan Type** dropdown: `Daily | Unlimited | Data Pack`
- Show **Daily Data (MB/Day)** field only when `Daily` is selected
- Show **Total Data (MB)** field for `Data Pack`
- Auto-set `profile_name` based on plan type + ID

**Status**: ⬜ TODO

---

### Step 34: Data Usage Sync Cron

Every 5 minutes, sync the live `bytes-in + bytes-out` from MikroTik for all active sessions.

```php
// app/Console/Commands/SyncUsageStats.php (already exists)
// Update used_mb in wifi_sessions from MikroTik /ip/hotspot/active
$active = $mikrotik->getActiveUsers();
foreach ($active as $row) {
    $bytesTotal = ($row['bytes-in'] ?? 0) + ($row['bytes-out'] ?? 0);
    $mb = round($bytesTotal / 1024 / 1024, 2);
    WifiSession::where('user_id', ...)->update(['used_mb' => $mb]);
}
```

**Schedule**: Every 5 minutes
**Status**: ⬜ TODO (command exists, needs enhancement)

---

### Step 35: "Data Limit Reached" MikroTik Walled Garden Config

In Winbox, ensure MikroTik redirects the user back to the portal when data is exhausted:

1. **IP → Hotspot → Server Profiles → [your profile]**
   - Login By: HTTP PAP
   - On-Login: (empty)
   - Login Page: `http://192.168.88.2:8000/login`

2. **IP → Hotspot → Walled Garden**
   - Add entry: Dst. Host = `192.168.88.2` → Allow (so portal is reachable without internet)

3. **IP → Hotspot → User Profiles**
   - Set `limit-bytes-total` for each plan profile
   - When limit is hit, MikroTik disconnects and redirects to login page

---

## 📋 IMMEDIATE ACTION CHECKLIST

| Priority | Task | Status |
|----------|------|--------|
| 🔴 Critical | Add `192.168.88.88` to Walled Garden in Winbox | ⬜ |
| 🔴 Critical | Enable API service in Winbox (IP → Services → api) | ⬜ |
| 🔴 Critical | Verify `apiuser` password = `Typeone@1230` in Winbox | ⬜ |
| 🔴 Critical | Allow port 8000 in Windows Firewall | ⬜ |
| 🟡 High | Create admin plan creator form for plan_type | Step 33 |
| 🟡 High | Build `/usage` dashboard page | Step 31 |
| 🟡 High | Build `data-exhausted.blade.php` | Step 30 |
| 🟢 Medium | Daily data reset cron at midnight | Step 29 |
| 🟢 Medium | 5-min usage sync cron | Step 34 |
