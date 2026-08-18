# Multi-Tenant Invoice & Billing System

A modern PHP-based multi-tenant invoice and billing management system with multi-role access (Admin, Vendor, Customer), real-time synchronized dashboards, customer management, automated tax calculations, and bill generation.

---

## Features

- **Multi-Role Authentication & Access Control:**
  - **Admin:** System-wide live business analytics, vendor performance tracking, full invoice and customer management.
  - **Vendor:** Tenant-scoped customer management, invoice creation, product billing, and personal revenue reports.
  - **Customer:** Dedicated portal to view and search purchase receipts and invoices.
- **Real-Time Synchronized Dashboard:** Live metrics for Today's Sales, Month's Sales, Year's Sales, Total Revenue, and Top Selling Products.
- **Security Hardened:** HttpOnly/SameSite cookies, CSRF protection, tenant-scoped database queries, and SQL injection prevention via prepared statements.
- **Cloud-Ready:** Preconfigured `Dockerfile` and automated MySQL environment detection for **Railway** and Docker deployments.

---

## Deploy to Railway

### Step 1: Push Code to GitHub

1. Push your project to GitHub:
   ```bash
   git branch -M main
   git remote add origin https://github.com/Lavkush2004/invoice-management-system.git
   git push -u origin main
   ```

### Step 2: Create Railway Project & MySQL Database

1. Log in to [Railway](https://railway.app/).
2. Click **+ New Project** > **Provision MySQL**.
3. Once MySQL is created, open the MySQL service settings, click the **Connect / Query** tab, and run the SQL commands from `database.sql` (or connect using MySQL Workbench / TablePlus with the provided connection string).

### Step 3: Deploy Web Service

1. In the same Railway project, click **+ Create** > **GitHub Repo** > Select your repository.
2. Railway will automatically detect the `Dockerfile` and start building.
3. In your Web Service settings under **Variables**, click **Add Reference** to link the MySQL environment variables (`MYSQLHOST`, `MYSQLUSER`, `MYSQLPASSWORD`, `MYSQLDATABASE`, `MYSQLPORT` or `DATABASE_URL`).
4. Under **Settings** > **Networking**, click **Generate Domain** (e.g. `https://your-app.up.railway.app`).

Your billing application is now live on Railway!
