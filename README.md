# 🏗️ Structor Design - Construction Project Management

**Structor Design** is a comprehensive, Laravel-based system built to manage site-level construction projects with precise tracking of materials, expenses, payments, labour attendance, and financial ledgers. Designed for collaboration between Admins, Site Engineers, Site Signers, and Clients, the platform offers real-time project tracking and detailed reporting via downloadable PDFs.

---

## 🧱 Project Structure & Design

The platform is structured around **Sites**, each representing a full construction project owned by a client.

### 🔹 Site-Level Management
- Each **Site** contains:
  - **Site Name** and **Site Owner**
  - Assigned **Site Engineers**
  - Optional **Site Signers** for limited data operations

### 🔸 Phase Management
- Sites are divided into **Phases**, such as foundation, framing, finishing, etc.
- Each Phase tracks:
  - 🧱 **Materials Used**
  - 💸 **Daily Expenses**
  - 💰 **Payments** to/from **Suppliers**
  - 👷 **Labour & Wasta Attendance**
  - 📊 **Financial Ledgers**, including balance calculations

### 🧾 PDF Reports & Export
At any point, you can generate:
- ✅ Ledger Summary PDFs
- ✅ Payment History PDFs
- ✅ Labour Attendance PDFs
- ✅ Complete Site Account Summaries

These are crucial for audits, updates, and client communications.

### 👥 Roles & Permissions

| Role          | Capabilities                                                                 |
|---------------|------------------------------------------------------------------------------|
| **Admin**     | Full access. Manages users, site assignments, financials, and approvals.     |
| **Client**    | View access to their sites, ledger summaries, payments.                      |
| **Site Signer**| Limited operations like data entry or approvals, assigned by the Admin.     |

### 🎨 User Interface
- Built using **Laravel Blade Components** and **Bootstrap 5**
- Clean, responsive UI for easy access across desktop and tablet devices
- Role-specific dashboard interfaces for clarity and access control

---

## ⚙️ Installation & Setup

To get started with the project locally, follow the instructions below:

```bash
# Step 1: Clone the repository
git clone <repository-url>
cd goodguys-platform

# Step 2: Install PHP dependencies
composer install

# Step 3: Copy environment file and generate app key
cp .env.example .env
php artisan key:generate

# Step 4: Run database migrations
php artisan migrate

# Step 5: Create a dummy admin user
php artisan app:create-admin
# Creates an admin user:
# Email: admin@admin.com
# Password: @Admin123

# (Optional) Step 6: Seed database with demo data
php artisan db:seed

# Step 7: Install Node.js dependencies
npm install

# Step 8: Build frontend assets
npm run build
# Or use for live development
npm run dev

# Step 9: Start Laravel server
php artisan serve
Access the project at:
➡️ http://127.0.0.1:8000

📦 Features
✅ Site & Phase Management

✅ Material, Expense & Payment Tracking

✅ Labour & Wasta Attendance

✅ Payment Flows and Ledger Management

✅ PDF Reports for attendance, ledgers, and payment summaries

✅ Role-based Access Control (Admin, Engineer, Signer, Client)

✅ Responsive UI for all devices

✅ Secure Authentication & User Management

🖥️ Tech Stack
Backend: Laravel (PHP)

Frontend: Laravel Blade + Bootstrap 5

Database: MySQL

Build Tools: NPM + Vite


