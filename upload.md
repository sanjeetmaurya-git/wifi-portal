# 🚀 Step-by-Step Guide: Making pmwani.type.in Live with MikroTik

Since you have already uploaded the files to the server, follow these exact steps to connect your live portal to your physical MikroTik router.

---

## 1. Prepare Your MikroTik (Physical Location)
Because your server is now on the internet (`pmwani.type.in`), it needs a "bridge" to reach the router in your shop/home.

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

### A. Update `.env` File
```env
APP_NAME="PM-WANI Portal"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://pmwani.type.in

# 🌐 MikroTik Connection
MIKROTIK_CONNECTED=true
MIKROTIK_HOST=123456789abc.sn.mynetname.net  # Use your DNS Name from Step 1A
MIKROTIK_USER=apiuser                       # Ensure this user exists in System -> Users
MIKROTIK_PASS=YourStrongPassword
MIKROTIK_PORT=8728
MIKROTIK_HOTSPOT_IP=192.168.88.1            # Your MikroTik's local IP for users

# 📁 Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_cpanel_db_name
DB_USERNAME=your_cpanel_db_user
DB_PASSWORD=your_cpanel_db_pass

# 🔑 Session (Must use database to prevent loops)
SESSION_DRIVER=database
SESSION_LIFETIME=120
```

### B. Run Database Migrations
Go to your cPanel Terminal or SSH and run:
```bash
php artisan migrate --force
```

---

## 3. Redirecting MikroTik to Live Portal
Now, tell your MikroTik to send users to the new online URL.

1.  In Winbox, go to **IP -> Hotspot -> Server Profiles**.
2.  Open your profile (e.g., `hsprof1`).
3.  Set **Login Page URL** to: `https://pmwani.type.in/login`
4.  Go to **Walled Garden** (IP -> Hotspot -> Walled Garden).
5.  Add a new entry:
    *   **Action:** allow
    *   **Dst. Host:** pmwani.type.in
    *   (This ensures users can reach the payment page before they have internet).

---

## 4. Answering Your Questions

### Q1: What needs changes in server?
*   **SSL:** Ensure your website has an SSL certificate (`https`). Use cPanel's **AutoSSL** or Let's Encrypt.
*   **Database:** You must export your local database and import it to the cPanel MySQL database.
*   **Permissions:** Ensure `storage` and `bootstrap/cache` folders are writable (775).

### Q2: Is it need to install RADIUS on server or Winbox?
*   **Winbox:** NO. Winbox is for your laptop to manage the router. Do not install it on the server.
*   **RADIUS:** NO. Your current code (`MikrotikService.php`) is designed to use the **API**. This is much easier for beginners and works perfectly. Only switch to RADIUS if you plan to have 1,000+ simultaneous users.

---

## 5. How to Test
Once configured, go to this URL:
👉 `https://pmwani.type.in/test-mikrotik`

*   If it says **"SUCCESS!"**, your server is talking to your router.
*   If it says **"CONNECTION FAILED"**, double-check your Port Forwarding and DDNS settings.

---

## Next Steps
1.  Follow **Step 1B** (Port Forwarding) first.
2.  Update the **.env** file on your live server.
3.  Check the **test-mikrotik** link.
4.  Once green, try connecting a phone to the WiFi!
