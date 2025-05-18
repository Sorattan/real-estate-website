# 🏠 Real Estate Management – PHP & MySQL

A simple PHP-based real estate website for listing, browsing, and managing properties.  
Built with plain PHP, HTML/CSS and MySQL it provides user registration, login, CRUD operations on property listings, appointment booking and sales/offers tracking.

---

## 🖥️ Running locally (XAMPP)

```
http://localhost/Website/
```
---

## 🚀 Feature overview

* **Authentication**

  * Agent sign‑up (`kayit.php`) & login (`giris.php`)
  * Customer sign‑up (`müşterikayit.php`) & login (`müşterigiriş.php`)
  * Session‑based access, logout with `cikis.php`
* **Listings**

  * Add / edit / delete from **Mülklerim** (`mulkler.php`, modal‑based)
  * Public catalogue & detail view (`index.php`, `detay.php`)
* **Offers & Appointments**

  * Submit offers (`teklifler.php`)
  * Book viewings (`randevular.php`)
  * Sale / rental finalisation (`satin_alım` trigger + `satislar.php`)
* **Personal dashboard**

  * KPI cards, latest customers & appointments (`acilis.php`)
  * Profile management (`profil.php`)

---

## 📂 Folder map

```
Database/
   └─ realestate.sql    # Full MySQL dump (views, triggers, data)
Website/
├─ acilis.php           # Agent dashboard
├─ baglanti.php         # DB connection helper
├─ index.php            # Public landing / catalogue
│
├─ giris.php            # Agent login
├─ kayit.php            # Agent sign‑up
├─ cikis.php            # Logout (destroys session)
│
├─ müşterigiriş.php     # Customer login
├─ müşterikayit.php     # Customer sign‑up
│
├─ mulkler.php          # Agent listing CRUD
├─ ilan_duzenle.php     # Edit a listing
├─ ilan_sil.php         # Delete a listing
│
├─ teklifler.php        # Incoming offers
├─ randevular.php       # Appointments calendar
├─ satislar.php         # Sales / rentals history
│
├─ detay.php            # Single‑property detail page
├─ profil.php           # Agent profile & password change
│
├─ css/
   └─ index.css         # Shared style additions
```

---

## ⚙️ Prerequisites

* **XAMPP** (PHP ≥ 8.1, MySQL / MariaDB)
* Any modern browser

---

## 💿 Installation

1. **Clone** into XAMPP `htdocs`:

   ```
   C:\xampp\htdocs\Website\
   ```
2. **Create DB & import schema**

   * In phpMyAdmin make a DB called **`realestate`**.
   * Import `Database/realestate.sql`.
3. **Configure credentials (Optional)** (`baglanti.php`):

   ```php
   $host = 'localhost';
   $kullanici = 'root';
   $parola = '';
   $vt   = 'realestate';
   ```
4. **Browse** to [http://localhost/Website/](http://localhost/Website/)
   * Register an agent or a customer and you’re set!

---

## 📝 License

Released under the **MIT License**.
