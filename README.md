# 🤖 AI QA Tool: High-Performance Page Auditor

A professional, Laravel-powered platform designed to automate Quality Assurance (QA) for web content using **Gemini 1.5 Flash**. Specifically built to handle bilingual content analysis (English/Welsh) and complex audit rules with server-side background processing.

## 🚀 The Problem vs. The Solution

*   **The Old Way:** Browser-based scripts that were slow, timed out on large CSVs, and relied on Gemini's internal (slow) web crawler.
*   **The Professional Way:** A Laravel-driven architecture using **Redis Queues**. We pre-fetch and clean HTML content server-side, then send optimized payloads to Gemini for sub-second analysis.

## ✨ Key Features

-   **Dynamic Prompt Engine:** Create and manage multiple AI "Audit Profiles" (CRUD). Change your QA rules without touching a line of code.
-   **Structured AI Output:** Uses Gemini’s JSON Schema mode to ensure the AI always returns valid, mapable data.
-   **Asynchronous Processing:** Powered by Laravel Queues (Redis). Upload a CSV with 1,000+ URLs and let the server handle the heavy lifting in the background.
-   **Bilingual Analysis:** Specialized logic for English vs. Welsh content matching, link verification, and accessibility checks.
-   **Dynamic Reporting:** Export results to CSV where columns are automatically generated based on the AI's response keys.
-   **Automated Deployment:** Includes a custom VPS setup script for a production-ready LEMP stack.

## 🛠 Tech Stack

-   **Framework:** Laravel 13
-   **AI Engine:** Google Gemini 1.5 Flash (via Structured Outputs)
-   **Database:** MySQL 8.0
-   **Cache/Queue:** Redis
-   **Frontend:** Blade, TailwindCSS, AlpineJS
-   **Server Management:** Nginx + PM2

---

## 📦 Installation

### 1. Server Preparation
Use the [VPS Setup Script](https://github.com/shahjalal132/vps-setup) to prepare your Ubuntu server:
```bash
sudo bash <(curl -sSL https://raw.githubusercontent.com/shahjalal132/vps-setup/main/setup.sh)
```

### 2. Application Setup
```bash
git clone https://github.com/your-username/ai-qa-tool.git .
composer install
npm install && npm run build
cp .env.example .env
php artisan key:generate
```

### 3. Configure Gemini AI
Add your API key to the `.env` file:
```env
GEMINI_API_KEY=your_api_key_here
QUEUE_CONNECTION=redis
```

### 4. Start the Engine
Keep the AI processing workers alive using PM2:
```bash
pm2 start "php artisan queue:work --tries=3" --name qa-worker
```

---

## 📖 How to Use

1.  **Define a Prompt:** Go to the "Prompts" page and create a new QA instruction (e.g., "NHS Content Auditor"). Define the JSON schema for the fields you want back (e.g., `h1_match`, `broken_links`).
2.  **Upload URLs:** Upload your CSV containing the English and Welsh URL pairs.
3.  **Map & Run:** In the "QA Manager," select your URL batch and the Prompt you want to use. Click **Run**.
4.  **Monitor:** Watch the progress in real-time. Once completed, download your detailed QA report as a CSV.

## ⚙️ Performance Optimization
This tool is optimized for speed:
-   **HTML Stripping:** We remove `<script>`, `<style>`, and `<svg>` tags before sending data to Gemini to save tokens and reduce latency.
-   **Parallel Workers:** Supports multiple concurrent workers to process dozens of URLs every second.
-   **Rate Limiting:** Built-in middleware to handle Gemini API rate limits (429 errors) with automatic retries.

## 📄 License
This project is licensed under the MIT License.

---
*Developed & Maintained by [Muhammad Shahjalal](https://github.com/shahjalal132)*