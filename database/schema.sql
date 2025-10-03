-- Schema for courrier management application (collectivite locale Senegal)

-- Drop dependent tables first to honor foreign key constraints
DROP TABLE IF EXISTS statut_history;
DROP TABLE IF EXISTS affectations;
DROP TABLE IF EXISTS pieces_jointes;
DROP TABLE IF EXISTS courriers;
DROP TABLE IF EXISTS logs;
DROP TABLE IF EXISTS utilisateurs;
DROP TABLE IF EXISTS services;

-- Table: services
CREATE TABLE services (
  id        BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  code      VARCHAR(30)     NOT NULL,
  libelle   VARCHAR(150)    NOT NULL,
  actif     TINYINT(1)      NOT NULL DEFAULT 1,

  PRIMARY KEY (id),
  UNIQUE KEY ux_services_code (code),
  UNIQUE KEY ux_services_libelle (libelle),
  KEY ix_services_actif (actif)
)
ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_unicode_ci
COMMENT='Services ou directions de la collectivite';

-- Seeds pour services
INSERT INTO services (code, libelle, actif) VALUES
  ('SG',      'Secretariat General', 1),
  ('FIN',     'Finances',            1),
  ('ETATCIV', 'Etat civil',          1),
  ('TECH',    'Services techniques', 1);

-- Table: utilisateurs
CREATE TABLE utilisateurs (
  id           BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  prenom_nom   VARCHAR(150)    NOT NULL,
  login        VARCHAR(80)     NOT NULL,
  mdp_hash     VARCHAR(255)    NOT NULL,
  role         ENUM('ADMIN','SECRETAIRE','CHEF_SERVICE','AGENT','LECTEUR') NOT NULL DEFAULT 'AGENT',
  actif        TINYINT(1)      NOT NULL DEFAULT 1,
  service_id   BIGINT UNSIGNED NULL,
  created_at   DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,

  PRIMARY KEY (id),
  UNIQUE KEY ux_utilisateurs_login (login),
  KEY ix_utilisateurs_service (service_id),
  KEY ix_utilisateurs_actif_service (actif, service_id),
  CONSTRAINT fk_utilisateurs_service
    FOREIGN KEY (service_id) REFERENCES services(id)
    ON UPDATE CASCADE
    ON DELETE SET NULL
)
ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_unicode_ci
COMMENT='Utilisateurs de la gestion de courrier (collectivite locale Senegal)';

-- Seeds pour utilisateurs (remplacer mdp_hash par un password_hash valide)
INSERT INTO utilisateurs (prenom_nom, login, mdp_hash, role, actif, service_id) VALUES
  ('Admin Systeme', 'admin', '$2y$10$REMPLACE_CE_HASH', 'ADMIN', 1, NULL),
  ('Secretariat General', 'secgen', '$2y$10$REMPLACE_CE_HASH', 'SECRETAIRE', 1, 1);

-- Table: courriers
CREATE TABLE courriers (
  id                BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  type              ENUM('ENTRANT','SORTANT') NOT NULL,
  ref               VARCHAR(50)     NOT NULL,
  objet             VARCHAR(255)    NOT NULL,
  expediteur        VARCHAR(180)    NULL,
  destinataire      VARCHAR(180)    NULL,
  date_reception    DATE            NULL,
  date_envoi        DATE            NULL,
  priorite          ENUM('BASSE','NORMALE','HAUTE','URGENTE') NOT NULL DEFAULT 'NORMALE',
  confidentialite   ENUM('PUBLIQUE','INTERNE','CONFIDENTIEL') NOT NULL DEFAULT 'INTERNE',
  service_source_id BIGINT UNSIGNED NULL,
  service_cible_id  BIGINT UNSIGNED NULL,
  statut            ENUM('ENREGISTRE','AFFECTE','EN_COURS','REPONDU','CLOS','ARCHIVE') NOT NULL DEFAULT 'ENREGISTRE',
  echeance          DATE            NULL,
  created_by        BIGINT UNSIGNED NOT NULL,
  created_at        DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,

  PRIMARY KEY (id),
  UNIQUE KEY ux_courriers_ref (ref),
  KEY ix_courriers_statut (statut),
  KEY ix_courriers_service_statut (service_cible_id, statut, priorite),
  KEY ix_courriers_created_at (created_at),
  KEY ix_courriers_echeance (echeance),
  KEY ix_courriers_type (type),
  KEY ix_courriers_source (service_source_id),
  KEY ix_courriers_created_by (created_by),

  CONSTRAINT fk_courriers_service_source
    FOREIGN KEY (service_source_id) REFERENCES services(id)
    ON UPDATE CASCADE ON DELETE SET NULL,
  CONSTRAINT fk_courriers_service_cible
    FOREIGN KEY (service_cible_id)  REFERENCES services(id)
    ON UPDATE CASCADE ON DELETE SET NULL,
  CONSTRAINT fk_courriers_created_by
    FOREIGN KEY (created_by)        REFERENCES utilisateurs(id)
    ON UPDATE CASCADE ON DELETE RESTRICT,

  CONSTRAINT chk_courriers_date_entree
    CHECK (type <> 'ENTRANT' OR date_reception IS NOT NULL),
  CONSTRAINT chk_courriers_date_sortie
    CHECK (type <> 'SORTANT'  OR date_envoi      IS NOT NULL)
)
ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_unicode_ci
COMMENT='Courriers entrants ou sortants de la collectivite';

-- Table: pieces_jointes
CREATE TABLE pieces_jointes (
  id           BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  courrier_id  BIGINT UNSIGNED NOT NULL,
  nom_fichier  VARCHAR(255)    NOT NULL,
  chemin       VARCHAR(255)    NOT NULL,
  taille       BIGINT UNSIGNED NOT NULL,
  mime         VARCHAR(120)    NOT NULL,
  uploaded_at  DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,

  PRIMARY KEY (id),
  KEY ix_pj_courrier (courrier_id),
  KEY ix_pj_mime (mime),
  KEY ix_pj_uploaded_at (uploaded_at),

  CONSTRAINT fk_pj_courrier
    FOREIGN KEY (courrier_id) REFERENCES courriers(id)
    ON UPDATE CASCADE
    ON DELETE CASCADE,

  CONSTRAINT chk_pj_taille CHECK (taille > 0)
)
ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_unicode_ci
COMMENT='Pieces jointes liees aux courriers';

-- Table: affectations
CREATE TABLE affectations (
  id                   BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  courrier_id          BIGINT UNSIGNED NOT NULL,
  service_id           BIGINT UNSIGNED NOT NULL,
  responsable_user_id  BIGINT UNSIGNED NULL,
  note                 TEXT         NULL,
  created_at           DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,

  PRIMARY KEY (id),
  KEY ix_aff_courrier   (courrier_id, created_at),
  KEY ix_aff_service    (service_id, created_at),
  KEY ix_aff_responsable(responsable_user_id, created_at),

  CONSTRAINT fk_aff_courrier
    FOREIGN KEY (courrier_id) REFERENCES courriers(id)
    ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT fk_aff_service
    FOREIGN KEY (service_id) REFERENCES services(id)
    ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT fk_aff_responsable
    FOREIGN KEY (responsable_user_id) REFERENCES utilisateurs(id)
    ON UPDATE CASCADE ON DELETE SET NULL
)
ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_unicode_ci
COMMENT='Historique des affectations des courriers';

-- Table: statut_history
CREATE TABLE statut_history (
  id             BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  courrier_id    BIGINT UNSIGNED NOT NULL,
  ancien_statut  ENUM('ENREGISTRE','AFFECTE','EN_COURS','REPONDU','CLOS','ARCHIVE') NULL,
  nouveau_statut ENUM('ENREGISTRE','AFFECTE','EN_COURS','REPONDU','CLOS','ARCHIVE') NOT NULL,
  user_id        BIGINT UNSIGNED NOT NULL,
  note           TEXT NULL,
  created_at     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

  PRIMARY KEY (id),
  KEY ix_sh_courrier_time   (courrier_id, created_at),
  KEY ix_sh_nouveau_statut  (nouveau_statut),
  KEY ix_sh_user            (user_id, created_at),

  CONSTRAINT fk_sh_courrier
    FOREIGN KEY (courrier_id) REFERENCES courriers(id)
    ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT fk_sh_user
    FOREIGN KEY (user_id) REFERENCES utilisateurs(id)
    ON UPDATE CASCADE ON DELETE RESTRICT,

  CONSTRAINT chk_sh_statuts_diff
    CHECK (ancien_statut IS NULL OR ancien_statut <> nouveau_statut)
)
ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_unicode_ci
COMMENT='Historique des transitions de statut des courriers';

-- Table: logs
CREATE TABLE logs (
  id           BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id      BIGINT UNSIGNED NULL,
  action       VARCHAR(80)     NOT NULL,
  details_json JSON            NULL,
  created_at   DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,

  PRIMARY KEY (id),
  KEY ix_logs_user_time   (user_id, created_at),
  KEY ix_logs_action_time (action, created_at),
  KEY ix_logs_created_at  (created_at),

  CONSTRAINT fk_logs_user
    FOREIGN KEY (user_id) REFERENCES utilisateurs(id)
    ON UPDATE CASCADE
    ON DELETE SET NULL
)
ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_unicode_ci
COMMENT='Journal applicatif des actions et evenements';

-- Optionnel: index de recherche plein texte
-- CREATE FULLTEXT INDEX ft_courriers ON courriers (objet, expediteur, destinataire, ref);
