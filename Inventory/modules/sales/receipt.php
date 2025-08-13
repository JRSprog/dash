<?php
require_once '../../includes/config.php';
require_once '../../includes/auth-check.php';
require_once '../../includes/functions.php';

// Validate sale ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: index.php?error=Invalid sale ID");
    exit();
}

$sale_id = (int)$_GET['id'];
$sale = getSaleById($sale_id);

if (!$sale) {
    header("Location: index.php?error=Sale not found");
    exit();
}

// Get company settings with fallbacks
$company_info = [
    'name' => 'My Store',
    'address' => '123 Main St, City',
    'phone' => '123-456-7890',
    'footer' => 'Thank you for your purchase!'
];

try {
    global $pdo;
    $stmt = $pdo->query("SELECT `key`, value FROM settings WHERE `key` IN ('company_name','company_address','company_phone','receipt_footer')");
    while ($row = $stmt->fetch()) {
        $company_info[str_replace('company_', '', $row['key'])] = $row['value'];
    }
} catch (Exception $e) {
    error_log("Settings error: " . $e->getMessage());
}

// Calculate totals
$subtotal = $sale['total_amount'];
$discount = $sale['discount'] ?? 0;
$tax = $sale['tax_amount'] ?? 0;
$grandTotal = $subtotal - $discount + $tax;
$amountPaid = $sale['amount_paid'];
$changeDue = max(0, $amountPaid - $grandTotal);

header("Content-Type: text/html; charset=UTF-8");
?><!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <link rel="icon" href="../../assets/images/jrs.png" type="x-icon">
    <title>Receipt #<?= htmlspecialchars($sale['invoice_no']) ?></title>
    <style>
        /* Thermal printer optimized centered styles */
        @page { margin: 0; size: 80mm auto; }
        body { 
            font-family: 'Courier New', monospace;
            font-size: 12px;
            width: 80mm;
            margin: 0 auto;
            padding: 2mm;
            line-height: 1.2;
            color: #000;
            text-align: center;
        }
        .content-wrapper {
            width: 100%;
            max-width: 72mm; /* Slightly narrower for better centering */
            margin: 0 auto;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-bold { font-weight: bold; }
        .divider { 
            border-top: 1px dashed #000;
            margin: 3px auto;
            width: 100%;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 0 auto;
        }
        .items td { padding: 1px 0; }
        .totals td { padding: 2px 0; }
        .totals tr:last-child td {
            padding-bottom: 5px;
        }
        @media print {
            .no-print { display: none !important; }
            body { font-size: 11px !important; padding: 0; }
            button { display: none !important; }
        }
    </style>
</head>
<body>
    <div class="content-wrapper">
        <!-- Print controls (hidden when printing) -->
        <div class="no-print" style="margin-bottom:5px;">
            <button onclick="window.print()" style="padding:3px 8px;">Print Receipt</button>
            <button onclick="window.close()" style="padding:3px 8px;">Close</button>
        </div>

        <!-- Receipt Header -->
        <div class="text-bold" style="font-size:14px; margin-top:20px;">
            JRSPro Store
        </div>
        <div>
            <?= htmlspecialchars($company_info['address']) ?><br>
            Tel: <?= htmlspecialchars($company_info['phone']) ?>
        </div>
        <div class="divider"></div>

        <!-- Sale Info -->
        <table>
            <tr>
                <td class="text-center">Invoice: <strong>#<?= htmlspecialchars($sale['invoice_no']) ?></strong></td>
            </tr>
            <tr>
                <td class="text-center">Date: <?= date('m/d/Y H:i', strtotime($sale['created_at'])) ?></td>
            </tr>
            <tr>
                <td class="text-center">Cashier: <?= htmlspecialchars($sale['created_by_name'] ?? $sale['created_by_username'] ?? 'System') ?></td>
            </tr>
            <tr>
                <td class="text-center">Method: <?= ucfirst($sale['payment_method']) ?></td>
            </tr>
        </table>
        <div class="divider"></div>

        <!-- Items List -->
        <table class="items">
            <thead>
                <tr>
                    <th class="text-center">Item</th>
                    <th class="text-center">Price</th>
                    <th class="text-center">Qty</th>
                    <th class="text-center">Total</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($sale['items'] as $item): ?>
                <tr>
                    <td class="text-center"><?= htmlspecialchars($item['product_name']) ?></td>
                    <td class="text-center">₱<?= number_format($item['unit_price'], 2) ?></td>
                    <td class="text-center"><?= $item['quantity'] ?></td>
                    <td class="text-center">₱<?= number_format($item['total_price'], 2) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <div class="divider"></div>

        <!-- Totals -->
        <table class="totals">
            <tr>
                <td class="text-center">Subtotal:</td>
                <td class="text-center">₱<?= number_format($subtotal, 2) ?></td>
            </tr>
            <?php if ($discount > 0): ?>
            <tr>
                <td class="text-center">Discount:</td>
                <td class="text-center">-₱<?= number_format($discount, 2) ?></td>
            </tr>
            <?php endif; ?>
            <?php if ($tax > 0): ?>
            <tr>
                <td class="text-center">Tax:</td>
                <td class="text-center">₱<?= number_format($tax, 2) ?></td>
            </tr>
            <?php endif; ?>
            <tr class="text-bold">
                <td class="text-center">Grand Total:</td>
                <td class="text-center">₱<?= number_format($grandTotal, 2) ?></td>
            </tr>
            <tr>
                <td class="text-center">Amount Paid:</td>
                <td class="text-center">₱<?= number_format($amountPaid, 2) ?></td>
            </tr>
            <tr>
                <td class="text-center">Change Due:</td>
                <td class="text-center">₱<?= number_format($changeDue, 2) ?></td>
            </tr>
        </table>

        <!-- Footer -->
        <div class="divider"></div>
        <div style="margin-top:5px;">
            <?= htmlspecialchars($company_info['footer']) ?><br>
            <?= date('Y') ?> &copy; JRSPro Store
        </div>
    </div>

    <script>
        // Auto-print with slight delay
        window.onload = function() {
            setTimeout(function() {
                window.print();
                // Optional: close after printing
                // window.onafterprint = function() { window.close(); };
            }, 300);
        };
    </script>
</body>
</html>