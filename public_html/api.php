<?php

$base_pipeline = file_exists(__DIR__ . '/../pipeline/db/Database.php')
    ? __DIR__ . '/../pipeline'
    : __DIR__ . '/pipeline';

require_once $base_pipeline . '/ingest/BormeDownloader.php';
require_once $base_pipeline . '/ingest/StreamingDownloader.php';
require_once $base_pipeline . '/extract/ParserPdf.php';
require_once $base_pipeline . '/ingest/ParserXml.php';
require_once $base_pipeline . '/db/Database.php';

session_start();
header('Content-Type: application/json; charset=utf-8');

$action = $_GET['action'] ?? '';

function openborme_is_web_read_only()
{
    $v = getenv('OPENBORME_WEB_READ_ONLY');
    if ($v === false || trim((string) $v) === '') {
        return false;
    }
    $v = strtolower(trim((string) $v));
    return !in_array($v, ['0', 'false', 'no', 'off'], true);
}

function openborme_normalize_province($province)
{
    $province = strtoupper(trim((string) $province));
    return $province === '' ? 'ALL' : $province;
}

function openborme_period_start($period)
{
    $now = new DateTimeImmutable('now');
    if ($period === 'week') {
        return $now->modify('monday this week')->format('Ymd');
    }
    if ($period === 'month') {
        return $now->format('Ym') . '01';
    }
    if ($period === 'year') {
        return $now->format('Y') . '0101';
    }
    return $now->modify('monday this week')->format('Ymd');
}

function openborme_event_condition($mode)
{
    if ($mode === 'dissolution') {
        return "(is_dissolution = 1 OR normalized_type IN ('DISOLUCION','CESE') OR type LIKE '%Disoluci%' OR type LIKE '%Cese%' OR raw_text LIKE '%Disoluci%' OR raw_text LIKE '%Cese%')";
    }
    return "(is_creation = 1 OR normalized_type = 'CONSTITUCION' OR type LIKE '%Constitu%' OR raw_text LIKE '%Constitu%')";
}

function openborme_fetch_period_rows(PDO $db, $province, $startDate, $mode, $limit = 250)
{
    $eventCondition = openborme_event_condition($mode);
    $where = ["date >= :start_date", $eventCondition];
    $params = [':start_date' => $startDate];

    if ($province !== 'ALL') {
        $where[] = "province = :province";
        $params[':province'] = $province;
    }

    $sql = "SELECT COALESCE(legacy_id, id) AS id, date, province, company_name, company_uid, type, normalized_type, event_group
            FROM borme_acts
            WHERE " . implode(' AND ', $where) . "
            ORDER BY date DESC, id DESC
            LIMIT :limit";

    $stmt = $db->prepare($sql);
    foreach ($params as $k => $v) {
        $stmt->bindValue($k, $v);
    }
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll();
}

function openborme_count_period_rows(PDO $db, $province, $startDate, $mode)
{
    $eventCondition = openborme_event_condition($mode);
    $where = ["date >= :start_date", $eventCondition];
    $params = [':start_date' => $startDate];

    if ($province !== 'ALL') {
        $where[] = "province = :province";
        $params[':province'] = $province;
    }

    $sql = "SELECT COUNT(*) AS total FROM borme_acts WHERE " . implode(' AND ', $where);
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $row = $stmt->fetch();
    return (int) ($row['total'] ?? 0);
}

if ($action === 'new_companies_stats') {
    $db = Database::getInstance();
    $province = openborme_normalize_province($_GET['province'] ?? 'ALL');
    $granularity = strtolower(trim((string) ($_GET['granularity'] ?? 'month')));
    $from = preg_replace('/\D+/', '', (string) ($_GET['from'] ?? '20200101'));
    if (strlen($from) !== 8) {
        $from = '20200101';
    }

    $bucketExpr = "substr(date,1,4) || '-' || substr(date,5,2)";
    if ($granularity === 'day') {
        $bucketExpr = "substr(date,1,4) || '-' || substr(date,5,2) || '-' || substr(date,7,2)";
    }

    $where = ["date >= :from"];
    $params = [':from' => $from];
    if ($province !== 'ALL') {
        $where[] = "province = :province";
        $params[':province'] = $province;
    }

    $sql = "SELECT $bucketExpr AS bucket,
                   SUM(CASE WHEN is_creation = 1 THEN 1 ELSE 0 END) AS creations,
                   SUM(CASE WHEN is_dissolution = 1 THEN 1 ELSE 0 END) AS dissolutions
            FROM borme_acts
            WHERE " . implode(' AND ', $where) . "
            GROUP BY bucket
            ORDER BY bucket ASC";

    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $rows = $stmt->fetchAll();

    echo json_encode([
        'province' => $province,
        'from' => $from,
        'granularity' => $granularity,
        'series' => $rows,
    ]);
    exit;
}

if ($action === 'new_companies_snapshot') {
    $db = Database::getInstance();
    $province = openborme_normalize_province($_GET['province'] ?? 'ALL');
    $limit = max(20, min(1000, (int) ($_GET['limit'] ?? 250)));

    $periods = ['week', 'month', 'year'];
    $payload = ['province' => $province, 'periods' => []];

    foreach ($periods as $period) {
        $startDate = openborme_period_start($period);
        $creations = openborme_count_period_rows($db, $province, $startDate, 'creation');
        $dissolutions = openborme_count_period_rows($db, $province, $startDate, 'dissolution');

        $payload['periods'][$period] = [
            'start_date' => $startDate,
            'creations' => $creations,
            'dissolutions' => $dissolutions,
            'net' => $creations - $dissolutions,
            'new_companies' => openborme_fetch_period_rows($db, $province, $startDate, 'creation', $limit),
            'dissolved_companies' => openborme_fetch_period_rows($db, $province, $startDate, 'dissolution', $limit),
        ];
    }

    echo json_encode($payload);
    exit;
}

if ($action === 'export_new_companies') {
    $db = Database::getInstance();
    $province = openborme_normalize_province($_GET['province'] ?? 'ALL');
    $period = strtolower(trim((string) ($_GET['period'] ?? 'week')));
    if (!in_array($period, ['week', 'month', 'year'], true)) {
        $period = 'week';
    }
    $mode = strtolower(trim((string) ($_GET['mode'] ?? 'creation')));
    if (!in_array($mode, ['creation', 'dissolution'], true)) {
        $mode = 'creation';
    }
    $format = strtolower(trim((string) ($_GET['format'] ?? 'csv')));
    if (!in_array($format, ['csv', 'excel'], true)) {
        $format = 'csv';
    }

    $startDate = openborme_period_start($period);
    $rows = openborme_fetch_period_rows($db, $province, $startDate, $mode, 200000);

    $safeProvince = preg_replace('/[^A-Z0-9_-]+/', '_', $province);
    $baseName = "openborme_{$mode}_{$period}_{$safeProvince}_" . date('Ymd_His');
    $headers = ['date', 'id', 'province', 'company_name', 'company_uid', 'type', 'normalized_type', 'event_group'];

    if ($format === 'csv') {
        header_remove('Content-Type');
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $baseName . '.csv"');
        $out = fopen('php://output', 'w');
        fprintf($out, chr(0xEF) . chr(0xBB) . chr(0xBF));
        fputcsv($out, $headers);
        foreach ($rows as $row) {
            fputcsv($out, [
                $row['date'] ?? '',
                $row['id'] ?? '',
                $row['province'] ?? '',
                $row['company_name'] ?? '',
                $row['company_uid'] ?? '',
                $row['type'] ?? '',
                $row['normalized_type'] ?? '',
                $row['event_group'] ?? '',
            ]);
        }
        fclose($out);
        exit;
    }

    header_remove('Content-Type');
    header('Content-Type: application/vnd.ms-excel; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $baseName . '.xls"');
    echo "<table border='1'><thead><tr>";
    foreach ($headers as $h) {
        echo '<th>' . htmlspecialchars($h) . '</th>';
    }
    echo "</tr></thead><tbody>";
    foreach ($rows as $row) {
        echo '<tr>';
        echo '<td>' . htmlspecialchars((string) ($row['date'] ?? '')) . '</td>';
        echo '<td>' . htmlspecialchars((string) ($row['id'] ?? '')) . '</td>';
        echo '<td>' . htmlspecialchars((string) ($row['province'] ?? '')) . '</td>';
        echo '<td>' . htmlspecialchars((string) ($row['company_name'] ?? '')) . '</td>';
        echo '<td>' . htmlspecialchars((string) ($row['company_uid'] ?? '')) . '</td>';
        echo '<td>' . htmlspecialchars((string) ($row['type'] ?? '')) . '</td>';
        echo '<td>' . htmlspecialchars((string) ($row['normalized_type'] ?? '')) . '</td>';
        echo '<td>' . htmlspecialchars((string) ($row['event_group'] ?? '')) . '</td>';
        echo '</tr>';
    }
    echo "</tbody></table>";
    exit;
}

if ($action === 'start') {
    if (openborme_is_web_read_only()) {
        http_response_code(403);
        echo json_encode(['error' => 'Modo solo lectura activo en web. Ejecuta la ingesta desde Python/CLI.']);
        exit;
    }

    $start = $_GET['start'] ?? null;
    $end = $_GET['end'] ?? null;
    $days = (int) ($_GET['days'] ?? 7);

    $_SESSION['status'] = 'running';
    $_SESSION['progress'] = 0;
    $_SESSION['count'] = 0;

    if ($start && $end) {
        $_SESSION['logs'] = ["Iniciando carga histórica desde $start hasta $end..."];
        $_SESSION['mode'] = 'history';
        $_SESSION['range'] = ['start' => $start, 'end' => $end];
    } else {
        $_SESSION['logs'] = ["Iniciando extracción de los últimos $days días..."];
        $_SESSION['mode'] = 'recent';
        $_SESSION['days'] = $days;
    }

    header('Content-Type: application/json');
    echo json_encode(['status' => 'started']);
    exit;
}

if ($action === 'status') {
    header('Content-Type: application/json');
    echo json_encode([
        'status' => $_SESSION['status'] ?? 'idle',
        'progress' => $_SESSION['progress'] ?? 0,
        'logs' => $_SESSION['logs'] ?? [],
        'count' => $_SESSION['count'] ?? 0,
        'file' => file_exists('borme_data_extract.csv') ? 'borme_data_extract.csv' : null
    ]);
    exit;
}

if ($action === 'get_act') {
    $id = $_GET['id'] ?? '';
    $date = preg_replace('/\D+/', '', (string) ($_GET['date'] ?? ''));
    if (!$id)
        die(json_encode(['error' => 'ID requerido']));

    $db = Database::getInstance();
    $stmt = $db->prepare("SELECT * FROM borme_acts WHERE id = :id OR hash_md5 = :id LIMIT 1");
    $stmt->execute([':id' => $id]);
    $data = $stmt->fetch();

    if (!$data) {
        if (strlen($date) === 8) {
            $stmt = $db->prepare("SELECT * FROM borme_acts WHERE legacy_id = :legacy_id AND date = :date LIMIT 1");
            $stmt->execute([':legacy_id' => $id, ':date' => $date]);
        } else {
            $stmt = $db->prepare("SELECT * FROM borme_acts WHERE legacy_id = :legacy_id ORDER BY date DESC LIMIT 1");
            $stmt->execute([':legacy_id' => $id]);
        }
        $data = $stmt->fetch();
    }

    if (!$data) {
        die(json_encode(['error' => 'Acta no encontrada en la base de datos']));
    }

    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

if ($action === 'search') {
    $q = $_GET['q'] ?? '';
    $cif = $_GET['cif'] ?? '';
    $province = $_GET['province'] ?? '';
    $section = $_GET['section'] ?? '';
    $type = $_GET['type'] ?? '';
    $date_from = $_GET['date_from'] ?? '';
    $date_to = $_GET['date_to'] ?? '';
    $capital_min = $_GET['capital_min'] ?? '';
    $capital_max = $_GET['capital_max'] ?? '';
    $page = max(1, (int) ($_GET['page'] ?? 1));
    $page_size = max(1, min(200, (int) ($_GET['page_size'] ?? 50)));
    $offset = ($page - 1) * $page_size;
    $sort = $_GET['sort'] ?? 'date_desc';

    if (strlen($q) < 3 && empty($cif))
        die(json_encode(['error' => 'Búsqueda muy corta (usa al menos 3 caracteres o CIF)']));

    $db = Database::getInstance();
    $driver = Database::getDriver();
    $conditions = [];
    $params = [];
    $capitalNumericExpr = "CAST(NULLIF(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(CAST(capital AS TEXT)), 'euros', ''), '.', ''), ',', '.'), ' ', ''), '') AS REAL)";
    if ($driver === 'pgsql') {
        $capitalNumericExpr = "CAST(REPLACE(REPLACE(NULLIF(regexp_replace(lower(CAST(capital AS TEXT)), '[^0-9,.-]', '', 'g'), ''), '.', ''), ',', '.') AS DOUBLE PRECISION)";
    }

    if ($q) {
        $conditions[] = "(company_name LIKE :q OR company_uid LIKE :q OR raw_text LIKE :q)";
        $params[':q'] = "%$q%";
    }
    if ($cif) {
        $conditions[] = "company_uid = :cif";
        $params[':cif'] = $cif;
    }
    if ($province) {
        $conditions[] = "province = :province";
        $params[':province'] = strtoupper($province);
    }
    if ($section && in_array($section, ['A', 'B', 'C'])) {
        $conditions[] = "section = :section";
        $params[':section'] = $section;
    }
    if ($type) {
        $conditions[] = "type LIKE :type";
        $params[':type'] = "%$type%";
    }
    if ($date_from) {
        $conditions[] = "date >= :date_from";
        $params[':date_from'] = $date_from;
    }
    if ($date_to) {
        $conditions[] = "date <= :date_to";
        $params[':date_to'] = $date_to;
    }
    if ($capital_min !== '') {
        $conditions[] = "$capitalNumericExpr >= :capmin";
        $params[':capmin'] = (float) $capital_min;
    }
    if ($capital_max !== '') {
        $conditions[] = "$capitalNumericExpr <= :capmax";
        $params[':capmax'] = (float) $capital_max;
    }

    $where = $conditions ? ('WHERE ' . implode(' AND ', $conditions)) : '';
    $order = 'ORDER BY date DESC';
    if ($sort === 'date_asc') $order = 'ORDER BY date ASC';
    // Nota: relevancia pendiente; por ahora usamos fecha desc

    // Total count
    $count_stmt = $db->prepare("SELECT COUNT(*) as total FROM borme_acts $where");
    $count_stmt->execute($params);
    $total = (int) ($count_stmt->fetch()['total'] ?? 0);

    // Results
    $sql = "SELECT COALESCE(legacy_id, id) AS id, id AS canonical_id, date, type, province, company_name, company_uid, capital, raw_text 
            FROM borme_acts 
            $where
            $order
            LIMIT :limit OFFSET :offset";
    $stmt = $db->prepare($sql);
    foreach ($params as $k => $v) {
        $stmt->bindValue($k, $v);
    }
    $stmt->bindValue(':limit', $page_size, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $results = $stmt->fetchAll();

    echo json_encode([
        'total' => $total,
        'page' => $page,
        'page_size' => $page_size,
        'results' => $results
    ]);
    exit;
}

if ($action === 'timeline') {
    $cif = $_GET['cif'] ?? '';
    $name = $_GET['name'] ?? '';
    if (!$cif && !$name)
        die(json_encode(['error' => 'CIF o Nombre requerido']));

    $db = Database::getInstance();
    if ($cif) {
        $stmt = $db->prepare("SELECT * FROM borme_acts WHERE company_uid = :cif ORDER BY date ASC");
        $stmt->execute([':cif' => $cif]);
    } else {
        $stmt = $db->prepare("SELECT * FROM borme_acts WHERE company_name = :name ORDER BY date ASC");
        $stmt->execute([':name' => $name]);
    }

    header('Content-Type: application/json');
    echo json_encode($stmt->fetchAll());
    exit;
}

if ($action === 'top_capital') {
    $db = Database::getInstance();
    // Extraemos los números del capital usando CAST tras limpiar, o simple sort texto x longitud temporal
    // Para SQLite, ordenamos por la longitud del capital y el valor alfanumérico crudo de forma básica
    $stmt = $db->prepare("
        SELECT id, date, company_name, province, capital, type 
        FROM borme_acts 
        WHERE capital IS NOT NULL AND capital != '' 
        ORDER BY length(CAST(capital AS TEXT)) DESC, CAST(capital AS TEXT) DESC LIMIT 10
    ");
    $stmt->execute();

    header('Content-Type: application/json');
    echo json_encode($stmt->fetchAll());
    exit;
}

if ($action === 'radar') {
    $db = Database::getInstance();
    $stmt = $db->prepare("
        SELECT province, COUNT(*) as total_acts,
               SUM(CASE WHEN (is_creation = 1 OR normalized_type = 'CONSTITUCION' OR type LIKE '%Constitu%' OR raw_text LIKE '%Constitu%') THEN 1 ELSE 0 END) as nuevas,
               SUM(CASE WHEN (is_dissolution = 1 OR normalized_type IN ('DISOLUCION','CESE') OR type LIKE '%Disoluci%' OR type LIKE '%Cese%' OR raw_text LIKE '%Disoluci%' OR raw_text LIKE '%Cese%') THEN 1 ELSE 0 END) as cierres
        FROM borme_acts
        GROUP BY province
        ORDER BY total_acts DESC
        LIMIT 15
    ");
    $stmt->execute();

    header('Content-Type: application/json');
    echo json_encode($stmt->fetchAll());
    exit;
}

if ($action === 'process_step') {
    if (openborme_is_web_read_only()) {
        http_response_code(403);
        echo json_encode(['error' => 'Modo solo lectura activo en web. Ejecuta la ingesta desde Python/CLI.']);
        exit;
    }

    $mode = $_SESSION['mode'] ?? 'recent';
    $current_count = $_SESSION['count'] ?? 0;

    $tmp_dir = sys_get_temp_dir() . '/openborme_api_tmp';
    if (!is_dir($tmp_dir)) {
        mkdir($tmp_dir, 0777, true);
    }
    $downloader = new StreamingDownloader($tmp_dir);

    if ($mode === 'history') {
        $start = $_SESSION['range']['start'];
        $end = $_SESSION['range']['end'];

        $current_date_ts = strtotime("$start +$current_count days");
        $end_ts = strtotime($end);

        if ($current_date_ts <= $end_ts) {
            $date = date('Ymd', $current_date_ts);
            $_SESSION['logs'][] = "Procesando fecha histórica: $date...";

            // This is where we'd call processDate, but we need to capture output or just run it.
            // For now, let's use the Downloader.
            try {
                // We use a trick to instantiate the private method or just download specifically.
                // Let's call downloadRange for just this day.
                $downloader->downloadRange(date('Y-m-d', $current_date_ts), date('Y-m-d', $current_date_ts));

                $_SESSION['count']++;
                $total_days = (int) (($end_ts - strtotime($start)) / 86400) + 1;
                $_SESSION['progress'] = min(100, round(($_SESSION['count'] / $total_days) * 100));
            } catch (Exception $e) {
                $_SESSION['logs'][] = "Error en $date: " . $e->getMessage();
            }
        } else {
            $_SESSION['status'] = 'done';
            $_SESSION['progress'] = 100;
            $_SESSION['logs'][] = "¡Carga histórica completada!";
        }
    } else {
        // Recent mode logic
        $days = $_SESSION['days'] ?? 7;
        if ($current_count < $days) {
            $date = date('Ymd', strtotime("-$current_count days"));
            $_SESSION['logs'][] = "Ingestando boletín reciente: $date...";
            $downloader->downloadRange(date('Y-m-d', strtotime("-$current_count days")), date('Y-m-d', strtotime("-$current_count days")));
            $_SESSION['count']++;
            $_SESSION['progress'] = min(100, round(($_SESSION['count'] / $days) * 100));
        } else {
            $_SESSION['status'] = 'done';
            $_SESSION['progress'] = 100;
            $_SESSION['logs'][] = "¡Extracción reciente completada!";
        }
    }

    header('Content-Type: application/json');
    echo json_encode(['status' => 'ok']);
    exit;
}
