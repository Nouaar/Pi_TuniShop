# 🛒 TuniShop Symfony App - E-Shopping Management System

TuniShop Symfony App is a robust web-based e-shopping platform that helps manage products, depots, stock items, checkouts, claims, and much more. The app enables efficient inventory management, secure transactions, and dynamic interactions for users and administrators alike.

---

## 🚀 Features

- 🙋 **User Management**: Includes login, sign-up, and user role management (Admin, Seller, Buyer), with security features like token and session management.
- 🏬 **Depot Management**: Add, view, edit, and delete depots in the system.
- 📦 **Stock Management**: Add, update, and manage stocks linked to specific depots.
- 🖻 **Blog Management**: Admin can add, edit, and delete blogs, and users can interact with them.
- 📝 **Checkout Management**: Add, update, view, and manage user checkout details.
- 🧾 **Refunds Management**: Handle refund requests associated with claims and process accordingly.
- 🗯️ **Claims Management**: Receive and process customer feedback and complaints.
- 📊 **Data Visualization**: Real-time analytics and statistics based on e-shopping data like stock status, sales, etc.
- 🔒 **Secure Access & Authentication**: Role-based access control with identity verification.
- 🖨️ **PDF Export**: Export detailed checkout information as PDF, including all purchased items and Qr code references for secure validation.
- 🏴 **Logging**: Maintain logs for CRUD operations to track and recover any deleted or modified data.
- 🗣️ **Audio-based Navigation Assistance**: Provides real-time feedback and guidance, helping users navigate the app efficiently.
- 📝 **Data Export to PDF**: Export checkouts and product data to PDF with detailed information.(requires downloading wkhtmltopdf, through the following link : https://wkhtmltopdf.org/downloads.html)

---

## ⚠️ Error Handling & Validation

*Form Validation*  
- All forms validate user input (Depot, Stock, Checkout, etc.), ensuring required fields are filled and that the correct data types are used.
- Specific validations include checks for required fields and input types (e.g., date, email, number).

---

## 🛠️ Technologies

- **[PHP](https://www.php.net/)**: The primary programming language used to build the app, handling the business logic and backend operations.
- **[Symfony](https://symfony.com/)**: A PHP framework used for rapid web application development, providing the foundation for the TuniShop Symfony App.
- **[Twig](https://twig.symfony.com/)**: A flexible, fast, and secure template engine for PHP, used for rendering views in the app.
- **[MySQL](https://www.mysql.com/)**: A relational database management system for storing application data such as products, depots, users, and transactions.
- **[Composer](https://getcomposer.org/)**: Dependency manager for PHP, used to manage external libraries and tools.
- **[PDFLib](https://www.pdflib.com/)**: Used to generate and export PDF documents with checkout details and product information.

---

## 📂 Project Structure

```bash
  TuniShop_Symfony_App/
  ├── src/
  │   ├── Controller/                # PHP controllers handling the app's logic and routing
  │   │   ├── AdressUserController.php # Handles user address-related logic
  │   │   ├── BlogController.php      # Controls the blog-related UI and interactions
  │   │   ├── CheckoutController.php  # Manages checkout logic and interactions
  │   │   ├── DepotController.php     # Manages depot-related actions
  │   │   ├── StockController.php     # Handles stock management and CRUD operations
  │   ├── Entity/                    # Doctrine entities for database operations
  │   │   ├── Blog.php                # Blog entity for blog data storage
  │   │   ├── Checkout.php            # Checkout entity for storing checkout info
  │   │   ├── Depot.php               # Depot entity for managing depot data
  │   │   ├── Stock.php               # Stock entity for managing stock information
  │   ├── Form/                      # Symfony forms for handling form logic
  │   │   ├── CheckoutType.php        # Form type for checkout handling
  │   │   ├── StockType.php           # Form type for managing stock-related data
  │   ├── Repository/                # Custom database repositories for querying entities
  │   ├── Service/                   # Backend services to handle business logic (e.g., PDF export)
  │   │   ├── CheckoutService.php    # Service to handle checkout operations
  │   │   ├── PDFExportService.php   # Service for generating and exporting PDFs
  │   ├── templates/                 # Twig templates for the app's frontend
  │   │   ├── checkout/              # Views related to checkout functionalities
  │   │   ├── stock/                 # Views for stock management and actions
  │   │   ├── product/               # Product-related views
  ├── public/                        # Public directory containing assets (JS, CSS, images)
  ├── var/                           # Temporary application files like cache and logs
  ├── vendor/                        # Third-party libraries managed by Composer
  ├── .env                           # Environment configuration file
  ├── composer.json                  # PHP dependencies configuration file
  ├── README.md                      # This readme file
  ├── symfony.lock                   # Lock file to ensure consistent dependencies
```
---
## ⚙️ How to Run Locally
*1.Clone the repository*:
```bash
git clone https://github.com/yess1ne/tunishop-symfony-app.git
cd tunishop-symfony-app
```
*2.Install dependencies*:
Ensure that you have Composer installed, then run the following:
```bash
composer install
```
*3.Set up the database*:
Run the following to create the database and set up the schema:
```bash
php bin/console doctrine:database:create
php bin/console doctrine:schema:update --force
```
*4.Run the Symfony server*:
To start the server, use:
```bash
symfony server:start
```
*5.Access the app*:
Open your browser and navigate to:
```bash
http://localhost:8000
```
