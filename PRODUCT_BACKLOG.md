# 📋 Product Backlog - Telecompensation

**Version:** 1.0  
**Date:** Mai 2026  
**Produit:** Plateforme de Télécompensation SIBTEL

---

## 📑 Table des matières
1. [Vision du Produit](#vision)
2. [Épopées (Epics)](#epics)
3. [Backlog Produit par Priorité](#backlog)
4. [Roadmap](#roadmap)

---

## <a name="vision"></a>👁️ Vision du Produit

Créer une plateforme de traitement de fichiers de télécompensation SIBTEL qui couvre l’upload, le parsing, la validation métier, la génération XML ISO20022, la gestion des rejets, les exports et le suivi des actions utilisateur.

**Valeurs Clés:**
- 🔧 **Conformité SIBTEL:** format et règles métier respectés
- 📈 **Visibilité:** suivi des fichiers, rejets et statistiques
- 🔐 **Contrôle d’accès:** rôles admin / superviseur / opérateur
- 🧠 **Intelligence:** support ML pour estimer le risque de rejet
- 📄 **Exports:** rapports PDF/Excel prêts à partager

---

## <a name="epics"></a>🏰 Épopées (Epics)

### EPIC 1: Upload & parsing de fichiers
Permettre aux utilisateurs de charger des fichiers `.ENV`, `.PAK` ou `.TXT`, puis extraire les enregistrements SIBTEL en base.

### EPIC 2: Validation SIBTEL & règles métiers
Vérifier les RIB, montants, dates et contraintes propres à chaque type de valeur.

### EPIC 3: Génération XML ISO20022
Produire des XML métier conformes (`pacs.008`, `pacs.003`) et gérer les rejets via `pacs.004`.

### EPIC 4: Gestion des rejets
Afficher, traiter puis transformer les rejets en messages PACS.004 et enregistrements de suivi.

### EPIC 5: Authentification, rôles et audit
Gérer l’accès par rôle, tracer les actions utilisateur et enregistrer l’historique d’audit.

### EPIC 6: Monitoring et reporting
Fournir un dashboard, des statistiques et des exports PDF/Excel basés sur les données traitées.

### EPIC 7: Outils et administration
Donner des fonctionnalités administratives et un utilitaire métier de vérification de RIB.

---

## <a name="backlog"></a>📊 Backlog Produit par Priorité

### 🔴 P0 - Critique

#### US-001: Authentification Laravel
- **Objectif:** connexion sécurisée des utilisateurs.
- **Implémentation réelle:** Livewire `Login`, `Auth::attempt()`, validation email/password, logout.
- **Routes:** `/login`, `/logout`.

#### US-002: Contrôle par rôle
- **Objectif:** limiter l’accès selon `admin`, `superviseur`, `operateur`.
- **Implémentation réelle:** middleware `role:` sur les routes.
- **Accès:** upload/rejets (admin+superviseur+operateur), stats/rapport (admin+superviseur), users/audit (admin).

#### US-003: Upload de fichiers SIBTEL
- **Objectif:** recevoir et stocker des fichiers `.ENV`, `.PAK`, `.TXT`.
- **Implémentation réelle:** `app/Livewire/Fichiers/Upload.php`, stockage `storage/app/telecompensation`
- **Types gérés:** `10,20,30,31,32,33,40,41,42,43,82,83,84`.

#### US-004: Parsing SIBTEL
- **Objectif:** analyser les lignes du fichier et extraire les enregistrements globaux et détails.
- **Implémentation réelle:** `EnvParserService` plus `FichierTraitementService::traiter()`.
- **Tables:** `tc_enr_globaux`, `tc_enr_details`.

#### US-005: Validation RIB et format
- **Objectif:** vérifier la validité des RIB tunisiens.
- **Implémentation réelle:** `ValidatorService`, `RibValidatorService` (20 chiffres, modulo 97, banque valide).
- **Résultat:** erreurs sauvegardées dans `tc_rejets`.

#### US-006: Estimation ML de rejet
- **Objectif:** calculer un score de risque pour chaque transaction.
- **Implémentation réelle:** appel HTTP Flask vers `config('services.ml.url')`, payload de features.
- **Comportement:** fallback en cas d’échec, score minimum si rejet déjà détecté.

#### US-007: Liste et statistiques de fichiers
- **Objectif:** afficher le catalogue de fichiers avec filtres par nom, type, statut.
- **Implémentation réelle:** `Livewire\Fichiers\Index`, comptages `total`, `traites`, `erreurs`, pagination.

#### US-008: Détails fichier et actions de validation
- **Objectif:** valider ou rejeter un fichier, ajouter un commentaire.
- **Implémentation réelle:** `Livewire\Fichiers\Show`, `ValidationService`, modals de validation/rejet, champ commentaires.

#### US-009: Visualisation et export XML
- **Objectif:** visualiser le XML généré et le copier/télécharger.
- **Implémentation réelle:** `XmlTransformerService`, `Livewire\Fichiers\XmlViewer`, formatage `pacs.008` et `pacs.003`, wrapper AppHdr.

#### US-010: Gestion des rejets
- **Objectif:** lister les rejets, filtrer par étape, marquer comme traités.
- **Implémentation réelle:** `Livewire\Rejets\Index`, actions `marquerTraite`, `marquerTousTraites`.

#### US-011: Génération PACS.004
- **Objectif:** générer un message PACS.004 pour les rejets d’un fichier.
- **Implémentation réelle:** `Pacs004TransformerService`, `Livewire\Rejets\Pacs004Generator`, téléchargement XML.

#### US-012: Dashboard et indicateurs
- **Objectif:** visualiser KPI et tendances de la compensation.
- **Implémentation réelle:** `Livewire\Stats\Index`, graphiques évolution, statut, types, taux de rejet.

#### US-013: Gestion des utilisateurs
- **Objectif:** créer, modifier et supprimer des comptes.
- **Implémentation réelle:** `Livewire\Users\Index`, validation des champs, hash password, audit, protection auto.

#### US-014: Reporting PDF/Excel
- **Objectif:** exporter des rapports sur la période demandée.
- **Implémentation réelle:** `ExportService` + scripts Python `generate_pdf.py` / `generate_excel.py`, `RapportController`.

#### US-015: Audit des actions utilisateur
- **Objectif:** tracer les événements importants.
- **Implémentation réelle:** `AuditService`, table `audit_trail`, logs `LOGIN`, `UPLOAD`, `USER_CREATE`, `PACS004_GENERE`, etc.

### 🟠 P1 - Important

#### US-016: Outil de vérification de RIB
- **Objectif:** vérifier un RIB tunisien depuis l’interface.
- **Implémentation réelle:** `Livewire\Outils\VerificateurRib`, `RibValidatorService`.

#### US-017: Profil utilisateur
- **Objectif:** afficher le profil connecté.
- **Implémentation réelle:** `Livewire\Profile\Index`.

#### US-018: Export direct de PACS.004
- **Objectif:** télécharger un PACS.004 déjà généré.
- **Implémentation réelle:** route `/pacs004/{id}/telecharger`.

---

## <a name="roadmap"></a>🛣️ Roadmap

1. Sprint 1: Auth, rôles, upload de fichiers, parsing SIBTEL, validation de base.  
2. Sprint 2: Détail fichier, validation/rejet, XML ISO20022, gestion utilisateurs et audit.  
3. Sprint 3: Rejets PACS.004, dashboard statistiques, export PDF/Excel.  
4. Sprint 4: Amélioration ML, robustesse, tests, documentation.
