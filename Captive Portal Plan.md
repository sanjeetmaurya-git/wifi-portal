# 📶 Industry-Level SaaS Captive Portal Master Plan

## 🎯 **SaaS Standard Architecture**
To build a highly reliable, multi-router SaaS portal where users are identifies by MAC address, undergo a one-time KYC (registration), and are granted internet automatically for 15 days after verification. 

---

## 🏗️ **SYSTEM LOGIC FLOW (Graph)**

```mermaid
graph TD
    A[WiFi Connect] --> B{MAC Identified?}
    B -- YES (Auto-Login) --> C{Active Plan & < 15 Days?}
    C -- YES --> D[Auto-Login via API -> Internet ✅]
    C -- NO --> E[Show Plans Selection]
    B -- NO --> F[Redirect to Mobile Number UI]
    F --> G{Registered?}
    G -- NO --> H[Show KYC Form: Name, Address, etc.]
    H --> I[Verify OTP -> Create Profile]
    G -- YES --> J[Verify OTP -> Update MAC]
    I --> J
    J --> E
    E --> K[Razorpay Payment]
    K --> L[Success -> API Auth -> Internet ✅]
    L --> M[Forensic Log (14 Months)]
```

---

## 🛠️ **ADMIN SUPER-POWERS (The Dashboard)**
*   **Plan Engine:** Admin can Create, Update, and Delete all WiFi Plans (Price, Time, Speed Limits).
*   **User Manager:** Manage all KYC entries (Name, Address, City) and block/unblock users.
*   **Live Analytics:** Real-time data on Transactions, Active Sessions, and Bandwidth usage.
*   **Compliance Exporter:** Export session history (Forensic data) to PDF/Excel for law enforcement.

---

## 📁 **TECHNICAL ASSETS**

### **MikroTik `login.html` (Perfect Pop-up Code)**
Upload this to your MikroTik `hotspot/` folder:
```html
<html><head><title>Connecting...</title>
<meta http-equiv="refresh" content="0;url=http://192.168.88.94:8000/login?mac=$(mac)&ip=$(ip)&link_login=$(link-login)"></head>
<body>Redirecting to Login Portal...</body></html>
```

---

## 🏁 **STATUS & PHASES**

### **Phase 1: Connectivity & Payment (COMPLETED ✅)**
*   [x] Router API Setup (TcpTestSucceeded: True).
*   [x] Razorpay Integration Successful.
*   [x] Basic Backend Service (MikrotikService).

### **Current Phase: 2. SaaS Intelligence & KYC (IN PROGRESS ⏳)**
*   [ ] Migration to add KYC Fields (Name, Address, etc.).
*   [ ] `AuthController` 15-Day Logic rewrite.
*   [ ] Admin Panel: Plan CRUD (Create, Read, Update, Delete).

### **Phase 3: Forensic & Launch (PENDING 🚀)**
*   [ ] 14-Month Session Log Auto-Cleanup.
*   [ ] Public Domain Deployment (Cloud).

---
✍️ *SaaS Captive Portal — Master Plan. Updated: 2026-04-06*
