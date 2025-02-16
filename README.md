# Thermal Printer Receipt Management System

A PHP-based thermal printer receipt generation API that enables printing of professional-quality receipts with merchant and customer copies using ESC/POS printer technology.

## Features

- Two-copy receipt printing system (Merchant & Customer)
- Lebanese Pound (LBP) currency formatting
- Configurable receipt width
- Logo printing support
- Detailed item listing with proper alignment
- Professional totals section
- Secure API key authentication
- Environment-based configuration
- CUPS printer integration
- Error handling and validation

## Requirements

- PHP 7.4 or higher
- Composer
- CUPS printing system
- ESC/POS compatible thermal printer
- MAMP or similar PHP development environment

## Installation

1. Clone the repository:
```bash
git clone [repository-url]
cd print
```

2. Install dependencies:
```bash
composer install
```

3. Configure environment:
```bash
cp .env.example .env
```

4. Edit `.env` file with your configuration:
```
API_KEY=your_api_key_here
PRINTER_NAME=your_printer_name
RECEIPT_WIDTH=48
```

## Usage

Send a POST request to the API endpoint with the following JSON structure:

```json
{
    "api_key": "your_api_key",
    "data": {
        "company": {
            "name": "Company Name",
            "address": "Company Address",
            "phone": "Phone Number"
        },
        "items": [
            {
                "name": "Item Name",
                "quantity": 1,
                "price": "100,000"
            }
        ],
        "subtotal": "100,000",
        "tax": "11,000",
        "total": "111,000"
    }
}
```

## API Reference

### POST /

Prints a receipt with both merchant and customer copies.

#### Request Headers
- Content-Type: application/json

#### Request Body
| Parameter | Type | Description |
|-----------|------|-------------|
| api_key | string | Authentication key |
| data | object | Receipt data object |

#### Response
- Success: `{"status": "success", "message": "Receipt printed successfully"}`
- Error: `{"status": "error", "message": "Error description"}`

## Project Structure

```
print/
├── config/
│   └── bootstrap.php     # Application initialization
├── vendor/              # Composer dependencies
├── .env                 # Environment configuration
├── .env.example        # Environment template
├── index.php           # Main application file
└── README.md           # Documentation
```

## Error Handling

The system includes comprehensive error handling for:
- Invalid API keys
- Malformed JSON requests
- Missing required fields
- Printer connection issues
- Invalid price formats

## Price Formatting

- Supports Lebanese Pound (LBP) formatting
- Handles prices with commas (e.g., "100,000")
- Automatically sanitizes and formats prices for display

## Security

- API key authentication required for all requests
- Environment-based configuration
- Input validation and sanitization
- CORS headers configuration

## Contributing

1. Fork the repository
2. Create your feature branch
3. Commit your changes
4. Push to the branch
5. Create a new Pull Request

## License

[Your License Here]

## Support

For support, please contact [Your Contact Information]
