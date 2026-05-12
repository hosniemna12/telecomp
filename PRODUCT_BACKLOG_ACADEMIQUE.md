# 📋 Product Backlog Académique - Plateforme de Télécompensation SIBTEL

**Version:** 2.0 (Format BDD)  
**Date:** Mai 2026  
**Produit:** Plateforme de Traitement de Fichiers de Télécompensation SIBTEL  
**Durée:** 3 Sprints (2 semaines/sprint)  
**Format:** User Stories BDD "En tant que... je souhaite... afin que..."

---

## 📑 Table des matières
1. [Vision du Produit](#vision)
2. [Rôles et Permissions](#roles)
3. [Architecture Métier](#architecture)
4. [Sprint 1 - Fondations](#sprint1)
5. [Sprint 2 - Traitement](#sprint2)
6. [Sprint 3 - Reporting et Gestion](#sprint3)
7. [Matrice de Traçabilité](#matrice)

---

## <a name="vision"></a>👁️ Vision du Produit

Créer une plateforme de traitement automatisé de fichiers de télécompensation SIBTEL conforme aux normes ISO 20022, permettant aux institutions bancaires de :
- Recevoir et valider des fichiers de compensation
- Tracer les rejets de transactions
- Générer des messages de rejet normalisés
- Auditer et reporter sur l'activité de compensation

**Valeurs Clés:**
- 🔧 **Conformité SIBTEL & ISO 20022:** Respect strict des formats métier
- 📈 **Visibilité:** Suivi complet des fichiers, rejets et statistiques
- 🔐 **Sécurité & Audit:** Contrôle d'accès par rôles, traçabilité complète
- 🧠 **Intelligence:** Estimation du risque de rejet via ML
- 📄 **Exports:** Rapports PDF/Excel pour décideurs

---

## <a name="roles"></a>👥 Rôles et Permissions

| Rôle | Permissions | Responsabilités | Composants Accessibles |
|------|-------------|-----------------|------------------------|
| **Administrateur** | ✅ Tous les accès | Gestion globale, utilisateurs, audit complet | Users, Audit, Upload, Validation, Stats, Rapports |
| **Superviseur** | Upload, Validation, Rejets, Rapports | Validation fichiers, génération PACS.004, exports | Upload, Validation, Rejets, Stats, Rapports |
| **Opérateur** | Upload, Consultation | Upload fichiers, consultation statut | Upload, Consultation Fichiers |

---

## <a name="architecture"></a>🏗️ Architecture Métier

### Modèles de Données Clés
```
User (id, name, email, role, password)
    ↓
TcFichier (id, nom, type_valeur, statut, uploaded_by, valide_par)
    ├─ TcEnrDetail (transactions, montants, RIB)
    ├─ TcEnrGlobal (totaux fichier)
    ├─ TcRejet (erreurs parsing/validation)
    ├─ TcPacs004 (messages rejet ISO 20022)
    ├─ TcCommentaire (communications équipe)
    ├─ TcNotification (alertes utilisateurs)
    ├─ TcXmlProduit (XML ISO généré)
    └─ TcLogsTraitement (logs exécution)

AuditTrail (actions utilisateurs)
LoginHistory (connexions)
```

### Services Métier
- **FichierTraitementService:** Orchestrateur principal
- **EnvParserService:** Parsing SIBTEL (13 formats)
- **ValidatorService:** Validation règles métier
- **XmlTransformerService:** Génération ISO 20022
- **Pacs004TransformerService:** Génération rejets
- **ValidationService:** Workflow validation/rejet
- **AuditService:** Traçabilité actions
- **ExportService:** Rapports PDF/Excel

---

## <a name="sprint1"></a>🎯 SPRINT 1 - Fondations & Authentification
**Durée:** 2 semaines | **Objectif:** Base système sécurisée et opérationnelle

### US-001: Authentification Sécurisée des Utilisateurs

**En tant qu'** utilisateur  
**Je souhaite** me connecter à la plateforme avec email et mot de passe  
**Afin que** seuls les utilisateurs autorisés accèdent au système

| Aspect | Détail |
|--------|--------|
| **Rôles concernés** | Admin, Superviseur, Opérateur |
| **Composants Livewire** | `Livewire\Auth\Login` |
| **Services** | Laravel Auth, LoginHistory |
| **Model** | User (email, password, role) |
| **Routes** | `POST /login` (email, password) → session | `POST /logout` → destroy session |
| **Critères d'acceptation** | ✅ Email valide et unique | ✅ Mot de passe hashé (bcrypt) | ✅ Session sécurisée | ✅ Logout fonctionne | ✅ Redirection après connexion |
| **Dépendances** | Aucune (user story fondatrice) |
| **Effort** | 3 points |

---

### US-002: Contrôle d'Accès par Rôles

**En tant qu'** administrateur  
**Je souhaite** limiter l'accès aux fonctionnalités selon le rôle de l'utilisateur  
**Afin que** chaque utilisateur ne voit que ses pages autorisées

| Aspect | Détail |
|--------|--------|
| **Rôles concernés** | Admin (configuration), Tous (application) |
| **Composants** | Middleware `CheckRole` |
| **Services** | Laravel Middleware, Policy |
| **Model** | User (role: admin\|superviseur\|operateur) |
| **Permissions** | Admin: tout | Superviseur: upload+validation+stats | Opérateur: upload+lecture |
| **Routes protégées** | `/users`, `/audit`, `/rapport`, `/validation` |
| **Critères d'acceptation** | ✅ Admin accès complet | ✅ Superviseur accès partiel | ✅ Opérateur accès restreint | ✅ Redirection 403 si non autorisé | ✅ Menu adapté au rôle |
| **Dépendances** | US-001 |
| **Effort** | 3 points |

---

### US-003: Gestion Complète des Utilisateurs par Admin

**En tant qu'** administrateur  
**Je souhaite** créer, modifier et supprimer des comptes utilisateurs avec nom, email, mot de passe et rôle  
**Afin que** l'accès à la plateforme soit géré de manière sécurisée

| Aspect | Détail |
|--------|--------|
| **Rôles concernés** | Admin seulement |
| **Composants Livewire** | `Livewire\Users\Index` (CRUD) |
| **Services** | AuditService (log), User service |
| **Model** | User (name, email, password, role, created_at) |
| **Opérations** | Create: nom+email+pwd+rôle → hash pwd | Update: modifier champs | Delete: soft/hard |
| **Validations** | Email unique et format valide | Rôle ∈ {admin, superviseur, operateur} | Mot de passe min 8 car |
| **Critères d'acceptation** | ✅ Créer utilisateur avec rôle valide | ✅ Mot de passe hashé | ✅ Valider email unique | ✅ Modifier utilisateur | ✅ Supprimer utilisateur | ✅ Audit log création/suppression |
| **Dépendances** | US-001, US-002 |
| **Effort** | 5 points |

---

### US-004: Traçabilité Complète des Actions Utilisateur

**En tant qu'** administrateur  
**Je souhaite** voir l'historique de toutes les actions utilisateurs avec détails  
**Afin que** je puisse auditer et tracer les responsabilités

| Aspect | Détail |
|--------|--------|
| **Rôles concernés** | Admin seulement |
| **Composants Livewire** | `Livewire\Audit\Index` |
| **Services** | AuditService |
| **Model** | AuditTrail (user_id, action, module, description, ip_address, statut, donnees_avant, donnees_apres, created_at) |
| **Actions tracées** | LOGIN, USER_CREATE, USER_DELETE, UPLOAD, VALIDATION, REJET_VALIDATION, PACS004_GENERE, RAPPORT_EXPORT |
| **Filtres** | Par action / user / module / date / statut |
| **Affichage** | Tableau paginé avec tri, avant/après données |
| **Critères d'acceptation** | ✅ Tous logs visibles en base | ✅ Filtres fonctionnels | ✅ Voir données avant/après changement | ✅ Pagination 50 par page | ✅ Export possible |
| **Dépendances** | US-001 |
| **Effort** | 5 points |

---

### US-005: Profil Utilisateur Personnel

**En tant qu'** utilisateur connecté (tous rôles)  
**Je souhaite** voir mon profil (nom, email, rôle, date création)  
**Afin que** je confirme mon identité et mes permissions

| Aspect | Détail |
|--------|--------|
| **Rôles concernés** | Admin, Superviseur, Opérateur |
| **Composants Livewire** | `Livewire\Profile\Index` |
| **Services** | Auth, User service |
| **Model** | User |
| **Affichage** | Nom, Email, Rôle, Date inscription, Dernière connexion |
| **Optionnel** | Modifier mot de passe personnel |
| **Critères d'acceptation** | ✅ Affiche informations exactes | ✅ Rôle lisible | ✅ Modification pwd fonctionnelle | ✅ Logout accessible |
| **Dépendances** | US-001 |
| **Effort** | 2 points |

---

## <a name="sprint2"></a>🎯 SPRINT 2 - Traitement de Fichiers & Validation
**Durée:** 2 semaines | **Objectif:** Pipeline complet d'upload, parsing, validation, génération XML

### US-006: Upload de Fichiers SIBTEL

**En tant qu'** opérateur ou superviseur  
**Je souhaite** uploader un fichier `.ENV`, `.PAK` ou `.TXT` contenant des données de compensation  
**Afin que** le système traite mes données de compensation

| Aspect | Détail |
|--------|--------|
| **Rôles concernés** | Superviseur, Opérateur |
| **Composants Livewire** | `Livewire\Fichiers\Upload` |
| **Services** | FichierTraitementService, AuditService |
| **Model** | TcFichier (nom_fichier, chemin_complet, type_valeur, statut, uploaded_by, date_reception) |
| **Types acceptés** | 10(Virement), 20(Prélèvement), 30-33(Chèque), 40-43(LDC), 82-84(Autres) |
| **Validations** | Extension .env/.pak/.txt | Fichier max 10MB | Détection auto type valeur |
| **Stockage** | storage/app/telecompensation/{date}/{filename} |
| **Statut initial** | EN_COURS |
| **Critères d'acceptation** | ✅ Upload fichier max 10MB | ✅ Validation extension | ✅ Détection auto type | ✅ Sauvegarde chemin complet | ✅ Audit log upload | ✅ Notification upload réussi |
| **Dépendances** | US-001, US-002 |
| **Effort** | 5 points |

---

### US-007: Parsing Automatique Fichier SIBTEL

**En tant qu'** système  
**Je souhaite** extraire automatiquement les enregistrements du fichier uploadé  
**Afin que** les données soient structurées en base de données

| Aspect | Détail |
|--------|--------|
| **Services** | EnvParserService, FichierTraitementService |
| **Models** | TcEnrGlobal, TcEnrDetail, TcRejet |
| **Formats gérés** | Tous 13 types SIBTEL (280/200/160/350 caractères) |
| **Extraction** | RIB (20 chiffres), montants, dates (DDMMYYYY), libellés, codes |
| **Erreurs parsing** | Créer TcRejet avec code: PARSE_ERR |
| **Logs** | TcLogsTraitement pour chaque étape (INFO/WARNING/ERROR) |
| **Statut fichier** | EN_ATTENTE_VALIDATION après parsing réussi |
| **Critères d'acceptation** | ✅ Parse enreg. global | ✅ Parse enreg. détails | ✅ Gère tous formats | ✅ Log erreurs parsing | ✅ Crée rejets parsing | ✅ Statut fichier updated |
| **Dépendances** | US-006 |
| **Effort** | 8 points |

---

### US-008: Validation RIB Tunisien (Modulo 97)

**En tant qu'** système  
**Je souhaite** valider que chaque RIB tunisien est correct selon normes BCT  
**Afin que** seules les transactions avec RIB valides soient acceptées

| Aspect | Détail |
|--------|--------|
| **Services** | ValidatorService, RibValidatorService |
| **Models** | TcEnrDetail, TcRejet |
| **Règles** | Format: 20 chiffres | Clé modulo 97 | Code banque valide (26, etc.) |
| **Calcul clé** | rib_partiel = 18 premiers chiffres | cle = 97 - (rib_partiel + "00" mod 97) |
| **Erreur** | Créer TcRejet avec code: VAL_RIB_ERR et motif détaillé |
| **Logs** | TcLogsTraitement WARNING si RIB invalide |
| **Critères d'acceptation** | ✅ Vérifier format 20 chiffres | ✅ Calculer clé modulo 97 | ✅ Valider code banque | ✅ Log erreur si invalide | ✅ Créer rejet avec motif |
| **Dépendances** | US-007 |
| **Effort** | 5 points |

---

### US-009: Validation Métier des Transactions SIBTEL

**En tant qu'** système  
**Je souhaite** vérifier montants, dates et contraintes métier par type valeur  
**Afin que** les règles SIBTEL soient respectées

| Aspect | Détail |
|--------|--------|
| **Services** | ValidatorService |
| **Models** | TcRejet |
| **Validations globales** | Montant > 0 | Dates format DDMMYYYY |
| **Type-spécifique** | Type 20: code_emetteur 6 chiffres, ref_contrat requis | Type 30-32: date_emission requis | Type 40-43: date_echeance requis |
| **Erreur** | Créer TcRejet avec code: VALID_ERR et motif précis |
| **Logs** | TcLogsTraitement ERROR par erreur trouvée |
| **Critères d'acceptation** | ✅ Valider montant > 0 | ✅ Valider format dates | ✅ Vérifier contraintes par type | ✅ Log détail erreur | ✅ Créer rejet avec motif complet |
| **Dépendances** | US-007, US-008 |
| **Effort** | 6 points |

---

### US-010: Estimation du Risque de Rejet via ML

**En tant qu'** système  
**Je souhaite** estimer un score de risque de rejet pour chaque transaction  
**Afin que** les superviseurs identifient rapidement les transactions à risque

| Aspect | Détail |
|--------|--------|
| **Services** | FichierTraitementService (appel HTTP) |
| **Endpoint ML** | POST http://127.0.0.1:5000/predict |
| **Features** | type_valeur, montant, code_banque_don, code_banque_dest, rib_donneur_valide (0\|1), rib_beneficiaire_valide (0\|1), echeance_depassee (0\|1), meme_banque (0\|1), situation_donneur, type_compte |
| **Payload** | JSON 10 features |
| **Résultat ML** | {score: 0-100, couleur: "rouge"\|"orange"\|"vert", rejete: bool, proba: float, explications: [{feature, libelle, detail, gravite}]} |
| **Fallback** | Si ML inaccessible → score=null, couleur=null |
| **Boost SIBTEL** | Si motif_rejet détecté → score min 80, couleur rouge |
| **Sauvegarde** | TcEnrDetail (score_ml, resultat_ml JSON) |
| **Critères d'acceptation** | ✅ Appel ML avec 10 features | ✅ Score retourné | ✅ Boost si rejet détecté | ✅ Fallback sans erreur | ✅ Explications incluses |
| **Dépendances** | US-007 |
| **Effort** | 5 points |

---

### US-011: Génération XML ISO 20022 (pacs.008 & pacs.003)

**En tant qu'** superviseur  
**Je souhaite** que le système génère automatiquement des fichiers XML ISO 20022 conformes  
**Afin que** les messages puissent être transmis à la banque centrale

| Aspect | Détail |
|--------|--------|
| **Services** | XmlTransformerService |
| **Models** | TcXmlProduit |
| **Formats générés** | pacs.008.001.10 (virement) | pacs.003.001.09 (chèque, prélèvement, LDC) |
| **Enveloppe** | RequestPayload + AppHdr (head.001.001.01) |
| **Namespace** | urn:iso:std:iso:20022:tech:xsd:pacs.008.001.10 |
| **Champs obligatoires** | MsgId, CreDtTm, NbOfTxs, IntrBkSttlmDt, SttlmInf, PmtTpInf, InstgAgt |
| **Devise** | TND (Tunisian Dinar) |
| **Sauvegarde** | TcXmlProduit (contenu_xml) |
| **Critères d'acceptation** | ✅ XML bien formé | ✅ Namespace correct | ✅ Tous champs ISO obligatoires | ✅ Format devise TND | ✅ Enveloppe AppHdr complète |
| **Dépendances** | US-007, US-009 |
| **Effort** | 8 points |

---

### US-012: Liste Fichiers avec Filtres et Statistiques

**En tant qu'** opérateur ou superviseur  
**Je souhaite** voir la liste de tous les fichiers uploadés avec filtres et statuts  
**Afin que** je suive la progression de mes uploads et validations

| Aspect | Détail |
|--------|--------|
| **Rôles concernés** | Superviseur, Opérateur (lecture seule) |
| **Composants Livewire** | `Livewire\Fichiers\Index` |
| **Model** | TcFichier |
| **Filtres** | nom_fichier (contains) | type_valeur | statut | date_reception (range) |
| **Colonnes affichées** | Nom, Type, Statut, Nb Transactions, Nb Rejets, Montant, Date upload, Opérateur |
| **Statistiques** | Total fichiers | Traités | En attente | Erreurs |
| **Tri** | Par date desc, par statut, par nombre rejets |
| **Pagination** | 25 par page |
| **Critères d'acceptation** | ✅ Liste paginée | ✅ Filtres multi-critères | ✅ Statistiques correctes | ✅ Tri fonctionnel | ✅ Clic sur ligne → détails |
| **Dépendances** | US-006, US-001, US-002 |
| **Effort** | 5 points |

---

### US-013: Affichage Détail Fichier & Transactions

**En tant qu'** superviseur  
**Je souhaite** voir le détail complet d'un fichier (enregistrements globaux/détails, rejets)  
**Afin que** je puisse analyser les transactions et décider de valider ou rejeter

| Aspect | Détail |
|--------|--------|
| **Rôles concernés** | Superviseur, Admin (modification), Opérateur (lecture) |
| **Composants Livewire** | `Livewire\Fichiers\Show` |
| **Affichage** | Section global (totaux) | Tableau détails (100 lignes) | Rejets détectés | Commentaires |
| **Colonnes détails** | RIB donneur, RIB bénéficiaire, montant, score_ml, motif_rejet si erreur |
| **Actions** | Valider fichier (si EN_ATTENTE_VALIDATION) | Rejeter fichier | Ajouter commentaire |
| **Models** | TcEnrDetail, TcRejet, TcCommentaire, TcEnrGlobal |
| **Critères d'acceptation** | ✅ Affiche global + détails | ✅ Affiche rejets avec motif | ✅ Permet validation/rejet | ✅ Historique commentaires | ✅ Score ML visible |
| **Dépendances** | US-006, US-012 |
| **Effort** | 6 points |

---

### US-014: Validation ou Rejet Fichier par Superviseur

**En tant qu'** superviseur  
**Je souhaite** valider ou rejeter un fichier après vérification des données  
**Afin que** seuls les fichiers conformes soient traités

| Aspect | Détail |
|--------|--------|
| **Rôles concernés** | Superviseur, Admin |
| **Services** | ValidationService, AuditService |
| **Model** | TcFichier |
| **Workflow** | EN_ATTENTE_VALIDATION → VALIDE (validate) ou REJETE_VALIDATION (reject) |
| **Champs updated** | statut, valide_par (user_id), date_validation, commentaire_rejet (si rejet) |
| **Notifications** | Notifier opérateur : "Fichier validé ✓" ou "Fichier rejeté ✗" + motif |
| **Audit** | Log action: "VALIDATION" ou "REJET_VALIDATION" |
| **Critères d'acceptation** | ✅ Changement statut correct | ✅ Enregistrer validateur | ✅ Commentaire optionnel | ✅ Notifier opérateur | ✅ Audit log action |
| **Dépendances** | US-013 |
| **Effort** | 4 points |

---

### US-015: Liste Rejets avec Filtres par Étape

**En tant qu'** superviseur  
**Je souhaite** voir tous les rejets détectés avec leur étape (parsing/validation) et code  
**Afin que** je puisse traiter les erreurs et générer les messages PACS.004

| Aspect | Détail |
|--------|--------|
| **Rôles concernés** | Superviseur, Admin |
| **Composants Livewire** | `Livewire\Rejets\Index` |
| **Model** | TcRejet |
| **Filtres** | Étape (PARSING\|VALIDATION) | Code rejet | Traité (oui/non) | Date |
| **Colonnes** | Fichier, Detail, Étape, Code rejet, Motif, Traité, Date |
| **Actions** | Marquer comme traité (single) | Marquer tous comme traité (bulk) |
| **Compteurs** | Total rejets | Parsing errors | Validation errors | Traités vs non traités |
| **Critères d'acceptation** | ✅ Liste paginée | ✅ Filtres multi-critères | ✅ Marquer traité (update TcRejet.traite = true) | ✅ Compteurs corrects | ✅ Tri par date desc |
| **Dépendances** | US-007, US-009 |
| **Effort** | 5 points |

---

## <a name="sprint3"></a>🎯 SPRINT 3 - Reporting, Exports & PACS.004
**Durée:** 2 semaines | **Objectif:** Génération rejets normalisés, rapports décisionnels, dashboards

### US-016: Génération PACS.004 pour Rejets Fichier

**En tant qu'** superviseur  
**Je souhaite** générer un message PACS.004 compilant tous les rejets d'un fichier  
**Afin que** je puisse renvoyer les erreurs à la banque centrale de manière normalisée

| Aspect | Détail |
|--------|--------|
| **Rôles concernés** | Superviseur, Admin |
| **Composants Livewire** | `Livewire\Rejets\Pacs004Generator` |
| **Services** | Pacs004TransformerService, AuditService |
| **Model** | TcPacs004 |
| **Format** | pacs.004.001.11 conforme SIBTEL/BFI |
| **Champs** | fichier_id, rejet_id, msg_id (unique), contenu_xml, valide_xsd (bool), statut, created_at |
| **Statuts** | GENERE → ENVOYE |
| **Validation** | XSD validate XML généré |
| **Audit** | Log: "PACS004_GENERE" avec nb_rejets |
| **Notification** | Notifier superviseur: "Pacs.004 généré (N rejets)" |
| **Critères d'acceptation** | ✅ Générer PACS.004 pour fichier | ✅ XML valide XSD | ✅ Télécharger XML possible | ✅ Marquer envoyé | ✅ Audit log génération | ✅ Notification superviseur |
| **Dépendances** | US-014, US-015 |
| **Effort** | 8 points |

---

### US-017: Visualisation et Export XML ISO 20022

**En tant qu'** superviseur ou opérateur  
**Je souhaite** visualiser et télécharger les fichiers XML générés  
**Afin que** je puisse examiner et transmettre les messages normatifs

| Aspect | Détail |
|--------|--------|
| **Rôles concernés** | Superviseur, Opérateur (lecture), Admin |
| **Composants Livewire** | `Livewire\Fichiers\XmlViewer` |
| **Models** | TcXmlProduit, TcPacs004 |
| **Affichage** | XML formaté avec syntax coloring (pacs.008 ou pacs.003 ou pacs.004) |
| **Actions** | Copier XML vers clipboard | Télécharger fichier XML (contenu_xml.xml) |
| **Critères d'acceptation** | ✅ Affiche XML avec coloration | ✅ Copier clipboard fonctionne | ✅ Télécharger XML fonctionne | ✅ Format UTF-8 |
| **Dépendances** | US-011, US-016 |
| **Effort** | 3 points |

---

### US-018: Centre de Notifications Utilisateur

**En tant qu'** utilisateur (tous rôles)  
**Je souhaite** voir mes notifications (validations, rejets, uploads)  
**Afin que** je suive en temps réel les événements importants

| Aspect | Détail |
|--------|--------|
| **Rôles concernés** | Admin, Superviseur, Opérateur |
| **Composants Livewire** | `Livewire\Notifications` |
| **Model** | TcNotification (user_id, titre, message, type, lu, fichier_id, created_at) |
| **Types** | UPLOAD, VALIDATION, REJET_VALIDATION, PACS004_GENERE |
| **Affichage** | Bell icon avec compteur non lus | Panel notifications (recent en haut) |
| **Actions** | Marquer comme lu | Marquer tous comme lu | Cliquer → aller au fichier |
| **Compteurs** | Nombre notifications non lues |
| **Critères d'acceptation** | ✅ Affiche notifications non lues | ✅ Compteur correct | ✅ Marquer lu fonctionnel | ✅ Clic vers fichier | ✅ Tri par date desc |
| **Dépendances** | US-006, US-014 |
| **Effort** | 4 points |

---

### US-019: Dashboard & KPI de Compensation

**En tant qu'** superviseur ou admin  
**Je souhaite** voir un dashboard avec KPI et tendances de la compensation  
**Afin que** je suive la performance globale et l'activité de compensation

| Aspect | Détail |
|--------|--------|
| **Rôles concernés** | Admin, Superviseur |
| **Composants Livewire** | `Livewire\Stats\Index` |
| **Métriques** | Fichiers traités (total) | Transactions valides/rejetées | Montant total traité | Taux rejet (%) | Évolution derniers 7 jours |
| **Graphiques** | Historique par jour (bar) | Répartition par type (pie) | Taux rejet tendance (line) |
| **Filtres** | Date début/fin | Type valeur |
| **Données** | TcFichier, TcEnrDetail, TcRejet, TcPacs004 |
| **Critères d'acceptation** | ✅ KPI affichés correctement | ✅ Graphiques générés | ✅ Filtrables par date/type | ✅ Données temps réel | ✅ Responsive design |
| **Dépendances** | US-006, US-009 |
| **Effort** | 6 points |

---

### US-020: Export Rapport PDF Détaillé

**En tant qu'** superviseur ou admin  
**Je souhaite** exporter un rapport PDF avec statistiques, fichiers et transactions  
**Afin que** je puisse l'envoyer à la direction pour décisions

| Aspect | Détail |
|--------|--------|
| **Rôles concernés** | Admin, Superviseur |
| **Composants Livewire** | `Livewire\RapportExport` |
| **Services** | ExportService (appel Python: generate_pdf.py) |
| **Contenu** | En-tête (période, généré par) | Résumé (KPI) | Tableau fichiers (50 lignes) | Tableau rejets top 20 | Graphiques |
| **Filtres** | Date début/fin | Type rapport (journalier/hebdomadaire/mensuel) |
| **Format** | PDF A4 landscape, tables formatées, graphiques |
| **Téléchargement** | rapport_btl_YYYYMMDD_HHMMSS.pdf |
| **Critères d'acceptation** | ✅ PDF généré | ✅ Filtre période fonctionne | ✅ Tables bien formatées | ✅ Graphiques inclus | ✅ Téléchargeable |
| **Dépendances** | US-019 |
| **Effort** | 6 points |

---

### US-021: Export Rapport Excel Complet

**En tant qu'** superviseur ou admin  
**Je souhaite** exporter un rapport Excel avec détails exhaustifs pour analyse  
**Afin que** je puisse analyser les données en profondeur dans Excel

| Aspect | Détail |
|--------|--------|
| **Rôles concernés** | Admin, Superviseur |
| **Composants Livewire** | `Livewire\RapportExport` |
| **Services** | ExportService (appel Python: generate_excel.py) |
| **Feuilles** | Résumé (KPI) | Fichiers (tous, 50+ colonnes) | Transactions (détails) | Rejets (tous) |
| **Contenu** | RIB (complet), montants, codes rejets, dates, scores ML |
| **Filtres** | Date début/fin |
| **Format** | XLSX avec styles et formules Excel |
| **Téléchargement** | rapport_btl_YYYYMMDD_HHMMSS.xlsx |
| **Critères d'acceptation** | ✅ Excel généré | ✅ Multiple feuilles | ✅ Données complètes | ✅ Formules Excel si nécessaire | ✅ Téléchargeable |
| **Dépendances** | US-019 |
| **Effort** | 6 points |

---

### US-022: Outil Vérification RIB Interactif

**En tant qu'** opérateur ou superviseur  
**Je souhaite** vérifier manuellement un RIB sans uploader de fichier  
**Afin que** je puisse corriger les erreurs avant upload ou identifier RIB invalides

| Aspect | Détail |
|--------|--------|
| **Rôles concernés** | Admin, Superviseur, Opérateur |
| **Composants Livewire** | `Livewire\Outils\VerificateurRib` |
| **Services** | RibValidatorService |
| **Entrée** | Champ texte RIB (numérisation automatique) |
| **Validation** | 20 chiffres | Modulo 97 | Code banque |
| **Résultat** | ✅ Valide | ❌ Invalide (motif: cle invalide / format invalid / banque invalide) | Clé calculée |
| **Critères d'acceptation** | ✅ Valider RIB | ✅ Afficher détail erreur | ✅ Afficher clé calculée | ✅ Feedback utilisateur clair |
| **Dépendances** | US-008 |
| **Effort** | 3 points |

---

### US-023: Système de Commentaires sur Fichiers

**En tant qu'** superviseur ou opérateur  
**Je souhaite** ajouter des commentaires sur un fichier pour communiquer avec l'équipe  
**Afin que** je puisse justifier mes actions et collaborer

| Aspect | Détail |
|--------|--------|
| **Rôles concernés** | Admin, Superviseur, Opérateur |
| **Composants Livewire** | `Livewire\Fichiers\Show` (section commentaires) |
| **Services** | ValidationService |
| **Model** | TcCommentaire (fichier_id, user_id, contenu, type: VALIDATION/REJET/COMMENTAIRE, created_at) |
| **Affichage** | Timeline commentaires (chronologique) avec auteur, date, contenu |
| **Actions** | Ajouter commentaire (textarea) |
| **Critères d'acceptation** | ✅ Ajouter commentaire | ✅ Afficher avec auteur/date | ✅ Timeline chronologique | ✅ Validation longueur (max 500 car) |
| **Dépendances** | US-013 |
| **Effort** | 3 points |

---

## <a name="matrice"></a>📊 Matrice de Traçabilité

### Dépendances User Stories

```
SPRINT 1:
  US-001 (Auth) → fondation
  US-002 (Rôles) → dépend US-001
  US-003 (Users) → dépend US-001, US-002
  US-004 (Audit) → dépend US-001
  US-005 (Profile) → dépend US-001

SPRINT 2:
  US-006 (Upload) → dépend US-001, US-002
  US-007 (Parsing) → dépend US-006
  US-008 (Val RIB) → dépend US-007
  US-009 (Val Métier) → dépend US-007, US-008
  US-010 (ML) → dépend US-007
  US-011 (XML) → dépend US-007, US-009
  US-012 (List) → dépend US-006, US-001, US-002
  US-013 (Detail) → dépend US-006, US-012
  US-014 (Valider) → dépend US-013
  US-015 (Rejets) → dépend US-007, US-009

SPRINT 3:
  US-016 (PACS004) → dépend US-014, US-015
  US-017 (XML View) → dépend US-011, US-016
  US-018 (Notif) → dépend US-006, US-014
  US-019 (Dashboard) → dépend US-006, US-009
  US-020 (PDF) → dépend US-019
  US-021 (Excel) → dépend US-019
  US-022 (RIB Tool) → dépend US-008
  US-023 (Comment) → dépend US-013
```

### Mapping Rôles ↔ User Stories

| Rôle | User Stories | Total |
|------|--------------|-------|
| **Admin** | US-001,002,003,004,005,013,014,016,017,018,019,020,021,022,023 | 15/23 |
| **Superviseur** | US-001,002,005,006,012,013,014,015,016,017,018,019,020,021,022,023 | 16/23 |
| **Opérateur** | US-001,002,005,006,012,013,017,018,022,023 | 10/23 |

---

## 📈 Résumé des Sprints

| Sprint | Focus Principal | User Stories | Effort Total | Rôles Focus |
|--------|-----------------|--------------|--------------|-------------|
| **Sprint 1** | 🔒 Fondations & Auth | US-001 → US-005 | 18 points | Admin |
| **Sprint 2** | 📥 Traitement Fichiers | US-006 → US-015 | 57 points | Superviseur, Opérateur |
| **Sprint 3** | 📤 Reporting & Exports | US-016 → US-023 | 42 points | Admin, Superviseur |

**Total effort:** 117 points (~6 semaines développement)

---

## ✅ Critères de Succès Globaux

✅ **Sécurité:** Authentification, rôles, audit complets  
✅ **Conformité:** SIBTEL & ISO 20022 respectés  
✅ **Performance:** Upload < 2s, Parsing < 5s, Dashboard réactif  
✅ **Qualité:** Logs complets, gestion erreurs robuste, tests  
✅ **UX:** Interface intuitive, notifications claires, responsive  
✅ **ML:** Intégration estimation risque 100% fonctionnelle  

---

**Fin du Product Backlog Académique**
