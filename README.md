# DSSM Backend - Paynow Subscription System

A PHP backend for handling Paynow subscriptions, built for Render.com deployment.

## Structure

```
dssm-backend/
├── paynow/
│   ├── initiate.php    # Initiate payment
│   ├── poll.php        # Poll payment status
│   ├── callback.php    # Handle Paynow callbacks
│   └── config.php      # Configuration
├── api/
│   ├── check_subscription.php    # Check subscription status
│   └── activate_subscription.php # Activate subscription
├── frontend/
│   ├── index.html      # Payment form
│   ├── success.html    # Success page
│   ├── cancel.html     # Cancel page
│   └── app.js          # Frontend JavaScript
├── utils/
│   ├── db.php          # Database connection
│   ├── helpers.php     # Helper functions
│   └── logger.php      # Logging utility
├── logs/               # Auto-created log directory
├── schema.sql          # Database schema
└── README.md
```

## Deployment on Render.com

1. Create a new Web Service on Render.com
2. Connect your GitHub repository
3. Set the following environment variables:
   - `DB_HOST`: Your MySQL database host
   - `DB_NAME`: Database name
   - `DB_USER`: Database username
   - `DB_PASS`: Database password
   - `PAYNOW_ID`: Your Paynow integration ID
   - `PAYNOW_KEY`: Your Paynow integration key
   - `RETURN_URL`: https://your-render-app.com/frontend/success.html
   - `RESULT_URL`: https://your-render-app.com/paynow/callback.php

4. Set Build Command: (leave empty)
5. Set Start Command: `php -S 0.0.0.0:$PORT -t .`

## Database Setup

1. Create a MySQL database
2. Run the `schema.sql` file to create tables:
   - `users`: Stores user information
   - `subscriptions`: Stores subscription data
   - `payment_logs`: Logs payment transactions

## Paynow Configuration

1. Log into your Paynow merchant account
2. Go to Settings > Integrations
3. Set Result URL to: `https://your-render-app.com/paynow/callback.php`
4. Set Return URL to: `https://your-render-app.com/frontend/success.html`

## API Endpoints

### Initiate Payment
- **URL**: `/paynow/initiate.php`
- **Method**: POST
- **Body**: `{"reference": "sub_123", "amount": 10.00, "phone": "0771234567", "email": "user@example.com"}`
- **Response**: `{"success": true, "redirect_url": "...", "poll_url": "...", "reference": "..."}`

### Poll Payment Status
- **URL**: `/paynow/poll.php?reference=sub_123`
- **Method**: GET
- **Response**: `{"success": true, "status": "Paid"}`

### Check Subscription
- **URL**: `/api/check_subscription.php?phone=0771234567`
- **Method**: GET
- **Response**: `{"subscribed": true, "expires": "2024-12-31"}`

### Activate Subscription
- **URL**: `/api/activate_subscription.php`
- **Method**: POST
- **Body**: `{"reference": "sub_123"}`
- **Response**: `{"success": true, "message": "Subscription activated", "expires_at": "2024-12-31 12:00:00"}`

## Flutter Integration

Update your Flutter app to point to the Render.com URLs:

```dart
const String baseUrl = 'https://your-render-app.com';

// Example usage
final response = await http.post(
  Uri.parse('$baseUrl/paynow/initiate.php'),
  headers: {'Content-Type': 'application/json'},
  body: jsonEncode({
    'reference': reference,
    'amount': amount,
    'phone': phone,
    'email': email,
  }),
);
```

## Logging

Logs are written to `/logs/app.log` with timestamps and log levels.

## Security Notes

- Never commit credentials to version control
- Use HTTPS in production
- Validate all inputs
- Monitor logs for suspicious activity