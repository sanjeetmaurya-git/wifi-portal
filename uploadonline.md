# 🌐 Hosting Guide: Moving Portal to WHM Subdomain

To move your portal from your local PC to a live WHM/cPanel server, follow this step-by-step plan.

---

## 1. Server Setup (WHM/cPanel)
1.  **Create Subdomain:** In cPanel, go to "Domains" and create a subdomain (e.g., `wifi.yourdomain.com`).
2.  **Upload Files:**
    *   Compress your local `wifi-portal` folder (except `vendor` and `node_modules`).
    *   Upload and extract it to the subdomain's folder (usually `public_html/wifi`).
3.  **Database:**
    *   In cPanel, create a new MySQL Database and User.
    *   Export your local XAMPP database (SQL format) and import it via **phpMyAdmin** on the server.
4.  **Composer:** Run `composer install --no-dev` via Terminal (or SSH) in the project folder.
5.  **Public Folder:** Ensure your subdomain's **Document Root** points to the `/public` folder of the Laravel project.

---

## 2. MikroTik Connection (The "Bridge")
Since your server is now on the internet, it needs to reach your router at your location.

1.  **Static IP or DDNS:** Since your Jio IP changes, you **must** use a Dynamic DNS (DDNS) on your MikroTik.
    *   Go to **IP -> Cloud**. Check "DDNS Enabled".
    *   Note the "DNS Name" (e.g., `123456.sn.mynetname.net`). Use this as `MIKROTIK_HOST` in `.env`.
2.  **Port Forwarding (CRITICAL):**
    *   On your **Jio Router**, forward port **8728** (MikroTik API) to your MikroTik's local IP.
    *   This allows the online WHM server to send "Add User" commands to your local router.
3.  **Walled Garden:**
    *   In MikroTik **IP -> Hotspot -> Walled Garden**, add your subdomain:
        *   `Action: Allow` | `Dst. Host: wifi.yourdomain.com`
    *   This allows the user's phone to reach your online portal even before they have internet.

---

## 3. Update `.env` on Server
Modify the `.env` file on your WHM server with these values:

```env
APP_URL=https://wifi.yourdomain.com
MIKROTIK_HOST=your_ddns_name_from_step_2 (e.g. 12345.sn.mynetname.net)
MIKROTIK_HOTSPOT_IP=192.168.29.162 (Keep this as the local router IP)

# Database
DB_HOST=localhost
DB_DATABASE=your_cpanel_db_name
DB_USERNAME=your_cpanel_db_user
DB_PASSWORD=your_cpanel_password

# Session Management (Crucial for online)
SESSION_DRIVER=database
SESSION_DOMAIN=yourdomain.com
```

---

## 4. Final MikroTik Adjustment
On your MikroTik router, update your **Server Profile** (`hsprof1`):
1.  **Login Page URL:** Change it to `https://wifi.yourdomain.com`.
2.  **Walled Garden:** Check again that the subdomain is allowed.

---

## 5. Security Checklist
*   [ ] **SSL Certificate:** Use "AutoSSL" or "Let's Encrypt" in WHM to ensure the portal is `https`.
*   [ ] **API Password:** Change your `apiuser` password in MikroTik to something very strong, as it is now exposed to the internet.
*   [ ] **Firewall:** In MikroTik, limit API access (8728) only to the IP address of your WHM server for maximum security.

---
