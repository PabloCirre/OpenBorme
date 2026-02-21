<?php

$base_pipeline = file_exists(__DIR__ . '/../pipeline/db/Database.php')
    ? __DIR__ . '/../pipeline'
    : __DIR__ . '/pipeline';

require_once $base_pipeline . '/ingest/BormeDownloader.php';
require_once $base_pipeline . '/extract/ParserPdf.php';
require_once $base_pipeline . '/ingest/ParserXml.php';
require_once $base_pipeline . '/db/Database.php';

session_start();

$action = $_GET['action'] ?? '';

if ($action === 'start') {
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
    if (!$id)
        die(json_encode(['error' => 'ID requerido']));

    $db = Database::getInstance();
    $stmt = $db->prepare("SELECT * FROM borme_acts WHERE id = :id OR hash_md5 = :id LIMIT 1");
    $stmt->execute([':id' => $id]);
    $data = $stmt->fetch();

    if (!$data) {
        die(json_encode(['error' => 'Acta no encontrada en la base de datos']));
    }

    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

if ($action === 'search') {
    $q = $_GET['q'] ?? '';
    if (strlen($q) < 3)
        die(json_encode(['error' => 'Búsqueda muy corta']));

    $db = Database::getInstance();
    // Búsqueda inteligente: Nombre de empresa, CIF o contenido
    $stmt = $db->prepare("
        SELECT id, date, type, province, company_name, company_uid, capital 
        FROM borme_acts 
        WHERE company_name LIKE :q 
           OR company_uid LIKE :q 
           OR raw_text LIKE :q
        ORDER BY date DESC LIMIT 50
    ");
    $stmt->execute([':q' => "%$q%"]);
    $results = $stmt->fetchAll();

    header('Content-Type: application/json');
    echo json_encode($results);
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
        ORDER BY length(capital) DESC, capital DESC LIMIT 10
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
               SUM(CASE WHEN type LIKE '%Constitu%' THEN 1 ELSE 0 END) as nuevas,
               SUM(CASE WHEN type LIKE '%Disoluci%' OR type LIKE '%Cese%' THEN 1 ELSE 0 END) as cierres
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
    $mode = $_SESSION['mode'] ?? 'recent';
    $current_count = $_SESSION['count'] ?? 0;

    $downloader = new BormeDownloader(__DIR__ . "/../data");

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
            $_SESSION['logs'][] = "Descargando boletín reciente: $date...";
            $downloader->downloadRange(date('Y-m-d', strtotime("-$current_count days")), date('Y-m-d', strtotime("-$current_count days")));
            $_SESSION['count']++;
            $_SESSION['progress'] = min(100, round(($_SESSION['count'] / $days) * 100));
        } else {
            $_SESSION['status'] = 'done';
            $_SESSION['progress'] = 100;
        }
    }

    header('Content-Type: application/json');
    echo json_encode(['status' => 'ok']);
    exit;
}
