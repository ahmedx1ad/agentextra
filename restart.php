<?php
// Vider le cache d'opcode si disponible
if (function_exists('opcache_reset')) {
    opcache_reset();
    echo "<p>Cache PHP OPcode vidé avec succès.</p>";
} else {
    echo "<p>Le cache OPcode n'est pas activé sur ce serveur.</p>";
}

// Vider le cache APC si disponible
if (function_exists('apc_clear_cache')) {
    apc_clear_cache();
    apc_clear_cache('user');
    apc_clear_cache('opcode');
    echo "<p>Cache APC vidé avec succès.</p>";
}

echo "<h1>Instructions de redémarrage</h1>";
echo "<p>Pour résoudre complètement le problème, suivez ces étapes:</p>";
echo "<ol>";
echo "<li>Redémarrez votre serveur XAMPP (Apache):</li>";
echo "<ul>";
echo "<li>Ouvrez le panneau de contrôle XAMPP</li>";
echo "<li>Cliquez sur 'Stop' pour Apache</li>";
echo "<li>Attendez quelques secondes</li>";
echo "<li>Cliquez sur 'Start' pour redémarrer Apache</li>";
echo "</ul>";
echo "<li>Si l'erreur persiste, essayez aussi de redémarrer le service MySQL</li>";
echo "<li>Enfin, essayez de modifier légèrement le nom de la seconde méthode clearCache():</li>";
echo "</ol>";

echo "<h2>Modification suggérée pour app/Controllers/SettingsController.php</h2>";
echo "<pre style='background-color: #f0f0f0; padding: 10px; border-radius: 5px;'>";
echo htmlspecialchars('/**
 * Clear the system cache via AJAX
 * Used by AJAX request to clear the application cache
 */
public function ajaxClearCache(): void {
    // Renommer la méthode de clearCache à ajaxClearCache
    // Le reste du code reste identique
}');
echo "</pre>";

echo "<p>Après avoir effectué ces modifications, accédez à <a href='settings'>la page des paramètres</a>.</p>"; 