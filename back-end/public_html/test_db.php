<?php
try {
    $pdo = new PDO("mysql:host=127.0.0.1;port=3307;dbname=staff_db", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $stmt = $pdo->query("SELECT id, nama_template, teks_statis FROM template_ijazah");
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $output = ['success' => true, 'data' => $results];
    file_put_contents(__DIR__ . '/db_output.json', json_encode($output, JSON_PRETTY_PRINT));
    echo "SUCCESS";
} catch (Exception $e) {
    $output = ['success' => false, 'error' => $e->getMessage()];
    file_put_contents(__DIR__ . '/db_output.json', json_encode($output, JSON_PRETTY_PRINT));
    echo "ERROR: " . $e->getMessage();
}
