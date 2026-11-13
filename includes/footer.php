<?php
$is_admin_dir = (strpos($_SERVER['PHP_SELF'], '/admin/') !== false);
$base_path = $is_admin_dir ? '../' : '';
?>
    </main>
    <footer class="site-footer">
        <div class="footer-grid">
            <div>
                <h3>CRAPAA</h3>
                <p>Centre de Ressources des Activités Physiques Adaptées d’Alsace.<br>Plateforme régionale de référence pour étudiants, professionnels et grand public.</p>
            </div>
            <div>
                <h4>Navigation rapide</h4>
                <ul>
                    <li><a href="<?= $base_path ?>decouvrir.php">Découvrir l'APA</a></li>
                    <li><a href="<?= $base_path ?>ressources.php">Bibliothèque scientifique</a></li>
                    <li><a href="<?= $base_path ?>annuaire.php">Annuaire & Carte interactive</a></li>
                    <li><a href="<?= $base_path ?>contribuer.php">Proposer un contenu</a></li>
                </ul>
            </div>
            <div>
                <h4>Contact & Légal</h4>
                <ul>
                    <li><a href="<?= $base_path ?>contact.php">Nous contacter</a></li>
                    <li><a href="<?= $base_path ?>cgu.php">Mentions légales & CGU</a></li>
                    <li><a href="<?= $base_path ?>cgu.php#rgpd">Politique de confidentialité (RGPD)</a></li>
                </ul>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; <?= date('Y') ?> CRAPAA - Université de Strasbourg / Master 2 APAS. Tous droits réservés.</p>
        </div>
    </footer>
</body>
</html>