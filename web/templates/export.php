<?php
// templates/export.php
// Simplified export logic for web users

$query = $_GET['q'] ?? '';
$format = $_GET['format'] ?? 'csv';

if ($format === 'csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="export_openborme_' . date('Ymd') . '.csv"');

    $output = fopen('php://output', 'w');
    // Add UTF-8 BOM for Excel
    fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

    fputcsv($output, ['Fecha', 'ID Acto', 'Empresa', 'Tipo', 'Provincia']);

    // Sample data (In a real scenario, this would query the DB/Filesystem)
    fputcsv($output, [date('Y-m-d'), 'BORME-A-2026-SAMPLE', 'Empresa de Prueba SL', 'CONSTITUCION', 'MADRID']);

    fclose($output);
    exit;
}

echo "<div class='card' style='padding: 2rem; border-top: 5px solid var(--boe-red);'>";
echo "<h3>Error de Formato</h3>";
echo "<p>El formato <strong>" . htmlspecialchars($format) . "</strong> no está disponible actualmente vía web.</p>";
echo "<p>Por favor, use el formato <strong>CSV</strong> o consulte nuestra <a href='/api'>API de Desarrolladores</a> para formatos JSON.</p>";
echo "</div>";
