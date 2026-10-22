# TODO --- Système de recrutement et RH

> Projet : application de recrutement et de gestion RH\
> Répartition proposée : Danie, John, Tsiky et Rindra\
> Convention : chaque tâche terminée doit être testée et cochée
> (`[x]`).\
> Les tâches marquées **À confirmer** nécessitent une décision commune
> avant l'implémentation.

## 0. Règles de travail communes

-   [ ] Utiliser Git et travailler sur des branches distinctes.
-   [ ] Se mettre d'accord sur les noms des tables, colonnes, statuts et
    routes avant de coder.
-   [ ] Mettre à jour le dictionnaire de données après toute
    modification du schéma.
-   [ ] Chaque changement de statut doit être enregistré dans la table
    d'historique correspondante.
-   [ ] Exécuter les changements métier liés entre eux dans une
    transaction lorsque nécessaire.
-   [ ] Vérifier les droits selon le rôle : `CANDIDAT`, `RH`,
    `DIRECTION`, `ADMINISTRATEUR` (et `FINANCE` si ce rôle est
    conservé).
-   [ ] Ne jamais enregistrer de mot de passe en clair.
-   [ ] Protéger les documents de candidature (CV, lettre de motivation,
    CIN) : accès réservé aux utilisateurs autorisés.
-   [ ] Ajouter des tests aux règles de gestion importantes.
-   [ ] Préparer une démonstration intégrée de bout en bout.

------------------------------------------------------------------------

## Personne Danie --- Base de données, authentification et socle commun

### Danie.1. Base de données

-   [ ] Relire le dictionnaire de données existant et le comparer aux
    besoins fonctionnels.
-   [ ] Finaliser le schéma relationnel et les clés étrangères.
-   [ ] Vérifier les contraintes d'unicité et les champs obligatoires.
-   [ ] Compléter `condition_contrat` pour distinguer le type de valeur
    de la valeur réelle.
-   [ ] Vérifier les statuts autorisés pour `demande_recrutement`,
    `offre_emploi`, `candidature`, `entretien`, `test_technique`,
    `contrat` et `employe`.
-   [ ] Ajouter les tables d'historique manquantes : `historique_poste`,
    `historique_offre_emploi`, `historique_condition_contrat`,
    `historique_entretien`, `historique_test_technique`,
    `historique_employe` et `historique_formulaire_reponse`.
-   [ ] Ajouter les tables complémentaires : `notification`,
    `validation_offre` et `contrat_condition`.
-   [ ] Compléter `evaluation_candidat` pour référencer les tests
    techniques si nécessaire.
-   [ ] Ajouter les index nécessaires aux recherches par statut, date et
    clés étrangères.
-   [ ] Écrire le script de création de la base et un script de données
    de test.
-   [ ] Vérifier que les scripts peuvent être exécutés sur une base
    vide.

### Danie.2. Authentification et comptes

-   [ ] Développer la page de connexion.
-   [ ] Vérifier l'email et le mot de passe.
-   [ ] Gérer les erreurs de connexion sans révéler d'informations
    sensibles.
-   [ ] Développer l'inscription des candidats.
-   [ ] Contrôler l'unicité de l'email.
-   [ ] Créer les enregistrements liés dans `utilisateur`, `personne` et
    `candidat`.
-   [ ] Gérer la déconnexion et la session.
-   [ ] Contrôler les accès aux pages selon le rôle.
-   [ ] Tester les cas valides et invalides.

### Danie.3. Socle commun et intégration

-   [ ] Définir les conventions communes : routes, noms des paramètres,
    format des erreurs et accès aux données.
-   [ ] Préparer la configuration de la base de données.
-   [ ] Définir une méthode commune pour enregistrer les changements de
    statut et leurs historiques.
-   [ ] Définir une méthode commune pour créer les notifications.
-   [ ] Relire les migrations et les scripts fournis par les autres
    membres.
-   [ ] Intégrer et tester les modules ensemble.

**Livrables Danie :** scripts SQL, schéma à jour, données de test,
connexion/inscription, contrôle des rôles et conventions communes.

------------------------------------------------------------------------

## Personne John --- Espace Département et demandes de recrutement

### John.1. Accueil Département

-   [ ] Créer la page d'accueil du Département.
-   [ ] Afficher la liste des postes disponibles.
-   [ ] Afficher la liste des demandes de recrutement avec leur statut.
-   [ ] Prévoir les filtres utiles.

### John.2. Gestion des postes

-   [ ] Créer le formulaire d'ajout d'un poste.
-   [ ] Associer le poste à un département.
-   [ ] Enregistrer le statut initial du poste.
-   [ ] Après création, rediriger vers la gestion des conditions du
    contrat du poste.
-   [ ] Enregistrer les changements de statut du poste dans
    `historique_poste`.

### John.3. Conditions de contrat proposées par le Département

-   [ ] Créer un formulaire permettant d'ajouter plusieurs conditions en
    une fois.
-   [ ] Enregistrer le libellé, le type de valeur, la valeur et l'ordre
    d'affichage.
-   [ ] Affecter le statut initial `CREER`.
-   [ ] Permettre de consulter les conditions d'un poste.
-   [ ] Tester l'ajout de plusieurs conditions et la validation des
    champs.

### John.4. Création d'une demande de recrutement

-   [ ] Créer le formulaire de demande de recrutement.
-   [ ] Proposer une liste déroulante des départements.
-   [ ] Proposer une liste des postes du département sélectionné.
-   [ ] Ajouter un accès rapide à la création d'un poste.
-   [ ] Saisir l'effectif demandé, le motif, le salaire par personne et
    la date du besoin.
-   [ ] Déduire `id_demandeur` de l'utilisateur connecté.
-   [ ] Enregistrer le statut initial `BROUILLON`.
-   [ ] Afficher le détail de la demande et son statut courant.
-   [ ] Tester les champs obligatoires et les valeurs numériques
    positives.

**Livrables John :** pages Département, gestion des postes, conditions
initiales et création/consultation des demandes.

------------------------------------------------------------------------

## Personne Tsiky --- Espace RH : validation, offres, candidatures et évaluations

### Tsiky.1. Accueil RH et demandes

-   [ ] Afficher les demandes de recrutement avec leur statut.
-   [ ] Afficher les détails d'une demande.
-   [ ] Pour une demande `BROUILLON`, permettre au RH d'ouvrir le détail
    et de décider de la suite.
-   [ ] Enregistrer le passage à `EN_ATTENTE` ou `REFUSE` selon la
    décision retenue dans le workflow.
-   [ ] Permettre au RH ayant refusé une demande de la remettre en
    `EN_ATTENTE`, conformément aux règles d'autorisation.
-   [ ] Enregistrer chaque changement dans `historique_demande`.
-   [ ] Enregistrer chaque décision dans `validation_demande`.
-   [ ] Gérer les validations supplémentaires éventuelles
    (Direction/Finance) après clarification du workflow.

### Tsiky.2. Confirmation des conditions de contrat

-   [ ] Afficher toutes les conditions d'un poste.
-   [ ] Permettre au RH de modifier une condition.
-   [ ] Permettre d'ajouter une condition.
-   [ ] Permettre de supprimer une condition tant que les règles métier
    l'autorisent.
-   [ ] Ajouter un bouton de confirmation des conditions.
-   [ ] Passer les conditions concernées à `CONFIRMER`.
-   [ ] Enregistrer les changements dans `historique_condition_contrat`.

### Tsiky.3. Publication des offres

-   [ ] Afficher les demandes validées pouvant être publiées.
-   [ ] Préparer le formulaire de création d'une offre à partir d'une
    demande validée.
-   [ ] Enregistrer le titre, la description et les dates.
-   [ ] Gérer le workflow de validation de l'offre par la Direction.
-   [ ] Enregistrer les changements de statut dans
    `historique_offre_emploi`.
-   [ ] Afficher la liste des offres publiées.

### Tsiky.4. Candidatures et pièces jointes

-   [ ] Afficher les candidatures d'une offre avec leur statut courant.
-   [ ] Créer la page de détail d'une candidature.
-   [ ] Afficher les informations du candidat, notamment email et
    téléphone.
-   [ ] Donner un accès autorisé au CV PDF, à la lettre de motivation
    PDF et à la CIN (image ou PDF).
-   [ ] Vérifier la présence, le type et la taille des fichiers envoyés.
-   [ ] Enregistrer les décisions `PRESELECTIONNEE` ou `REFUSEE`.
-   [ ] Insérer chaque changement dans `historique_candidature`.
-   [ ] Après présélection, permettre d'ouvrir le formulaire de
    planification d'entretien.

### Tsiky.5. Entretiens et tests techniques

-   [ ] Créer le formulaire de planification d'entretien avec
    candidature, organisateur, date et statut initial `PLANIFIE`.
-   [ ] Afficher la liste des entretiens et leur statut.
-   [ ] Permettre de modifier le statut d'un entretien.
-   [ ] Enregistrer les changements dans `historique_entretien`.
-   [ ] Afficher le bouton d'évaluation uniquement lorsque l'entretien
    est `REALISE`.
-   [ ] Créer la gestion des tests techniques : formulaire, liste et
    changement de statut.
-   [ ] Enregistrer les changements dans `historique_test_technique`.
-   [ ] Prévoir les comptes rendus et les dates nécessaires.

### Tsiky.6. Évaluations et requêtes du candidat

-   [ ] Permettre l'évaluation du dossier (`DOSSIER`) et la modification
    d'une évaluation existante.
-   [ ] Permettre l'évaluation d'un entretien (`ENTRETIEN`) réalisé.
-   [ ] Permettre l'évaluation d'un test technique (`TEST_TECHNIQUE`)
    réalisé.
-   [ ] Afficher les évaluations existantes et leurs notes/commentaires.
-   [ ] Permettre les décisions de sélection prévues par le workflow.
-   [ ] Créer la liste des candidats ayant envoyé des requêtes sur les
    conditions du contrat.
-   [ ] Afficher toutes les requêtes d'un candidat.
-   [ ] Permettre au RH d'accepter ou de refuser chaque requête avant
    transmission à la Direction.
-   [ ] Enregistrer les décisions et leurs historiques.

**Livrables Tsiky :** espace RH, validation des demandes, offres, dossiers
de candidature, évaluations, entretiens, tests et traitement RH des
requêtes.

------------------------------------------------------------------------

## Personne D --- Direction, espace Candidat, notifications et contrats

### D.1. Espace Direction

-   [x] Créer la page d'accueil avec la liste des offres et leurs
    statuts.
-   [x] Permettre de consulter une offre en attente de validation.
-   [x] Permettre de valider ou refuser une offre selon le workflow
    final.
-   [x] Enregistrer chaque décision et chaque changement de statut.
-   [x] Créer la liste des candidatures avec filtre par statut.
-   [x] Créer la page de détail d'une candidature.
-   [x] Afficher les informations du candidat et les liens protégés vers
    CV, lettre de motivation et CIN.
-   [x] Afficher les évaluations RH de tous les types.
-   [x] Permettre à la Direction de retenir une candidature.
-   [x] Passer la candidature au statut `RETENUE` et insérer une ligne
    dans `historique_candidature`.
-   [x] Déclencher une notification destinée au candidat, avec un lien
    vers les conditions du contrat.

### D.2. Espace Candidat et dépôt de candidature

-   [x] Créer la page d'accueil du candidat.
-   [x] Afficher les offres publiées et encore ouvertes.
-   [x] Ajouter le bouton « Postuler ».
-   [x] Restreindre le dépôt de candidature aux utilisateurs connectés
    ayant un profil candidat.
-   [x] Créer le formulaire de candidature.
-   [x] Rendre le CV PDF obligatoire.
-   [x] Rendre la lettre de motivation PDF obligatoire.
-   [x] Rendre la CIN obligatoire, au format image ou PDF.
-   [x] Empêcher un candidat de postuler deux fois à la même offre.
-   [x] Enregistrer la candidature avec le statut initial `RECUE`.
-   [x] Afficher les notifications et distinguer celles qui sont
    lues/non lues.
-   [x] Permettre d'ouvrir le détail d'une notification et son lien
    associé.

### D.3. Requêtes sur les conditions de contrat

-   [x] Afficher les conditions confirmées du poste concerné.
-   [x] Afficher un champ de proposition pour chaque condition.
-   [x] Permettre au candidat d'envoyer plusieurs propositions en une
    seule soumission.
-   [x] Ne pas créer de requête pour les conditions laissées vides.
-   [x] Enregistrer les propositions et leur motif si demandé.
-   [x] Transmettre les requêtes au RH.
-   [x] Afficher au candidat le statut de traitement de chaque requête.

### D.4. Contrat et embauche

-   [x] Créer la page du formulaire de contrat.
-   [x] Afficher les informations de l'employeur et du salarié.
-   [x] Afficher le lieu de travail, le poste, la date de début et le
    salaire.
-   [x] Afficher le type de contrat (`CE`, `CDD` ou `CDI`) et les
    informations de durée.
-   [x] Afficher les missions, horaires, congés et absences.
-   [x] Intégrer la date et le mécanisme de signature défini pour le
    projet.
-   [x] Enregistrer la réponse du candidat à la proposition de contrat.
-   [x] Après acceptation définitive, figer les conditions applicables
    dans `contrat_condition`.
-   [x] Générer le contrat au format PDF.
-   [x] Enregistrer le document généré et son statut.
-   [x] À la signature, mettre à jour le contrat et enregistrer
    `historique_contrat`.
-   [x] Créer l'employé seulement au moment prévu par la règle métier,
    après signature.
-   [x] Attribuer un matricule unique.
-   [x] Créer l'affectation initiale du nouvel employé.
-   [x] Enregistrer le statut initial de l'employé et son historique.

**Livrables D :** espace Direction, espace Candidat, notifications,
requêtes sur les conditions, génération de contrat PDF et processus
d'embauche.

------------------------------------------------------------------------

## 5. Intégration et recette --- tâches partagées

-   [x] Vérifier l'inscription et la connexion.
-   [x] Tester le cycle complet d'une demande : `BROUILLON` →
    validation(s) → publication.
-   [x] Tester le cycle d'une offre : validation Direction → publication
    → fermeture.
-   [x] Tester le dépôt d'une candidature et les pièces jointes.
-   [x] Tester le cycle de candidature : `RECUE` → `PRESELECTIONNEE` →
    évaluation(s) → `SELECTIONNEE`/`REFUSEE` → décision de la Direction.
-   [x] Vérifier que chaque changement de statut écrit une ligne
    d'historique.
-   [x] Tester les autorisations de chaque rôle.
-   [x] Tester les requêtes candidat et les décisions RH/Direction.
-   [x] Tester la notification de retenue.
-   [x] Tester la génération du PDF et la création de l'employé après
    signature.
-   [x] Vérifier les contraintes SQL, les transactions et les cas
    d'erreur.
-   [x] Mettre à jour `README.md`, le dictionnaire de données et les
    règles de gestion.

## 6. Points à décider en équipe avant de terminer

-   [ ] **Validation de la demande :** le RH fait-il passer la demande à
    `EN_ATTENTE`, puis la Direction/Finance décide-t-elle, ou le RH
    prend-il la décision finale ?
-   [ ] **Validation de l'offre :** confirmer les statuts intermédiaires
    et l'acteur qui déclenche la publication.
-   [ ] **Refus :** définir si `REFUSE` et `REFUSEE` sont les libellés
    définitifs des statuts de demande et d'offre.
-   [ ] **Évaluations :** décider si plusieurs évaluations par type sont
    permises et si l'historique des notes doit être conservé.
-   [ ] **Signature :** préciser si la signature est électronique ou si
    le candidat dépose un contrat signé.
-   [ ] **Type `CE` :** préciser sa signification et ses règles métier.
-   [ ] **Salaire :** confirmer si le salaire de la demande est mensuel,
    annuel ou exprimé autrement.
-   [ ] **Suppression des conditions :** préciser si une condition
    confirmée peut être supprimée ou seulement désactivée.
