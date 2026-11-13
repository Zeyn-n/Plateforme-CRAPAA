<?php
session_start();
require_once '../config/db.php';

// Seuls les modérateurs et administrateurs peuvent exporter les données
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'], ['moderateur', 'admin', 'super_admin'])) {
    header('Location: ../connexion.php');
    exit;
}

$type = $_GET['type'] ?? 'structures';

if ($type === 'professionnels') {
    filename:
    $filename = 'export_professionnels_crapaa_' . date('Y-m-d') . '.csv';
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=' . $filename);
    
    $output = fopen('php://output', 'w');
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF)); // BOM UTF-8 pour Excel
    
    fputcsv($output, ['ID', 'Nom', 'Prénom', 'Diplôme', 'Spécialités', 'Code Postal', 'Ville', 'Téléphone', 'Email', 'Statut'], ';');
    
    $stmt = $pdo->query("SELECT id, nom, prenom, diplome, specialites, code_postal, ville, telephone, email, statut FROM professionnels");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        fputcsv($output, $row, ';');
    }
    fclose($output);
    exit;
} else {
    $filename = 'export_structures_crapaa_' . date('Y-m-d') . '.csv';
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=' . $filename);
    
    $output = fopen('php://output', 'w');
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF)); // BOM UTF-8 pour Excel
    
    fputcsv($output, ['ID', 'Nom de la structure', 'Type d etablissement', 'Code Postal', 'Ville', 'Téléphone', 'Email', 'Statut'], ';');
    
    $stmt = $pdo->query("SELECT id, nom, type_etablissement, code_postal, ville, telephone, email, statut FROM structures");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        fputcsv($output, $row, ';');
    }
    fclose($output);
    exit;
}