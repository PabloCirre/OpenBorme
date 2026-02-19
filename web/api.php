<?php

require_once __DIR__ . '/../pipeline/ingest/BormeDownloader.php';
require_once __DIR__ . '/../pipeline/extract/ParserPdf.php';
require_once __DIR__ . '/../pipeline/ingest/ParserXml.php';

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

    require_once __DIR__ . '/../pipeline/extract/ParserPdf.php';
    require_once __DIR__ . '/../pipeline/ingest/ParserXml.php';

    $storage_dir = __DIR__ . "/../data";
    $found_file = null;
    $type = (strpos($id, '-A-') !== false) ? 'pdf' : 'xml';
    $section_dir = ($type === 'pdf') ? 'section_A' : 'section_C';

    $dates = array_filter(scandir($storage_dir), function ($item) use ($storage_dir) {
        return is_dir("$storage_dir/$item") && preg_match('/^\d{8}$/', $item);
    });

    foreach ($dates as $date) {
        $path = "$storage_dir/$date/$section_dir/$id.$type";
        if (file_exists($path)) {
            $found_file = $path;
            break;
        }
    }

    if (!$found_file) {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Acta no encontrada']);
        exit;
    }

    if ($type === 'pdf') {
        $parser = new ParserPdf();
        $results = $parser->parse($found_file, "Provincia");
        $data = $results[0] ?? [];
    } else {
        $parser = new ParserXml();
        $data = $parser->parse($found_file);
    }

    header('Content-Type: application/json');
    echo json_encode($data);
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
