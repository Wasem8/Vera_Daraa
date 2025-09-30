# 💅 Vera_Daraa – Beauty Center Management System

A **Laravel 12**-based system to manage a beauty center.  
It provides complete solutions for **Bookings, Invoices, Payments, Employees, Services, Offers, Notifications, and Client Management**.

---

## ✨ Features
- 🔑 User authentication & roles:
    - Admin
    - Receptionist
    - Client
- 💇 Manage services, departments, and offers.
- 📅 Booking system with available slots & cancellations.
- 🧾 Invoice generation, archiving & payments tracking.
- 👩‍💼 Employee management with status/archive toggle.
- 📊 Statistics & financial reports for admins.
- 🔔 Notifications (with Firebase).
- 🗄 Archiving system (bookings & invoices).
- 🤖 AI Face Analysis (experimental feature).

---

## 🛠 Requirements
- PHP >= 8.2
- Laravel 12
- MySQL / MariaDB
- Composer


---

## 🧩 Main Packages

- [Laravel Sanctum](https://laravel.com/docs/12.x/sanctum) – API Authentication
- [Spatie Laravel Permission](https://spatie.be/docs/laravel-permission) – Role & Permission Management
- [Laravel Notification Channels FCM](https://laravel-notification-channels.com/fcm/) – Firebase Push Notifications

---

## 🧱 Architecture & Code Quality
- Follows **SOLID principles** and **Clean Code practices**.
- Built with **Layered Architecture**:
    - **Controllers** → Handle HTTP requests and responses.
    - **Requests** → Form Request classes for validation.
    - **Services** → Contain the business logic.
    - **Repositories** → Handle database queries and encapsulate Eloquent models.
    - **Models** → Represent the database entities.
    - **Routes**
      - `routes/vera/web.php` – Admin and Receptionist (Web interface)
      - `routes/vera/client.php` – Client (Mobile interface)

---

## 📂 Postman Collection
To test APIs, import the file:  
`Veraa.json`

---

## 🚀 Installation
```bash
git clone <repo-url>
cd project-folder
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan serve
````

