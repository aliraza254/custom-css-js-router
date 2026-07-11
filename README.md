# Custom CSS JS Router

[![WordPress.org Plugin](https://img.shields.io/badge/WordPress.org-Plugin-blue.svg?logo=wordpress&logoColor=white)](https://wordpress.org/plugins/custom-css-js-router/)
[![WordPress Version](https://img.shields.io/badge/WordPress-%3E%3D%206.0-0073aa.svg?logo=wordpress)](https://wordpress.org/plugins/custom-css-js-router/)
[![PHP Version](https://img.shields.io/badge/PHP-%3E%3D%208.0-777bb4.svg?logo=php&logoColor=white)](https://wordpress.org/plugins/custom-css-js-router/)
[![License: GPL v2+](https://img.shields.io/badge/License-GPL%20v2%2B-lightgrey.svg)](https://www.gnu.org/licenses/gpl-2.0.html)

**Custom CSS JS Router** is a lightweight, high-performance, and secure WordPress plugin designed to inject custom CSS styles and JavaScript scripts globally or page-specifically, without modifying your theme files.

Developed with clean, object-oriented PHP and structured according to WordPress.org Detailed Plugin Guidelines, it provides a safe, robust, and translation-ready solution for site-wide customization.

---

## ⚡ Key Features

- **Granular Routing**: Inject code globally or target specific pages/posts individually.
- **Context-Aware Loading**: Choose where code is executed:
  - **Frontend Only**: Keeps admin dashboard loading times clean.
  - **Admin Only**: Runs exclusively inside the WordPress administration dashboard.
  - **Both**: Runs everywhere.
- **Modern UI**: Polished dashboard built with native WordPress styles and a CodeMirror editor.
- **Built-in Safe Mode**: Built-in recovery system to bypass custom execution in case of syntax or script conflicts.
- **GPLv2 Compatible**: Distributed under the GPLv2 or later license.
- **Translation Ready**: Fully localized and shipped with translation templates.

---

## 🛠️ Installation

### Via WordPress Dashboard
1. Log in to your WordPress dashboard.
2. Navigate to **Plugins > Add New**.
3. Search for **Custom CSS JS Router**.
4. Click **Install Now** and then **Activate**.

### Manual Installation
1. Download the plugin ZIP archive.
2. Upload the extracted `custom-css-js-router` directory to your `/wp-content/plugins/` directory.
3. Activate the plugin through the **Plugins** menu in WordPress.

---

## 🛡️ Safe Mode Recovery

If you accidentally inject broken code that breaks your site or prevents admin panel access, you can bypass all custom code execution using one of the following methods:

1. **URL parameter**: Append `?ccr_safe_mode=1` to your browser address bar URL.
2. **WP-Config**: Define the bypass constant in your `wp-config.php` file:
   ```php
   define( 'CCR_SAFE_MODE', true );
   ```

Once activated, a notice will appear in the dashboard indicating that safe mode is running, allowing you to fix or pause the broken code safely.

---

## 📂 Code Structure

The plugin is designed with modularity and WordPress best practices:
```
custom-css-js-router/
├── admin/               # Admin dashboard stylesheets and JS scripts
├── languages/           # Localized .pot files
├── includes/
│   ├── Core/            # Dependency injection container & base service registry
│   ├── Controllers/     # Settings page controller and AJAX save routines
│   └── Services/        # Code injectors and DB options wrappers
├── README.txt           # WordPress.org Readme template
└── custom-css-js-router.php # Main plugin bootstrap file
```

---

## 📜 License

This project is licensed under the GPL v2 or later. See [LICENSE.txt](./LICENSE.txt) for more details.
