# DSSM Backend - Paynow Subscription System

A PHP backend for handling Paynow subscriptions, deployed with Docker on Render.com.

## Structure

```
dssm-backend/
├── Dockerfile
├── nginx.conf
├── src/
│   ├── index.php
│   ├── api.php
│   ├── config.php
│   ├── paynow/
│   │   ├── initiate.php
│   │   ├── poll.php
│   │   └── callback.php
│   ├── api/
│   │   ├── check_subscription.php
│   │   └── activate_subscription.php
│   ├── frontend/
│   │   ├── index.html
│   │   ├── success.html
│   │   ├── cancel.html
│   │   └── app.js
│   ├── utils/
│   │   ├── db.php
│   │   ├── helpers.php
│   │   └── logger.php
│   ├── logs/
│   ├── schema.sql
│   └── README.md
```

## Deployment on Render.com with Docker

1. Create a new Web Service on Render.com
2. Connect your GitHub repository (dssm-backend)
3. Set Environment to `Docker`
4. Set Root Directory to `.` (empty, since Dockerfile is at root)
5. Set the following environment variables:
   - `DB_HOST`: Your MySQL database host
   - `DB_NAME`: Database name
   - `DB_USER`: Database username
   - `DB_PASS`: Database password
   - `PAYNOW_ID`: Your Paynow integration ID
   - `PAYNOW_KEY`: Your Paynow integration key
   - `RETURN_URL`: https://your-render-app.onrender.com/frontend/success.html
   - `RESULT_URL`: https://your-render-app.onrender.com/paynow/callback.php

6. Build Command: (leave empty)
7. Start Command: (leave empty)
8. Click Deploy

Render will build the Docker image and run the PHP Apache server.

## Database Setup

1. Create a MySQL database (e.g., on PlanetScale, Railway, or AWS RDS)
2. Run the `schema.sql` file to create tables

## Paynow Configuration

1. Log into your Paynow merchant account
2. Set Result URL to: `https://your-render-app.onrender.com/paynow/callback.php`
3. Set Return URL to: `https://your-render-app.onrender.com/frontend/success.html`

## API Endpoints

Same as before, all endpoints are available under the Render URL.

## Flutter Integration

Update your Flutter app to use the Render URL without Cloudflare blocking.

## Notes

- The Docker setup uses PHP 8.2 with Apache
- Logs are written to `src/logs/app.log`
- No Cloudflare protection on Render, so direct API calls work