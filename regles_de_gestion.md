# Règles de gestion --- Système de recrutement et RH

## 1. Objet du document

Ce document formalise les règles métier de l'application de recrutement
et de gestion RH. Il sert de référence commune pour la base de données,
les pages, les contrôles applicatifs et les tests.

Les règles reprennent les besoins décrits pour les espaces Utilisateur,
Département, RH, Direction et Candidat. Les points qui nécessitent une
décision supplémentaire sont signalés dans la dernière section.

## 2. Acteurs et responsabilités

-   **Utilisateur non connecté :** peut accéder à la connexion et à
    l'inscription candidat.
-   **Département :** consulte les postes et les demandes, crée des
    postes, définit des conditions initiales et propose des demandes de
    recrutement.
-   **RH :** traite les demandes, confirme les conditions de contrat,
    publie les offres selon le circuit de validation, consulte les
    candidatures, organise les évaluations et traite les requêtes des
    candidats.
-   **Direction :** valide les offres selon le workflow retenu, consulte
    les candidatures et évaluations, et prend la décision finale de
    retenir un candidat.
-   **Candidat :** consulte les offres disponibles, postule, consulte
    ses notifications et propose éventuellement des modifications aux
    conditions de contrat.
-   **Administrateur :** rôle technique à préciser selon les besoins de
    gestion des comptes et des référentiels.

Un utilisateur ne peut effectuer que les actions autorisées pour son
rôle.

## 3. Comptes, personnes et authentification

**RG-UTIL-001 --- Email unique**\
Chaque compte utilisateur doit posséder une adresse email unique.

**RG-UTIL-002 --- Mot de passe**\
Le mot de passe doit être conservé sous forme de hash sécurisé, jamais
en clair.

**RG-UTIL-003 --- Inscription candidat**\
Lorsqu'un candidat s'inscrit, le système crée les données nécessaires
dans `utilisateur`, `personne` et `candidat`.

**RG-UTIL-004 --- Connexion obligatoire pour postuler**\
Seul un candidat connecté peut déposer une candidature.

**RG-UTIL-005 --- Contrôle des autorisations**\
Les pages et opérations doivent vérifier le rôle et les droits de
l'utilisateur côté serveur, pas uniquement masquer les boutons dans
l'interface.

## 4. Départements et postes

**RG-POSTE-001 --- Appartenance**\
Un poste est rattaché à un département.

**RG-POSTE-002 --- Création d'un poste**\
Un membre autorisé du Département peut créer un poste avec son libellé,
sa description éventuelle et son département.

**RG-POSTE-003 --- Conditions initiales**\
Après la création d'un poste, l'utilisateur est redirigé vers la gestion
des conditions de contrat associées à ce poste.

**RG-POSTE-004 --- Statut initial des conditions**\
Chaque condition nouvellement créée reçoit le statut `CREER`.

**RG-POSTE-005 --- Historique du poste**\
Tout changement de statut d'un poste est enregistré dans
`historique_poste` avec l'ancien statut, le nouveau statut,
l'utilisateur et la date.

**RG-POSTE-006 --- Historique des conditions**\
Tout changement de statut d'une condition est enregistré dans
`historique_condition_contrat`. Les modifications de contenu doivent
également être traçables si le projet conserve les versions.

## 5. Demandes de recrutement

**RG-DEM-001 --- Informations obligatoires**\
Une demande de recrutement doit indiquer le poste, le demandeur,
l'effectif demandé, le motif, le salaire proposé par personne et la date
du besoin.

**RG-DEM-002 --- Effectif positif**\
L'effectif demandé doit être strictement supérieur à zéro.

**RG-DEM-003 --- Demandeur connecté**\
`id_demandeur` correspond à l'utilisateur connecté qui soumet la
demande. Il ne doit pas être librement choisi dans le formulaire.

**RG-DEM-004 --- Statut initial**\
Une demande nouvellement créée est enregistrée avec le statut
`BROUILLON`.

**RG-DEM-005 --- Consultation**\
Le Département et le RH peuvent consulter les demandes selon leurs
autorisations respectives et voir leur statut courant.

**RG-DEM-006 --- Décision et validation**\
Les décisions de validation doivent être enregistrées dans
`validation_demande`, avec le validateur, le type de validation, la
décision, la date et un commentaire éventuel.

**RG-DEM-007 --- Historique obligatoire**\
Chaque changement de statut d'une demande est enregistré dans
`historique_demande`.

**RG-DEM-008 --- Refus et remise en attente**\
Le RH peut remettre en attente une demande qu'il a lui-même refusée,
conformément au besoin exprimé. L'application doit vérifier l'identité
de l'auteur de la décision et enregistrer cette nouvelle transition.

**RG-DEM-009 --- Pas de perte d'historique**\
Une nouvelle décision ne doit pas écraser les décisions précédentes dans
les tables d'historique.

## 6. Conditions de contrat d'un poste

**RG-COND-001 --- Association à un poste**\
Chaque condition est associée à un poste.

**RG-COND-002 --- Données d'une condition**\
Une condition possède un libellé, un type de valeur, une valeur et un
ordre d'affichage.

**RG-COND-003 --- Types de valeur**\
Le type de valeur décrit la nature de la donnée (par exemple `TEXTE` ou
`NOMBRE`) et doit être séparé de la valeur réelle.

**RG-COND-004 --- Confirmation RH**\
Le RH peut consulter, ajouter, modifier et, selon les règles définies,
supprimer les conditions d'un poste. Il peut confirmer les conditions en
passant leur statut à `CONFIRMER`.

**RG-COND-005 --- Traçabilité**\
Les changements de statut des conditions sont enregistrés dans
`historique_condition_contrat`.

**RG-COND-006 --- Conditions utilisées dans un contrat**\
Les conditions applicables à un contrat signé doivent être figées dans
le contrat concerné. Une modification ultérieure des conditions
générales du poste ne doit pas modifier un contrat déjà signé.

## 7. Offres d'emploi

**RG-OFFRE-001 --- Origine d'une offre**\
Une offre d'emploi est liée à une demande de recrutement.

**RG-OFFRE-002 --- Création**\
L'offre contient au minimum un titre, une description, une date de
publication et éventuellement une date de clôture.

**RG-OFFRE-003 --- Validation de la Direction**\
Le circuit décrit prévoit une validation de l'offre par la Direction
avant sa publication. La liste exacte des statuts intermédiaires doit
être confirmée.

**RG-OFFRE-004 --- Publication**\
Une offre ne peut être visible comme offre disponible pour les candidats
que lorsqu'elle est effectivement publiée et non clôturée.

**RG-OFFRE-005 --- Historique**\
Chaque changement de statut d'une offre est enregistré dans
`historique_offre_emploi`.

**RG-OFFRE-006 --- Fermeture**\
Une offre fermée ne doit plus accepter de nouvelles candidatures.

## 8. Candidatures et pièces jointes

**RG-CAND-001 --- Candidature liée à une offre**\
Chaque candidature associe un candidat à une offre d'emploi.

**RG-CAND-002 --- Une candidature par offre**\
Un candidat ne peut déposer qu'une seule candidature pour une même
offre.

**RG-CAND-003 --- Pièces obligatoires**\
Le dépôt de candidature exige : - un CV au format PDF ; - une lettre de
motivation au format PDF ; - une CIN au format image ou PDF.

**RG-CAND-004 --- Validation des fichiers**\
Le serveur vérifie la présence, la taille et le type des fichiers. Il ne
doit pas se fier uniquement au nom de fichier ou au type déclaré par le
navigateur.

**RG-CAND-005 --- Statut initial**\
Une candidature nouvellement déposée reçoit le statut `RECUE`.

**RG-CAND-006 --- Informations personnelles**\
Les informations du candidat, notamment son email et son téléphone, sont
accessibles uniquement aux utilisateurs autorisés.

**RG-CAND-007 --- Historique obligatoire**\
Chaque changement de statut d'une candidature est enregistré dans
`historique_candidature` avec l'ancien statut, le nouveau statut,
l'utilisateur et la date.

**RG-CAND-008 --- Présélection par le RH**\
Lorsque le RH retient un dossier à ce stade, la candidature passe à
`PRESELECTIONNEE`. La transition est historisée.

**RG-CAND-009 --- Refus par le RH**\
Lorsque le RH refuse une candidature, son statut passe à `REFUSEE` et le
changement est historisé.

**RG-CAND-010 --- Décision finale de la Direction**\
Lorsque la Direction retient définitivement un candidat, la candidature
passe à `RETENUE` et le changement est historisé.

## 9. Entretiens et tests techniques

**RG-EVAL-001 --- Planification**\
Un entretien est associé à une candidature et à un organisateur. Il
comporte une date et reçoit par défaut le statut `PLANIFIE`.

**RG-EVAL-002 --- Statuts d'entretien**\
Les statuts d'entretien prévus sont `PLANIFIE`, `REALISE` et `ANNULE`.

**RG-EVAL-003 --- Historique d'entretien**\
Chaque changement de statut d'un entretien est enregistré dans
`historique_entretien`.

**RG-EVAL-004 --- Évaluation d'entretien**\
L'évaluation d'un entretien n'est disponible que lorsque l'entretien est
`REALISE`.

**RG-EVAL-005 --- Tests techniques**\
Un test technique est associé à une candidature, possède un
organisateur, une date et un statut initial `PLANIFIE`.

**RG-EVAL-006 --- Historique des tests**\
Chaque changement de statut d'un test technique est enregistré dans
`historique_test_technique`.

**RG-EVAL-007 --- Types d'évaluation**\
Les types d'évaluation prévus sont `DOSSIER`, `ENTRETIEN` et
`TEST_TECHNIQUE`.

**RG-EVAL-008 --- Notes**\
Une note, lorsqu'elle est renseignée, doit être comprise entre 0 et 10.

**RG-EVAL-009 --- Modification d'une évaluation**\
Si une évaluation existe déjà, elle est affichée et peut être modifiée
par un utilisateur autorisé. Le projet doit décider si les anciennes
versions des notes sont conservées.

**RG-EVAL-010 --- Sélection ou refus**\
Les décisions de sélection ou de refus prises après les évaluations
doivent modifier le statut de la candidature conformément au workflow et
être historisées.

## 10. Requêtes du candidat sur les conditions

**RG-REQ-001 --- Conditions présentées**\
Le candidat retenu peut consulter les conditions confirmées du poste
associé à sa candidature.

**RG-REQ-002 --- Proposition de modification**\
Le candidat peut proposer des modifications sur plusieurs conditions et
les soumettre en une seule fois.

**RG-REQ-003 --- Champ laissé vide**\
Une condition laissée vide par le candidat ne constitue pas une demande
de modification et ne doit pas changer la condition initiale.

**RG-REQ-004 --- Traçabilité**\
Chaque proposition enregistrée doit être associée à la candidature, à la
condition concernée et à sa date de création.

**RG-REQ-005 --- Décision RH**\
Le RH examine chaque requête et peut l'accepter ou la refuser avant la
transmission à la Direction.

**RG-REQ-006 --- Décision de la Direction**\
La décision finale sur les requêtes appartient à la Direction. La
réponse de la Direction est la décision de référence pour déterminer les
conditions définitives du contrat.

**RG-REQ-007 --- Historique des décisions**\
Les décisions successives du RH et de la Direction sont conservées dans
l'historique. Elles ne doivent pas être écrasées par la décision
suivante.

**RG-REQ-008 --- Conditions définitives**\
Les conditions retenues pour le contrat sont les conditions initiales
confirmées, ajustées uniquement par les modifications validées selon le
circuit de décision.

## 11. Notifications

**RG-NOTIF-001 --- Notification de retenue**\
Lorsque la Direction retient une candidature, le système crée une
notification destinée au candidat concerné.

**RG-NOTIF-002 --- Lien**\
La notification contient un lien vers la page pertinente, notamment les
conditions du contrat.

**RG-NOTIF-003 --- Lecture**\
Le système permet de distinguer les notifications lues des notifications
non lues.

**RG-NOTIF-004 --- Autorisation**\
Un candidat ne peut consulter que ses propres notifications et les pages
accessibles à son compte.

## 12. Contrat et création de l'employé

**RG-CONTRAT-001 --- Contrat lié à une candidature**\
Le contrat préparé à la suite du recrutement est lié à la candidature
concernée.

**RG-CONTRAT-002 --- Contenu du contrat**\
Le contrat doit présenter l'employeur, le salarié, le lieu de travail,
la date de début, le poste, le salaire, le type et la durée du contrat,
les missions, les horaires, les congés et absences, ainsi que les
informations de signature.

**RG-CONTRAT-003 --- Types de contrat**\
Les types prévus sont `CE`, `CDD` et `CDI`. La signification métier de
`CE` doit être confirmée.

**RG-CONTRAT-004 --- Date de fin**\
La date de fin est facultative pour un CDI. Les règles applicables aux
autres types de contrat doivent être précisées.

**RG-CONTRAT-005 --- Cycle du contrat**\
Les statuts prévus dans le dictionnaire sont `BROUILLON`, `A_SIGNER`,
`SIGNE` et `ROMPU`.

**RG-CONTRAT-006 --- Génération PDF**\
Après validation définitive des conditions et acceptation du contrat, le
système génère un contrat PDF contenant les conditions applicables.

**RG-CONTRAT-007 --- Signature**\
Le contrat ne doit être considéré comme signé que lorsque le mécanisme
de signature défini pour le projet a été accompli.

**RG-CONTRAT-008 --- Historique du contrat**\
Chaque changement de statut du contrat est enregistré dans
`historique_contrat`.

**RG-CONTRAT-009 --- Création de l'employé**\
Le profil `employe` est créé après la signature du premier contrat.

**RG-CONTRAT-010 --- Matricule**\
Chaque employé possède un matricule unique.

**RG-CONTRAT-011 --- Affectation initiale**\
Lors de la création de l'employé, une affectation initiale vers le poste
et le département concernés est enregistrée.

**RG-CONTRAT-012 --- Historique employé**\
Chaque changement de statut d'un employé est enregistré dans
`historique_employe`.

## 13. Historique et intégrité des opérations

**RG-HIST-001 --- Traçabilité des statuts**\
Toute transition de statut prévue par le système doit enregistrer au
minimum l'entité concernée, l'ancien statut, le nouveau statut, l'auteur
et la date.

**RG-HIST-002 --- Premier statut**\
Lorsqu'une entité est créée, l'historique peut enregistrer une
transition dont l'ancien statut est nul et le nouveau statut correspond
au statut initial.

**RG-HIST-003 --- Pas d'historique fictif**\
Une entrée d'historique ne doit être créée que pour un événement
réellement effectué.

**RG-HIST-004 --- Cohérence transactionnelle**\
La modification du statut et l'insertion de son historique doivent
réussir ou échouer ensemble lorsque la base de données le permet, afin
d'éviter des données incohérentes.

**RG-HIST-005 --- Auteur et date**\
L'auteur doit provenir de la session authentifiée et la date doit être
fournie par le serveur ou la base de données.

**RG-HIST-006 --- Conservation**\
Les historiques ne doivent pas être écrasés lors d'une nouvelle
transition.

## 14. Points métier à confirmer

Les points ci-dessous ne sont pas suffisamment déterminés dans les
besoins et doivent être tranchés par l'équipe.

1.  **Validation de la demande :** le RH fait-il passer la demande de
    `BROUILLON` à `EN_ATTENTE`, puis la Direction ou la Finance
    prend-elle la décision finale ? Le texte mentionne plusieurs
    possibilités.
2.  **Statuts de demande :** confirmer l'orthographe et le sens exacts
    de `REFUSE`/`REFUSEE`, ainsi que les transitions autorisées.
3.  **Validation de l'offre :** préciser le statut initial, les statuts
    intermédiaires et l'acteur autorisé à publier l'offre.
4.  **Conditions confirmées :** déterminer si le RH peut modifier ou
    supprimer une condition après confirmation.
5.  **Évaluations :** déterminer si les évaluations précédentes doivent
    être conservées lorsqu'une note est modifiée.
6.  **Entretiens et tests :** déterminer si une candidature peut avoir
    plusieurs entretiens et plusieurs tests techniques.
7.  **Requêtes :** définir précisément les statuts et le passage entre
    décision RH et décision Direction.
8.  **Signature du contrat :** préciser si le candidat signe en ligne ou
    téléverse un document signé.
9.  **Type `CE` :** définir sa signification.
10. **Salaire :** préciser l'unité et la périodicité du salaire.
11. **Documents :** définir les tailles maximales autorisées et les
    règles de conservation des pièces d'identité.
12. **Suppression :** préciser quelles données peuvent être supprimées
    et lesquelles doivent être conservées pour assurer la traçabilité.

## 15. Principes d'implémentation

-   Valider les données côté serveur.
-   Utiliser des clés étrangères et contraintes SQL pour les règles
    d'intégrité.
-   Utiliser des transactions pour les opérations composées.
-   Contrôler les permissions sur chaque route et chaque document.
-   Ne pas exposer publiquement les fichiers de candidature.
-   Tester les transitions autorisées et les transitions interdites.
-   Mettre à jour ce document lorsque l'équipe prend une décision
    métier.
