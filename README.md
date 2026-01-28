# Simple Elementor Featured Image Widget

A lightweight WordPress plugin that adds a simple Elementor widget to display the **current post’s featured image**.

---

## ✨ Features

- Adds a **Featured Image** widget inside Elementor
- Displays the **current post/page featured image**
- Simple, lightweight, and easy to use
- Works with Elementor’s editor and frontend rendering

---

## 📦 Installation

1. Download or clone this repository into your WordPress plugins directory:

   `wp-content/plugins/simple-elementor-featured-image-widget/`

2. Activate the plugin:
   - Go to **WordPress Admin → Plugins**
   - Find **Simple Elementor Featured Image Widget**
   - Click **Activate**

3. Make sure **Elementor** is installed and activated.

---

## 🚀 Usage

1. Open a post/page in **Elementor**.
2. Search for the widget: **Featured Image** (or the widget name as registered).
3. Drag it into your layout.
4. The widget will output the featured image of the currently viewed post/page.

> Note: The image will show only if the post/page has a featured image set.

---

## ✅ Requirements

- WordPress 5.0+
- Elementor 3.0+
- PHP 7.4+ (recommended)

---

## 🛠 Development

### Folder Structure (suggested)

```text
simple-elementor-featured-image-widget/
├─ simple-elementor-featured-image-widget.php
├─ widgets/
│  └─ featured-image-widget.php
├─ assets/
│  ├─ css/
│  └─ js/
└─ README.md
