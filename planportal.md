# 📘 PLANPORTAL — WiFi Captive Portal Project Blueprint
**Laravel 12 | MikroTik + TP-Link | PM-WANI PDOA**

---

## 🔴 CURRENT CRITICAL BUG
**Problem:** User enters mobile → verifies OTP → redirected to `/plans` page.
**In WinBox IP → Hotspot → Active: NO ENTRY SHOWN.**
**Result: User has NO internet access.**

---

## 🧠 ROOT CAUSE ANALYSIS (Code Audit)

### The Authentication Flow — What SHOULD happen:
```
User connects WiFi
    ↓
MikroTik intercepts and redirects to:
    http://your-portal/login?mac=XX:XX:XX&ip=192.168.x.x&link-login=http://192.168.88.1/login
    ↓
User enters mobile → OTP → verifyOtp()
    ↓
Portal calls MikroTik API → adds user to /ip/hotspot/user/
    ↓
Portal sends POST to MikroTik's link-login URL (NOT a GET redirect)
    ↓
MikroTik creates Active Session
    ↓
User gets internet ✅
```

### Why Winbox Active Tab Shows NOTHING — 3 Reasons Found:

**Bug #1 — GET Redirect is WRONG for MikroTik (CRITICAL)**
```php
// ❌ CURRENT CODE (BROKEN) — line 311 in AuthController.php
return redirect($linkLogin . '?username=' . $request->mobile . '&password=' . $request->mobile);
```
MikroTik Hotspot login REQUIRES a **POST** request to its login URL.
A simple GET redirect will NOT create an active session in Winbox.
The router's internal firewall only authenticates users via POST form submission.

**Bug #2 — Password is WRONG (IMPORTANT)**
```php
// ❌ WRONG: Using mobile number as password
addHotspotUser($mobile, $mobile, 'default'); // password = mobile number

// ✅ CORRECT: Router user password must MATCH what you send in the login form
// Both must be the SAME value. Using mobile as both username AND password is OK
// BUT you must ensure the user EXISTS in /ip/hotspot/user/ BEFORE login attempt
```

**Bug #3 — `WifiSession::create()` called but `expires_at` is NULL**
```php
// ❌ PaymentController line 96-104 — expires_at is MISSING!
WifiSession::create([
    'user_id' => $user->id,
    'mac_address' => session('mac'),
    // ... 
    // ❌ 'expires_at' is not set! Status card shows broken timer.
]);
```

---

## ✅ THE FIX PLAN (Step by Step)

### FIX 1: Restore `mikrotik-login.blade.php` (MOST CRITICAL)
The file was deleted by mistake. We MUST use a hidden HTML form
that auto-submits via POST — NOT a PHP redirect.

**File to recreate:** `resources/views/mikrotik-login.blade.php`

### FIX 2: Restore POST login in `verifyOtp()` in AuthController.php
Replace the broken GET redirect with the hidden form view.

### FIX 3: Add `expires_at` to `WifiSession::create()` in PaymentController.php
Without this, the status/timer page breaks and sessions are "never active".

### FIX 4: Ensure `addHotspotUser()` is called BEFORE the POST login form
The user must exist in `/ip/hotspot/user/` BEFORE MikroTik will accept login.

---

## 🏗️ COMPLETE SYSTEM ARCHITECTURE

```
[User Device]
     │  connects to WiFi
     ▼
[MikroTik Router]
     │  intercepts, blocks internet, redirects to:
     │  http://portal/login?mac=..&ip=..&link-login=http://192.168.88.1/login
     ▼
[Laravel Portal — loginPage()]
     │  checks WifiSession by MAC
     │  if active session → shows timer/status card
     │  else → shows login form
     ▼
[User — enters mobile, clicks Send OTP]
     │
     ▼
[sendOtp()] → saves OTP to DB with 5min expiry
     │
     ▼
[User — enters OTP]
     │
     ▼
[verifyOtp()]
     │  1. Validates OTP from DB
     │  2. Creates/finds WifiUser
     │  3. Calls MikrotikService::addHotspotUser() — adds to /ip/hotspot/user/
     │  4. Renders mikrotik-login.blade.php (hidden POST form)
     │     — form auto-submits to MikroTik's link-login URL
     │     — MikroTik creates Active session → Winbox shows entry ✅
     │     — MikroTik redirects user to internet ✅
     ▼
[For Paid Plans — PaymentController::paymentSuccess()]
     │  1. Verifies Razorpay signature
     │  2. Creates WifiSession with correct expires_at
     │  3. Calls MikrotikService::addHotspotUser()
     │  4. Renders mikrotik-login.blade.php with session('link_login')
     ▼
[User has internet ✅]
```

---

## 📁 KEY FILES

| File | Role | Status |
|:-----|:-----|:-------|
| `AuthController.php` | OTP login + auto-login | ⚠️ Needs fix |
| `PaymentController.php` | Razorpay + session creation | ⚠️ Missing expires_at |
| `MikrotikService.php` | Router API wrapper | ✅ OK |
| `mikrotik-login.blade.php` | Hidden POST form for router | ❌ DELETED — Recreate |
| `resources/views/status.blade.php` | Timer card | ✅ OK |

---

## 📅 DEVELOPMENT PHASES

### Phase 1 — CURRENT: Fix Internet Access Bug
- [ ] Recreate `mikrotik-login.blade.php`
- [ ] Fix POST redirect in `verifyOtp()`
- [ ] Fix `expires_at` in `WifiSession::create()` in PaymentController
- [ ] Store `link_login` in session during OTP verify
- [ ] Test: Winbox Active tab must show entry after OTP

### Phase 2 — Plans & Payment Flow
- [ ] After plan purchase, trigger MikroTik POST login
- [ ] Timer card shows correct remaining time
- [ ] Disconnect WiFi button works

### Phase 3 — Multi-Router Support
- [ ] Create `RouterInterface` contract
- [ ] Wrap `MikrotikService` behind it
- [ ] Add `TPLinkService` using Omada Controller API

### Phase 4 — PM-WANI Integration
- [ ] C-DOT Central Registry API sync
- [ ] 1-year session log retention
- [ ] PDO dashboard for shop owners

---

## ⚙️ ENVIRONMENT VARIABLES (.env)
```env
MIKROTIK_CONNECTED=true          # Set to true when real router is connected
MIKROTIK_HOST=192.168.88.1       # Router LAN IP
MIKROTIK_USER=apiuser
MIKROTIK_PASS=yourpassword
MIKROTIK_PORT=8728

RAZORPAY_KEY=rzp_test_xxx
RAZORPAY_SECRET=xxx
```
