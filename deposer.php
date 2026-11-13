<?php
require_once 'config/db.php';

$message = '';
$erreur = '';

// Traitement de l'envoi du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titre = trim($_POST['titre'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $type_ressource = $_POST['type_ressource'] ?? '';
    $auteur_id = 1; // ID de l'utilisateur de test créé en BDD (Jean Dupont)

    // Vérification des champs texte
    if (empty($titre) || empty($type_ressource)) {
        $erreur = "Le titre et le type de ressource sont obligatoires.";
    } elseif (!isset($_FILES['fichier']) || $_FILES['fichier']['error'] !== UPLOAD_ERR_OK) {
        $erreur = "Veuillez sélectionner un fichier valide.";
    } else {
        $fileTmpPath = $_FILES['fichier']['tmp_name'];
        $fileName = $_FILES['fichier']['name'];
        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        // Formats autorisés
        $allowedExtensions = ['pdf', 'doc', 'docx', 'ppt', 'pptx'];

        if (!in_array($fileExtension, $allowedExtensions)) {
            $erreur = "Format non autorisé. Merci d'envoyer un fichier PDF, DOC, DOCX, PPT ou PPTX.";
        } else {
            // Renommer le fichier pour éviter les doublons (ex: 65f1234a56_mon_fichier.pdf)
            $newFileName = uniqid() . '_' . preg_replace('/[^a-zA-Z0-9_.-]/', '_', $fileName);
            $uploadFileDir = 'uploads/';
            $dest_path = $uploadFileDir . $newFileName;

            // Déplacement du fichier du dossier temporaire vers /uploads/
            if (move_uploaded_file($fileTmpPath, $dest_path)) {
                // Insertion dans la base de données
                $sql = "INSERT INTO ressources (titre, description, type_ressource, fichier_path, auteur_id) 
                        VALUES (:titre, :description, :type_ressource, :fichier_path, :auteur_id)";
                $stmt = $pdo->prepare($sql);
                
                if ($stmt->execute([
                    ':titre' => $titre,
                    ':description' => $description,
                    ':type_ressource' => $type_ressource,
                    ':fichier_path' => $dest_path,
                    ':auteur_id' => $auteur_id
                ])) {
                    $message = "Le document a été déposé avec succès et est en attente de validation !";
                } else {
                    $erreur = "Erreur lors de l'enregistrement en base de données.";
                }
            } else {
                $erreur = "Erreur lors du déplacement du fichier vers le dossier /uploads/. Vérifiez les permissions.";
            }
        }
    }
}

include 'includes/header.php';
?>

<section class="hero">
    <h1>Déposer une ressource</h1>
    <p>Partagez vos mémoires, fiches techniques ou cours avec la communauté APA d'Alsace.</p>
</section>

<?php if ($message): ?>
    <div style="background-color: #d4edda; color: #155724; padding: 1rem; border-radius: 4px; margin-bottom: 1rem;">
        <?= htmlspecialchars($message) ?>
    </div>
<?php endif; ?>

<?php if ($erreur): ?>
    <div style="background-color: #f8d7da; color: #721c24; padding: 1rem; border-radius: 4px; margin-bottom: 1rem;">
        <?= htmlspecialchars($erreur) ?>
    </div>
<?php endif; ?>

<form action="deposer.php" method="POST" enctype="multipart/form-data" style="background: #fff; padding: 2rem; border-radius: 8px; max-width: 600px; margin: 0 auto; box-shadow: 0 2px 5px rgba(0,0,0,0.05);">
    <div style="margin-bottom: 1rem;">
        <label for="titre" style="display: block; font-weight: bold; margin-bottom: .5rem;">Titre du document *</label>
        <input type="text" id="titre" name="titre" required style="width: 100%; padding: 0.5rem; border: 1px solid #ccc; border-radius: 4px;">
    </div>

    <div style="margin-bottom: 1rem;">
        <label for="type_ressource" style="display: block; font-weight: bold; margin-bottom: .5rem;">Type de ressource *</label>
        <select id="type_ressource" name="type_ressource" required style="width: 100%; padding: 0.5rem; border: 1px solid #ccc; border-radius: 4px;">
            <option value="">-- Sélectionner un type --</option>
            <option value="fiche_technique">Fiche technique</option>
            <option value="memoire">Mémoire</option>
            <option value="these">Thèse</option>
            <option value="article">Article scientifique</option>
            <option value="cours">Support de cours</option>
        </select>
    </div>

    <div style="margin-bottom: 1rem;">
        <label for="description" style="display: block; font-weight: bold; margin-bottom: .5rem;">Description</label>
        <textarea id="description" name="description" rows="4" style="width: 100%; padding: 0.5rem; border: 1px solid #ccc; border-radius: 4px;"></textarea>
    </div>

    <div style="margin-bottom: 1.5rem;">
        <label for="fichier" style="display: block; font-weight: bold; margin-bottom: .5rem;">Fichier (PDF, DOCX, PPTX) *</label>
        <input type="file" id="fichier" name="fichier" required style="width: 100%;">
    </div>

    <button type="submit" class="btn-login" style="width: 100%; border: none; cursor: pointer; padding: 0.75rem;">Déposer le document</button>
</form>

<?php include 'includes/footer.php'; ?>