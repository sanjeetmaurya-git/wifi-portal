# 🚀 Step-by-Step Guide: Making pmwani.typeone.in Live with MikroTik

Since you have already uploaded the files to the server, follow these exact steps to connect your live portal and fix the **419 Page Expired** error.

---

## 1. Prepare Your MikroTik (Physical Location)
Because your server is now on the internet (`pmwani.typeone.in`), it needs a "bridge" to reach the router in your shop/home.

### A. Enable DDNS (If you don't have a Static IP)
If your Jio/Airtel IP changes, the server will lose connection. Use MikroTik's free DDNS:
1.  Open **Winbox**.
2.  Go to **IP -> Cloud**.
3.  Check **DDNS Enabled**.
4.  Click **Apply**.
5.  **Copy the "DNS Name"** (e.g., `123456789abc.sn.mynetname.net`). This is your new `MIKROTIK_HOST`.

### B. Port Forwarding (Very Important)
Your local router (Jio Fiber / Airtel ONT) blocks incoming connections by default. You must open a "hole" for the server:
1.  Log in to your **Primary Router** (the one the MikroTik is plugged into).
2.  Go to **Port Forwarding / Virtual Server**.
3.  Add a rule:
    *   **External Port:** 8728
    *   **Internal Port:** 8728
    *   **Internal IP:** (The local IP of your MikroTik router, e.g., `192.168.29.50`)
    *   **Protocol:** TCP

### C. Enable MikroTik API
1.  In Winbox, go to **IP -> Services**.
2.  Ensure **api** (port 8728) is **enabled** (not greyed out).
3.  (Optional but recommended) In the `Available From` field, put the IP address of your WHM server for security.

---

## 2. Server Configuration (Live WHM Server)
Log in to your WHM/cPanel File Manager and edit the `.env` file manually.

---

## 3. SaaS Mode: The "No Port Forwarding" Method (Recommended)
If you are using **Jio Fiber** or **Airtel**, they often block port forwarding. To fix this, we use a **Polling Script**.

### A. Run Migration on Server
Ensure your database has the command queue table:
```bash
php artisan migrate
```

### B. Add Scheduler to MikroTik
1. Open Winbox -> **System** -> **Scheduler**.
2. Create a new task:
   - **Name:** `saas_pull`
   - **Interval:** `00:00:05` (Runs every 5 seconds)
   - **On Event:**
     ```routeros
     /tool fetch url="https://pmwani.typeone.in/api/router/fetch-commands" mode=https keep-result=yes dst-path=commands.rsc;
     :if ([:len [/file find name=commands.rsc]] > 0) do={
         :if ([:len [/file get commands.rsc contents]] > 0) do={
             /import commands.rsc;
         }
         /file remove commands.rsc;
     }
     ```

### C. Why this is better?
- **Plug & Play:** You can take this MikroTik to ANY home or office, and it will work instantly.
- **Security:** You don't need to expose Port 8728 to the public internet.
- **Scalability:** You can manage 1,000 routers this way.

---

## 4. Fix "419 Page Expired" Error
If you get a 419 error on login, update your live `.env` file:
```env
SESSION_DOMAIN=pmwani.typeone.in
SESSION_SECURE_COOKIE=true
SESSION_DRIVER=file
```
Then run: `php artisan config:clear`

```env
APP_NAME="PM-WANI Portal"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://pmwani.typeone.in

# 🔑 Session Fixes (Changes these exactly)
SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_DOMAIN=pmwani.typeone.in   # CHANGE THIS from your IP to your live domain
SESSION_SECURE_COOKIE=true         # SET TO TRUE for HTTPS
SESSION_SAME_SITE=lax

# 🌐 MikroTik Connection
MIKROTIK_CONNECTED=true
MIKROTIK_HOST=your_ddns_name_from_step_1A
MIKROTIK_USER=apiuser
MIKROTIK_PASS=YourStrongPassword
MIKROTIK_PORT=8728
MIKROTIK_HOTSPOT_IP=192.168.88.1

# 📁 Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_cpanel_db_name
DB_USERNAME=your_cpanel_db_user
DB_PASSWORD=your_cpanel_db_pass
```

### B. Final Steps via Terminal/SSH
After saving `.env`, run these commands in your project folder:
1.  `php artisan config:clear` (To apply new .env settings)
2.  `php artisan migrate --force` (To ensure the `sessions` table exists)

---

## 3. Redirecting MikroTik to Live Portal
Now, tell your MikroTik to send users to the new online URL.

1.  In Winbox, go to **IP -> Hotspot -> Server Profiles**.
2.  Open your profile (e.g., `hsprof1`).
3.  Set **Login Page URL** to: `https://pmwani.typeone.in/login`
4.  Go to **Walled Garden** (IP -> Hotspot -> Walled Garden).
5.  Add a new entry:
    *   **Action:** allow
    *   **Dst. Host:** pmwani.typeone.in
    *   (This ensures users can reach the payment page before they have internet).

---

## 4. Answering Your Questions

### Why 419 error on live?
It is because your `SESSION_DOMAIN` in `.env` was likely set to your local IP (192.168...). When the browser visits `pmwani.typeone.in`, it refuses to save the session cookie because the domains don't match. No session = No CSRF token = **419 Error**.

### Is RADIUS needed?
NO. Stick with the API for now. It is more stable for this scale.

---

## 5. How to Test
Once configured, visit:
👉 `https://pmwani.typeone.in/test-mikrotik`

---

## 6. Network Topology (How it works)

To ensure your private Jio users are not disturbed, use this exact physical connection:

1.  **INTERNET IN:** Jio Fiber LAN Port → MikroTik **Ether1 (WAN)**.
2.  **HOTSPOT OUT:** MikroTik **Ether2 (LAN)** → Netis Router / Access Point.

### Results:
*   **Users on Netis WiFi:** Will be redirected to `pmwani.typeone.in` for OTP and Payment.
*   **Users on Jio WiFi:** Will have direct internet. They will **NOT** see your portal. This is completely safe and isolated.

### Why this works on Live:
When a user pays on `pmwani.typeone.in`, the server sends the command to your Jio Public IP. Your Jio router (because of the Port Forwarding you did in Step 1B) sends that command to the MikroTik. The MikroTik then unlocks the internet for that specific user on the Netis WiFi.

---

## Next Steps
1.  Follow **Step 1B** (Port Forwarding) first.
2.  Update the **.env** file on your live server.
3.  Check the **test-mikrotik** link.
4.  Once green, try connecting a phone to the WiFi!
