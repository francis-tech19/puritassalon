# Web-Based Salon Management System for Purita's Beauty Lounge

[![BS Information Systems Capstone Project](https://img.shields.io/badge/Capstone-BS%20Information%20Systems-7A1C49.svg)](https://github.com)
[![Framework: Laravel 13](https://img.shields.io/badge/Framework-Laravel%2013-FF2D20.svg)](https://laravel.com/)
[![PHP: 8.3+](https://img.shields.io/badge/PHP-8.3%2B-777BB4.svg)](https://www.php.net/)
[![Frontend: Blade + Tailwind + Alpine](https://img.shields.io/badge/Frontend-Blade%20%7C%20Tailwind%20%7C%20Alpine-38B2AC.svg)](https://tailwindcss.com/)
[![Build: Vite](https://img.shields.io/badge/Build-Vite-646CFF.svg)](https://vitejs.dev/)

A production-ready, accessible, web-based Salon Management System specifically designed for **Purita's Beauty Lounge**. Built to digitize daily appointments, inventory monitoring, POS sales checkout, customer loyalty rewards, operational expense tracking, financial profit reporting, in-app notifications, and supplementary AI decision support.

---

## Table of Contents
1. [Project Overview](#project-overview)
2. [Technology Stack](#technology-stack)
3. [System Architecture](#system-architecture)
4. [User Roles & Security](#user-roles--security)
5. [Key System Modules](#key-system-modules)
6. [Installation & Setup Guide](#installation--setup-guide)
7. [Running the Application](#running-the-application)
8. [Testing & Verification](#testing--verification)

---

## Project Overview

Purita's Beauty Lounge previously operated using manual paper records and unstructured appointment inquiries over messaging channels. This system delivers:
- **Appointment Conflict Prevention:** Automatic detection preventing staff double-booking or overlapping times.
- **Rule-Based Available Slot Suggestions:** Recommends candidate appointment times when a requested slot is occupied.
- **POS & Billing:** Real-time checkout calculating subtotal, discount, cash/GCash payments, and receipt generation.
- **Inventory Tracking:** Stock-in/stock-out with automatic low-stock notifications when items reach minimum threshold levels.
- **Financial Profit Analysis:** Calculated accurately via `Revenue - Operational Expenses = Net Profit`.
- **Senior-Friendly UI/UX:** High-contrast text, large buttons, clean menus, and confirmation dialogs.
- **Supplementary AI Consultant:** Salon recommendations with offline fallback support.

---

## Technology Stack

- **Backend / Full-Stack:** Laravel 13 (PHP 8.3+)
- **Frontend / Templating:** Laravel Blade Templates
- **Styling & Interaction:** Tailwind CSS v4, Alpine.js, Lucide Icons, Chart.js
- **Build Tool:** Vite (via `laravel-vite-plugin`)
- **Database:** SQLite (default development) / MySQL
- **Session & Auth:** Built-in Laravel Authentication & Session Guards (Session Encrypted, CSRF Protected)

---

## System Architecture

```
Browser Client (Blade HTML + Alpine.js + Tailwind CSS)
        |
        v  (HTTPS / CSRF-Protected Requests & Session Cookies)
Laravel 13 Application (app/Http/Controllers, Middleware, Models)
        |
        +---> Eloquent ORM (SQLite / MySQL)
        |
        +---> Supplementary AI Assistant
```

---

## User Roles & Security

| Role | Access Scope |
| :--- | :--- |
| **ADMIN** | System administration, user account management, password reset, audit log overview. |
| **OWNER** | Full Operations: Dashboard, Appointments, Customers, Employees, Services, Inventory, Sales/POS, Expenses, Loyalty, Analytics, Financial Reports, Audit Logs, Settings, AI Assistant. |
| **STAFF** | Daily Operations: Appointments, Customers CRM, Services Menu, Inventory Adjustments, POS Sales Checkout, Loyalty Rewards, In-app Notifications. |

### Built-in Security Implementations
- **Bcrypt Hashing:** User passwords securely hashed with 12 rounds.
- **CSRF Defense:** Automatic token verification on all POST/PUT/PATCH/DELETE routes.
- **SQL Injection Defense:** Parameterized queries via Eloquent ORM.
- **Brute-Force Throttling:** Rate-limited authentication requests (`throttle:10,15`).
- **Protected Endpoints:** Diagnostics and management routes gated behind authentication middleware.
- **Session Encryption:** Enabled session cookies (`SESSION_ENCRYPT=true`).

---

## Installation & Setup Guide

### Prerequisites
1. **PHP**: v8.3 or higher
2. **Composer**: v2.x
3. **Node.js**: v18+ & npm

### 1. Setup Instructions
Navigate to the `laravel` directory:
```bash
cd laravel
```

Install PHP and JavaScript dependencies:
```bash
composer install
npm install
```

Configure your environment:
```bash
cp .env.example .env
php artisan key:generate
```

Run database migrations and seeders:
```bash
php artisan migrate --seed
```

---

## Running the Application

From inside the `laravel` folder:

```bash
# Terminal 1: Start Laravel Development Server
php artisan serve

# Terminal 2: Start Vite Asset Compiler
npm run dev
```

The application will be accessible at: `http://localhost:8000`

---

## Testing & Verification

- **Default Demo Accounts:**
  - Owner: Username `owner` | Password `password123`
  - Staff: Username `staff1` | Password `password123`
  - Admin: Access configured via system roles

