<?php
require_once 'config.php';

$dateFrom = '2026-09-01';
$dateTo = '2026-09-18';
$stmt = db()->prepare("
    SELECT DATE_FORMAT(o.created_at,'%M %Y') period,
           COUNT(DISTINCT o.id) transactions,
           COALESCE(SUM(od.quantity), 0) items_sold,
           COALESCE(SUM(od.subtotal), 0) revenue
    FROM orders o
    LEFT JOIN order_details od ON od.order_id = o.id
    WHERE o.status='Selesai'
      AND DATE(o.created_at) BETWEEN ? AND ?
    GROUP BY DATE_FORMAT(o.created_at,'%Y-%m')
");
$stmt->execute([$dateFrom, $dateTo]);
$monthly = $stmt->fetchAll();
echo "Monthly:\n";
print_r($monthly);

// Check per-product sold in the period
$prodStmt = db()->prepare("
    SELECT p.id, p.name, p.unit, c.name as category_name,
           COALESCE(SUM(od.quantity), 0) as total_sold,
           COALESCE(SUM(od.subtotal), 0) as total_amount
    FROM order_details od
    JOIN orders o ON o.id = od.order_id
    JOIN products p ON p.id = od.product_id
    LEFT JOIN categories c ON c.id = p.category_id
    WHERE o.status = 'Selesai'
      AND DATE(o.created_at) BETWEEN ? AND ?
    GROUP BY p.id, p.name, p.unit, c.name
    ORDER BY total_sold DESC
");
$prodStmt->execute([$dateFrom, $dateTo]);
$productsSold = $prodStmt->fetchAll();
echo "Products sold:\n";
print_r($productsSold);
