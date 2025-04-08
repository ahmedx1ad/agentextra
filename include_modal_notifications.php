<?php
/**
 * Ce fichier explique comment intégrer le système de notifications modales dans votre application
 * 
 * Instructions pour l'installation et l'utilisation :
 * 1. Copiez les fichiers suivants dans leurs emplacements respectifs:
 *    - app/views/layouts/modal_notifications.php
 *    - app/Helpers/NotificationHelper.php (mise à jour)
 * 
 * 2. Ajoutez le code suivant juste après l'ouverture de la balise <body> dans votre fichier de layout principal:
 */
?>

<!-- AJOUTER CE CODE DANS layouts/header.php juste après la balise <body> -->

<?php
// Vérifier s'il faut afficher le modal de notification
if (isset($_GET['show_modal']) && file_exists(VIEWS_PATH . '/layouts/modal_notifications.php')) {
    require_once VIEWS_PATH . '/layouts/modal_notifications.php';
}
?>

<!-- FIN DU CODE À AJOUTER -->

<?php
/**
 * 3. Dans vos contrôleurs, utilisez la méthode suivante pour afficher une notification modale:
 * 
 * Exemple pour une notification de succès:
 * 
 * \App\Helpers\NotificationHelper::successAndRedirect(
 *     "L'opération a réussi avec succès.",  // Message à afficher
 *     "dashboard",                         // URL de redirection après fermeture
 *     true,                               // Appeler exit() après redirection
 *     true                                // true = utiliser le modal, false = redirection immédiate
 * );
 * 
 * Exemple pour une notification d'erreur:
 * 
 * \App\Helpers\NotificationHelper::errorAndRedirect(
 *     "Une erreur s'est produite lors de l'opération.",
 *     "utilisateurs/create",
 *     true,
 *     true
 * );
 * 
 * 4. Pour afficher plusieurs messages d'erreur (comme des erreurs de validation de formulaire):
 * 
 * $erreurs = [
 *     "Le champ 'Nom' est obligatoire.",
 *     "L'adresse email n'est pas valide."
 * ];
 * 
 * \App\Helpers\NotificationHelper::error(
 *     "Le formulaire contient des erreurs.",  // Message principal
 *     $erreurs                                // Détails (liste des erreurs)
 * );
 * 
 * \App\Helpers\NotificationHelper::setRedirectUrl("formulaire"); // URL de retour
 * 
 * header("Location: " . $_SERVER['REQUEST_URI'] . "?show_modal=1");
 * exit;
 */
?> 