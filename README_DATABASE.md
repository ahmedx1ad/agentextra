# Guide de consolidation de la base de données AgentExtra

Ce guide vous aidera à consolider toutes vos bases de données en une seule base de données unifiée pour votre projet AgentExtra. Ce processus simplifiera la maintenance et améliorera les performances de votre application.

## Prérequis

- Serveur MySQL/MariaDB fonctionnel
- PHP 7.4 ou supérieur
- Accès en lecture/écriture au système de fichiers
- Privilèges d'administration sur la base de données

## Instructions étape par étape

### 1. Sauvegarde des données existantes

Avant de commencer, assurez-vous de sauvegarder vos données existantes :

```bash
# Dans le terminal (ajustez les chemins si nécessaire)
cd c:/xampp/mysql/bin
mysqldump -u root -p --databases agentextra > c:/backup_agentextra.sql
```

### 2. Configuration de la base de données unifiée

1. Ouvrez le fichier `app/Config/DB.php` et vérifiez que les paramètres de connexion sont corrects :
   - `$host` : L'adresse du serveur MySQL (généralement `localhost`)
   - `$dbname` : Le nom de la base de données (`agentextra`)
   - `$username` : Votre nom d'utilisateur MySQL (généralement `root`)
   - `$password` : Votre mot de passe MySQL (souvent vide avec XAMPP)

## Structure des fichiers SQL

La structure de la base de données a été consolidée dans un seul fichier :

- `database_setup.sql` : Fichier unique contenant toutes les définitions de tables, index et données initiales nécessaires à l'application
- `run_sql.bat` : Script batch amélioré pour exécuter facilement le script SQL sous Windows
- `check_database.php` : Outil de vérification pour s'assurer que la base de données est correctement configurée
- `import_data.php` : Script pour importer des données existantes depuis des sources externes

### Améliorations apportées

1. **Centralisation des scripts SQL** : Tous les scripts SQL individuels ont été consolidés en un seul fichier `database_setup.sql`, ce qui facilite la maintenance et les mises à jour.
   
2. **Correction des erreurs** : Les erreurs dans les scripts SQL ont été corrigées, notamment :
   - Utilisation de la clause `FROM dual` pour les requêtes `INSERT... SELECT`
   - Remplacement de `CREATE INDEX IF NOT EXISTS` par `ALTER TABLE ADD INDEX` pour une meilleure compatibilité
   - Ajout de guillemets autour des chemins dans le script batch

3. **Gestion améliorée des erreurs** : Le script batch `run_sql.bat` inclut maintenant une meilleure gestion des erreurs et fournit des informations plus claires sur les problèmes rencontrés.

### 3. Création de la structure de la base de données

1. Exécutez simplement le script batch fourni :

```
C:\xampp\htdocs\agentextra\run_sql.bat
```

Ce script :
- Vérifie la présence de MySQL
- Exécute le script SQL centralisé
- Vérifie que la structure a été correctement créée

### 4. Vérification de la configuration

1. Exécutez le script de vérification pour vous assurer que tout est correctement configuré :

```bash
# Dans le terminal
cd c:/xampp/htdocs/agentextra
php check_database.php
```

Ce script vérifiera :
- La connexion à la base de données
- La présence de toutes les tables requises
- Les permissions de l'utilisateur MySQL
- Les performances de base de la base de données

### 5. Migration des données

Si vous avez des données dans d'autres bases de données ou fichiers CSV, utilisez le script d'importation :

```bash
# Dans le terminal
cd c:/xampp/htdocs/agentextra
php import_data.php
```

Ce script importera :
- Les données de vos anciens fichiers SQL
- Les modèles CSV (agents et responsables)

### 6. Nettoyage (optionnel)

Une fois que vous avez vérifié que toutes vos données sont correctement migrées, vous pouvez supprimer les fichiers SQL individuels :

- `add_active_column.sql`
- `add_cin_column.sql`
- `add_test_performances.sql`
- `create_performances_table.sql`
- `create_user_favorites_table.sql`

Ces fichiers ne sont plus nécessaires puisque toutes les structures sont maintenant définies dans `database_setup.sql`.

## Résolution des problèmes courants

### Erreur de connexion à la base de données

- Vérifiez que le service MySQL est démarré dans XAMPP Control Panel
- Assurez-vous que les informations de connexion dans `app/Config/DB.php` sont correctes
- Vérifiez que l'utilisateur MySQL a les droits nécessaires

### Tables manquantes

- Exécutez à nouveau le script `database_setup.sql`
- Vérifiez les erreurs dans les logs MySQL (`c:/xampp/mysql/data/mysql_error.log`)

### Erreurs d'importation de données

- Vérifiez le format de vos fichiers CSV
- Assurez-vous que les colonnes correspondent aux champs de la base de données
- Vérifiez les contraintes de clé étrangère (les services et responsables doivent exister avant d'importer les agents)

## Maintenance future

Toutes les modifications de structure de la base de données devraient désormais être ajoutées directement au fichier `database_setup.sql`. Ce fichier centralisé constitue la source unique de vérité pour votre schéma de base de données.

Pour appliquer des modifications :
1. Mettez à jour le fichier `database_setup.sql`
2. Exécutez `run_sql.bat` pour appliquer les changements
3. Utilisez `check_database.php` pour vérifier que les modifications ont été correctement appliquées

Cette approche centralisée simplifie considérablement la maintenance et les mises à jour de votre base de données.

## Support

Si vous rencontrez des problèmes lors de la consolidation de votre base de données, veuillez consulter la documentation de XAMPP ou MySQL pour plus d'informations. 