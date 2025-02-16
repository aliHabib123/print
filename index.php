<?php

// Load bootstrap configuration
require_once __DIR__ . '/config/bootstrap.php';

use Mike42\Escpos\PrintConnectors\CupsPrintConnector;
use Mike42\Escpos\Printer;
use Mike42\Escpos\EscposImage;

/**
 * Format LBP price by removing currency symbol and commas
 *
 * @param string $price Price string in LBP format
 * @return string Cleaned price string
 */
function formatLBPPrice($price)
{
    $price = trim(str_replace('LBP', '', $price));
    $price = str_replace(',', '', $price);
    return $price;
}

/**
 * Format price for display with LBP currency
 *
 * @param string|float $price Price to format
 * @return string Formatted price with LBP
 */
function formatPriceForDisplay($price)
{
    if (is_string($price) && strpos($price, 'LBP') !== false) {
        return $price;
    }
    return number_format($price) . ' LBP';
}

/**
 * Print a single receipt
 *
 * @param Printer $printer ESC/POS Printer instance
 * @param array $data Receipt data
 * @param string $copyType Type of copy (e.g., "MERCHANT COPY")
 */
function printReceipt($printer, $data, $copyType = '')
{
    // Initialize printer
    $printer->initialize();

    // Print logo if no copy type (second copy only)
    if (!$copyType) {
        printLogo($printer);
    }

    // Print header
    printHeader($printer, $data, $copyType);

    // Print customer details
    printCustomerDetails($printer, $data);

    // Print items
    printItems($printer, $data);

    // Print totals
    printTotals($printer, $data);

    // Print footer
    printFooter($printer);
}

/**
 * Print logo if available
 *
 * @param Printer $printer
 */
function printLogo($printer)
{
    $logoPath = './images/logo.png';
    if (file_exists($logoPath)) {
        $logo = EscposImage::load($logoPath);
        $printer->feed();
        $printer->setJustification(Printer::JUSTIFY_CENTER);
        $printer->bitImage($logo);
        $printer->feed();
    }
}

/**
 * Print receipt header
 *
 * @param Printer $printer
 * @param array $data
 * @param string $copyType
 */
function printHeader($printer, $data, $copyType)
{
    $printer->feed();
    
    $printer->text(str_repeat("=", RECEIPT_WIDTH) . "\n");
    $printer->setJustification(Printer::JUSTIFY_CENTER);

    if ($copyType) {
        $printer->setJustification(Printer::JUSTIFY_CENTER);
        $printer->feed();
        $printer->selectPrintMode(Printer::MODE_EMPHASIZED);
        $printer->selectPrintMode(Printer::MODE_DOUBLE_HEIGHT);
        $printer->text("*** " . $copyType . " ***\n");
        $printer->selectPrintMode();
        $printer->feed();
        $printer->text(str_repeat("=", RECEIPT_WIDTH) . "\n");
    }

    $printer->text($data['company_name'] . "\n");
    $printer->text("Branch: " . $data['branch_name'] . "\n");
    $printer->text("Tel: " . $data['phone'] . "\n");
    $printer->text(str_repeat("=", RECEIPT_WIDTH) . "\n");
}

/**
 * Print customer details section
 *
 * @param Printer $printer
 * @param array $data
 */
function printCustomerDetails($printer, $data)
{
    $printer->feed();
    $printer->setJustification(Printer::JUSTIFY_LEFT);
    $printer->text("Invoice #: " . $data['invoice_number'] . "\n");
    $printer->text("Date: " . $data['date'] . "\n");
    $printer->text("Customer: " . $data['customer_name'] . "\n\n");
}

/**
 * Print items section
 *
 * @param Printer $printer
 * @param array $data
 */
function printItems($printer, $data)
{
    $printer->text("ITEMS:\n");
    $printer->text(str_repeat("-", RECEIPT_WIDTH) . "\n");
    $printer->feed();

    foreach ($data['items'] as $index => $item) {
        $printer->text(($index + 1) . ". " . $item['name'] . " - QTY " . $item['quantity'] . "\n");
        $printer->text("Unit Price: " . formatPriceForDisplay($item['price']) . "\n");
        $printer->text("Total: " . formatPriceForDisplay($item['total']) . "\n\n");
    }
}

/**
 * Print totals section
 *
 * @param Printer $printer
 * @param array $data
 */
function printTotals($printer, $data)
{
    $printer->text(str_repeat("-", RECEIPT_WIDTH) . "\n");
    $printer->setJustification(Printer::JUSTIFY_RIGHT);

    // Format values
    $subtotal = formatPriceForDisplay($data['subtotal']);
    $discount = formatPriceForDisplay($data['discount']);
    $grandTotal = formatPriceForDisplay($data['grand_total']);

    // Pad values for alignment
    $maxValueLength = 15;
    $subtotal = str_pad($subtotal, $maxValueLength, " ", STR_PAD_LEFT);
    $discount = str_pad($discount, $maxValueLength, " ", STR_PAD_LEFT);
    $grandTotal = str_pad($grandTotal, $maxValueLength, " ", STR_PAD_LEFT);

    // Print totals
    $printer->text(str_pad("Subtotal:", 25, " ", STR_PAD_LEFT) . $subtotal . "\n");
    $printer->text(str_pad("Discount:", 25, " ", STR_PAD_LEFT) . $discount . "\n");
    $printer->text(str_pad("Grand Total:", 25, " ", STR_PAD_LEFT) . $grandTotal . "\n");
}

/**
 * Print receipt footer
 *
 * @param Printer $printer
 */
function printFooter($printer)
{
    $printer->setJustification(Printer::JUSTIFY_CENTER);
    $printer->feed();
    $printer->text("Thank you for your business!\n");
    $printer->feed(2);
}

// Main execution
try {
    // Validate request method
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Method not allowed');
    }

    // Validate API key
    $headers = getallheaders();
    $api_key = isset($headers['X-API-Key']) ? $headers['X-API-Key'] : '';
    if ($api_key !== API_KEY) {
        throw new Exception('Invalid API key');
    }

    // Get and validate JSON input
    $json_input = file_get_contents('php://input');
    $data = json_decode($json_input, true);

    // Validate required fields
    $required_fields = ['customer_name', 'date', 'invoice_number', 'items', 'subtotal', 'discount', 'grand_total'];
    foreach ($required_fields as $field) {
        if (!isset($data[$field])) {
            throw new Exception("Missing required field: $field");
        }
    }

    // Initialize printer
    $printerList = shell_exec('lpstat -p');
    if (stripos($printerList, PRINTER_NAME) === false) {
        throw new Exception("Printer not found");
    }

    $connector = new CupsPrintConnector(PRINTER_NAME);
    $printer = new Printer($connector);

    // Print both copies
    printReceipt($printer, $data, "MERCHANT COPY");
    $printer->cut();
    printReceipt($printer, $data);
    $printer->cut();

    // Close printer connection
    $printer->close();

    // Send success response
    http_response_code(200);
    echo json_encode(['success' => true, 'message' => 'Invoice printed successfully']);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
