# HTLM Proje – Real Estate Management

A simple PHP-based real estate website for listing, browsing, and managing properties.  
Built with plain PHP, HTML/CSS and MySQL it provides user registration, login, CRUD operations on property listings, appointment booking and sales/offers tracking.

---

## 🖥️ Demo (Local)

You can run it on XAMPP under `htdocs`, then visit: http://localhost/Website/

---

## 🚀 Features

- **User authentication**  
  - Customer registration (`müşterikayit.php`) & login (`müşterigiriş.php`)  
  - Session-based access control; logout via `cikis.php`
- **Property Listings**  
  - Add new listing (`ilan_ekle.php` via `ilanlar.sql` schema)  
  - Edit (`ilan_duzenle.php`) & delete (`ilan_sil.php`)  
  - Browse all (`mulkler.php`) and view details (`detay.php`)
- **Booking & Offers**  
  - Book viewings / appointments (`randevular.php`)  
  - Submit and track offers (`teklifler.php`)  
  - Sales management (`satislar.php`)
- **User Profile**  
  - View & edit personal info (`profil.php`)
- **Admin / Management**  
  - Listing management pages (`ilan_*.php`), sales, appointments etc.

---

## 📂 Project Structure

 Website/
 ```
 acilis.php   # Landing / welcome
 baglanti.php   # DataBase connection (configure host/user/pass/db)
 kurulum.php   # (Optional) runs installation tasks
 index.php   # Main entry (redirects to login or listings)
 giris.php   # Login page
 kayit.php   # Customer signup
 cikis.php   # Logout

 musterigiris.php   # Alternate customer login
 musterikayit.php   # Alternate customer signup

 mulkler.php   # Browse all properties
 detay.php   # Single property detail
 ilan_duzenle.php   # Edit property
 ilan_sil.php   # Delete property

 teklifler.php   # View/submit offers
 randevular.php   # Appointments interface
 satislar.php   # Sales records

 profil.php   # User profile

 css/
 └─ index.css   # Styles

 img/
 └─ log.jpg   # Logo / header image
```
---

## ⚙️ Prerequisites

- **XAMPP** (Apache + PHP + MySQL)  
- A modern web browser (Chrome, Firefox, Edge…)

---

## 💿 Installation

**1. Clone or download** this repo into your XAMPP `htdocs` folder, renaming folder to `Website` if needed:

   ```
   C:\xampp\htdocs\Website\
   ```
   
**2. Create the database**

   - In phpMyAdmin, create a database named `realestate`.

   - Import the schema file:

    ```
    Database/realestate.sql
    ```
    
**3. Configure DB connection**
   - Open baglanti.php and personalize (if you want) your MySQL credentials. `Default values`:
 
    ```
    $host   = 'localhost';
    $kullanici   = 'root';
    $parola   = '';
    $vt = 'realestate';
    ```
   
**4. (Optional) Run installer**
   - If you prefer a web-based setup, open:

    ```
    http://localhost/Website/kurulum.php
    ```

**5. Start Using**
   - Visit http://localhost/Website/
   
   - Register a new customer, log in, and begin adding/listing properties.

---
## 📄 License
This project is released under the MIT License.
