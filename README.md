# 📦 Product Page – Hapana Fireworks

The Product Page is a core module of the Hapana Fireworks Web System.  
It allows users to browse, search, and view fireworks products, while administrators can manage product data through a connected backend system.

---

## 🚀 Overview

This module provides a smooth and responsive interface for displaying products with full integration to the MySQL database. It supports product listing, filtering, and detailed viewing, ensuring a user-friendly shopping experience.

---

## ✨ Features

### 🛍️ Product Display
- Displays all available fireworks products
- Shows:
  - Product Name
  - Price
  - Image
  - Category
  - Stock status
- Clean card-based UI design

---

### 🔍 Search & Filtering
- Search products by name
- Filter by categories:
  - Rockets
  - Crackers
  - Fountains
  - Sparklers
- Dynamic product loading from database

---

### 📄 Product Details
- View detailed information about each product
- Includes:
  - Full description
  - Price
  - Availability
- “Buy Now” or “Add to Cart” options

---

### 🧠 Smart Stock Handling
- Shows **Low Stock warnings**
- Prevents ordering unavailable items
- Updates automatically based on database values

---

## 🎨 UI/UX Design

- Modern **Black & Gold theme**
- Responsive layout using **Bootstrap 5**
- Card-style product display
- Hover effects and smooth animations
- Consistent navbar (same as homepage)

---

## 🛡️ Validation

- Ensures:
  - Price is numeric
  - Stock is non-negative
  - Product name is not empty
- Prevents invalid or missing product data

---

## 🗄️ Database Integration

The Product Page is fully connected to the MySQL database.

### Example Table: `products`

| Field       | Description              |
|------------|--------------------------|
| id         | Product ID              |
| name       | Product name            |
| category   | Product category        |
| price      | Product price           |
| stock      | Available quantity      |
| image      | Product image path      |

---

## 🔄 Workflow

1. Admin adds products through admin panel
2. Product data is stored in MySQL
3. Product Page fetches data dynamically
4. Users browse and select products
5. Selected items are added to cart or purchased

---

## 🛠️ Technologies Used

- **Frontend**
  - HTML5
  - CSS3
  - Bootstrap 5

- **Backend**
  - PHP

- **Database**
  - MySQL

---

## 📌 File Structure (Example)
/products.php
/product_details.php
/admin/products.php
/images/


---

## ⚙️ Setup Instructions

1. Ensure XAMPP is running (Apache & MySQL)
2. Import the database into phpMyAdmin
3. Configure database in `config.php`
4. Run the project:
