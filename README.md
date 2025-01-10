# Woo-Edit-Credit-System Plugin

## Description

The **Woo-Edit-Credit-System Plugin** is a WordPress plugin designed to extend WooCommerce functionality by introducing a credit-based system for managing projects. It enables users to purchase products with different credit limits, add projects from their accounts, and manage these projects under WooCommerce orders. It also supports a free trial feature via a shortcode and includes dynamic link validation functionality for project submissions.

## Demo Video

For a quick demonstration of the plugin in action, check out this video:

[<img src="https://img.youtube.com/vi/djIglDOujIo/0.jpg">](https://www.youtube.com/watch?v=djIglDOujIo)

## Features

- **Project Credit System**:
  - Each WooCommerce product is associated with a specific credit limit.
  - Users can add projects (1 project = 1 credit) after purchasing products.
  - Credit limits prevent users from adding more projects once credits are exhausted.
- **Admin Project Management**:
  - Admin can view all user-submitted projects in the WooCommerce order section.
- **Free Trial Functionality**:
  - Users can submit a free trial project without logging in.
  - A shortcode `[custom-cred-order-form]` is available to display the free trial form on the frontend.
  - Admin can view free trial projects in the WooCommerce order section.
- **Dynamic Link Validation**:
  - Admin can specify accepted link formats in the settings.
  - Frontend users must provide valid links when submitting their projects.
- **Flexible WooCommerce Integration**:
  - Fully integrates with WooCommerce products and orders.

## Installation

1. Download the plugin and upload it to your WordPress installation:
   - Upload the plugin folder to the `/wp-content/plugins/` directory.
   - Alternatively, install it directly through the WordPress Plugins menu by uploading the `.zip` file.
2. Activate the plugin through the "Plugins" menu in WordPress.

## Usage

### For Users

1. **Purchase Subscription**:
   - Log in to your account and purchase a WooCommerce product with a specific credit limit.
   - Each product corresponds to a different credit limit (e.g., 10 credits, 20 credits).
2. **Add Projects**:
   - Navigate to your account and add projects under the purchased product.
   - Each project consumes 1 credit.
   - If your credit limit is reached, you will no longer be able to add projects unless you purchase more credits.
3. **Free Trial Submission**:
   - Use the `[custom-cred-order-form]` shortcode to display the free trial form on the frontend.
   - Users can submit a free trial project without logging in.
   - Free trial submissions are managed by the admin in the WooCommerce order section.

### For Admins

1. **Manage Projects**:
   - View all user-submitted projects (both purchased and free trial) in the WooCommerce order section.
2. **Configure Link Validation**:
   - Go to the plugin settings in the WordPress admin area.
   - Define the accepted link formats for project submissions (e.g., Dropbox, Google drive).
   - Ensure that frontend users can only submit projects with valid links based on your settings.

### Link Validation

- When a user submits a project, the plugin dynamically validates the provided footage link.
- Admins can define accepted link formats in the settings to ensure the validity of project links.

## Shortcodes

### `[custom-cred-order-form]`

- **Purpose**: Displays the free trial project submission form on the frontend.
- **Usage**: Add this shortcode to any page or post to allow users to submit a free trial project without logging in.

## Requirements

- **WordPress 5.0 or higher**
- **WooCommerce** plugin

## Screenshots

### 1. User Account: Add Project

![Screenshot of User Account: Add Project](https://raw.githubusercontent.com/sanjoydas123/wordpress-course-create/main/add-project-screenshot.png)

### 2. Admin Settings: Link Validation

![Screenshot of Admin Settings: Link Validation](https://raw.githubusercontent.com/sanjoydas123/wordpress-course-create/main/admin-settings-screenshot.png)

### 3. Free Trial Submission Form

![Screenshot of Free Trial Submission Form](https://raw.githubusercontent.com/sanjoydas123/wordpress-course-create/main/free-trial-screenshot.png)

---

### Happy Managing Projects!
