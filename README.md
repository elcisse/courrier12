# Gestion du courrier

Application MVC legere pour la gestion des courriers entrants et sortants dans une collectivite locale.

## Prerequis

- PHP 8.1+
- Extension PDO MySQL activee
- Serveur MySQL (schema teste sur MySQL 8)
- Serveur web pointe sur le dossier `public`

## Installation

1. Copier le projet dans votre environnement (ex: `c:\MAMP\htdocs\courrier12`).
2. Mettre a jour `config/config.php` si besoin (hote, port, identifiants).
3. Importer la base:
   ```bash
   mysql -u root -p courrier24 < database/schema.sql
   ```
4. Verifier que le dossier `public/` est configure comme racine web. Sous Apache, utiliser la regle `public/.htaccess` fournie.
5. Ouvrir `http://localhost/courrier12/public/index.php` dans le navigateur.

## Fonctionnalites

- Tableau de bord avec indicateurs clefs et derniers courriers
- Gestion CRUD des services (code, libelle, statut actif)
- Ajout et gestion de pieces jointes (PDF, images, bureautique) avec telechargement et suppression controlee
- Authentification basique (login/logout) et attribution automatique du createur
- Filtres par type, statut, service cible et recherche texte
- Protection CSRF basique et messages flash

## Notes

- Le dossier public/uploads doit etre accessible en ecriture par le serveur web pour permettre l'enregistrement des fichiers.
- Les utilisateurs doivent exister dans la table `utilisateurs` (champ `mdp_hash` genere via `password_hash`). Les courriers utilisent automatiquement l'utilisateur connecte pour le champ `created_by`.
- La table `pieces_jointes` est utilisee pour stocker les fichiers rattaches. Les tables `affectations`, `statut_history` et `logs` restent disponibles pour de futures evolutions.
- Le projet utilise un routeur simple via les parametres `controller` et `action` (`index.php?controller=courrier&action=index`).

## Scripts utiles

- Reinitialiser la base (efface les donnees) :
  ```bash
  mysql -u root -p courrier24 < database/schema.sql
  ```
- Nettoyer le cache de session si besoin : supprimer manuellement le contenu du dossier de sessions PHP.





