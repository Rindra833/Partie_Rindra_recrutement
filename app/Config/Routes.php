<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 *
 * Structure par rôle — chaque groupe est protégé par le filtre `role:XXX`.
 * Les contrôleurs placeholder permettent à chaque membre de commencer
 * sans se bloquer sur l'auth (Danie finalisera AuthController).
 *
 * Rôles SQL : DEPARTEMENT | RH | DIRECTION | CANDIDAT | ADMINISTRATEUR | FINANCE
 */

// -------------------------------------------------------------------------
// Public (non authentifié)
// -------------------------------------------------------------------------
$routes->get('/', 'AuthController::connexion');
$routes->get('connexion', 'AuthController::connexion');
$routes->post('connexion', 'AuthController::checkConnexion');
$routes->get('inscription', 'AuthController::inscription');
$routes->post('inscription', 'AuthController::registerCandidat');
$routes->get('deconnexion', 'AuthController::deconnexion');

// -------------------------------------------------------------------------
// Espace Département — John
// -------------------------------------------------------------------------
$routes->group('departement', ['filter' => 'role:DEPARTEMENT'], static function ($routes) {
    $routes->get('/', 'Departement\AccueilController::index');
    // John ajoutera : postes, conditions, demandes
});

// -------------------------------------------------------------------------
// Espace RH — Tsiky
// -------------------------------------------------------------------------
$routes->group('rh', ['filter' => 'role:RH'], static function ($routes) {
    $routes->get('/', 'Rh\AccueilController::index');
    // Tsiky ajoutera : demandes, conditions, offres, candidatures, entretiens, tests, évaluations, requêtes
});

// -------------------------------------------------------------------------
// Espace Direction — D / Rindra
// -------------------------------------------------------------------------
$routes->group('direction', ['filter' => 'role:DIRECTION,FINANCE'], static function ($routes) {
    $routes->get('/', 'Direction\AccueilController::index');
    // Direction : validation offres, candidatures, retenue, contrats
});

// -------------------------------------------------------------------------
// Espace Direction — D / Rindra
// -------------------------------------------------------------------------
$routes->group('direction', ['filter' => 'role:DIRECTION,FINANCE'], static function ($routes) {
    $routes->get('/', 'Direction\AccueilController::index');

    // Offres (D.1.1)
    $routes->get('offres',                'Direction\OffresController::index');
    $routes->get('offres/(:num)',         'Direction\OffresController::show/$1');
    $routes->post('offres/(:num)/valider','Direction\OffresController::valider/$1');
    $routes->post('offres/(:num)/refuser','Direction\OffresController::refuser/$1');

    // Candidatures (D.1.2)
    $routes->get('candidatures',                            'Direction\CandidaturesController::index');
    $routes->get('candidatures/(:num)',                     'Direction\CandidaturesController::show/$1');
    $routes->post('candidatures/(:num)/retenir',            'Direction\CandidaturesController::retenir/$1');
    $routes->get('candidatures/(:num)/document/(:segment)', 'Direction\CandidaturesController::document/$1/$2');
});

$routes->group('api', ['filter' => 'role:CANDIDAT'], static function ($routes) {
    $routes->get('candidat/dashboard', 'Api\CandidatApiController::dashboard');
    $routes->get('notifications',                       'Api\CandidatApiController::notifications');
    $routes->post('notifications/(:num)/marquer-lue',   'Api\CandidatApiController::marquerLue/$1');
    $routes->post('notifications/tout-marquer-lu',      'Api\CandidatApiController::toutMarquerLu');
    $routes->get('candidat/offres',                        'Api\CandidatApiController::offres');
    $routes->get('candidat/mes-candidatures',              'Api\CandidatApiController::mesCandidatures');   // ⬅️
    $routes->get('candidat/requetes',                      'Api\CandidatApiController::requetes');          // ⬅️
    $routes->get('candidat/contrats',                      'Api\CandidatApiController::contrats');          // ⬅️
});

// -------------------------------------------------------------------------
// Espace Candidat — D / Rindra
// -------------------------------------------------------------------------
$routes->group('candidat', ['filter' => 'role:CANDIDAT'], static function ($routes) {
    // D.2.1 — Accueil + liste offres publiées
    $routes->get('/',         'Candidat\AccueilController::index');
    $routes->get('offres',    'Candidat\OffresController::index');

    // D.2.2 — Postuler (décommenter à l'étape suivante)
    $routes->get('offres/(:num)/postuler',   'Candidat\CandidaturesController::formulaire/$1');
    $routes->post('offres/(:num)/postuler',  'Candidat\CandidaturesController::postuler/$1');

    $routes->get('mes-candidatures',        'Candidat\CandidaturesController::mesCandidatures');

     // D.2.3 — Notifications
    $routes->get('notifications',                       'Candidat\NotificationsController::index');
    $routes->get('notifications/(:num)',                'Candidat\NotificationsController::show/$1');
    $routes->post('notifications/(:num)/marquer-lue',   'Candidat\NotificationsController::marquerLue/$1');
    $routes->post('notifications/tout-marquer-lu',      'Candidat\NotificationsController::toutMarquerLu');

     // D.3 — Requêtes sur conditions
    $routes->get('requetes',                             'Candidat\RequetesController::index');
    $routes->get('requetes/(:num)',                      'Candidat\RequetesController::show/$1');
    $routes->get('requetes/(:num)/creer',                'Candidat\RequetesController::formulaire/$1');
    $routes->post('requetes/(:num)/creer',               'Candidat\RequetesController::soumettre/$1');

        // D.4 — Contrat
    $routes->get('contrats',                          'Candidat\ContratController::index');
    $routes->get('contrats/(:num)',                   'Candidat\ContratController::show/$1');
    $routes->post('contrats/(:num)/accepter',         'Candidat\ContratController::accepter/$1');
    $routes->post('contrats/(:num)/refuser',          'Candidat\ContratController::refuser/$1');
    $routes->get('contrats/(:num)/pdf',               'Candidat\ContratController::telechargerPdf/$1');
// D.4.2 — Signature
$routes->post('contrats/(:num)/signer', 'Candidat\ContratController::signer/$1');
   
});

// -------------------------------------------------------------------------
// API JSON — pour les composants Vue
// -------------------------------------------------------------------------
$routes->group('api', ['filter' => 'role:DIRECTION,FINANCE'], static function ($routes) {
    $routes->get('direction/dashboard', 'Api\DirectionApiController::dashboard');
    $routes->get('direction/candidatures', 'Api\DirectionApiController::candidatures'); 
    $routes->get('direction/offres',       'Api\DirectionApiController::offres'); 
});

// -------------------------------------------------------------------------
// Admin (technique) — Danie / commun
// -------------------------------------------------------------------------
$routes->group('admin', ['filter' => 'role:ADMINISTRATEUR'], static function ($routes) {
    $routes->get('/', 'Admin\AccueilController::index');
});

// -------------------------------------------------------------------------
// Pages authentifiées partagées (tous rôles connectés)
// -------------------------------------------------------------------------
$routes->group('', ['filter' => 'auth'], static function ($routes) {
    // Ex. profil, notifications génériques — à compléter
});

// -----------------------------------------------------------------------------
$routes->get('dev/login-as/(:segment)', static function ($role) {
    $map = ['DIRECTION'=>'direction@test.local',
            'CANDIDAT'=>'candidat@test.local',
            'CANDIDAT2'   => 'candidat2@test.local',
            'CANDIDAT3'   => 'candidat3@test.local',
            'CANDIDAT4'   => 'candidat4@test.local',
            'RH'=>'rh@test.local',
            'DEPARTEMENT'=>'dept@test.local'];
    if (! isset($map[$role])) return "Rôle inconnu";
    $u = \Config\Database::connect()->table('utilisateur')
        ->where('email', $map[$role])->get()->getRowArray();
    if (! $u) return "Seed manquant — lance le SQL ci-dessous";
    session()->set(['id_utilisateur'=>$u['id_utilisateur'],
                    'email'=>$u['email'],'role'=>$u['role']]);
    return redirect()->to(match($u['role']){
        'DIRECTION'=>'/direction','CANDIDAT'=>'/candidat',
        'RH'=>'/rh','DEPARTEMENT'=>'/departement',default=>'/'
    });
});
$routes->get('dev/logout', static function () {
    session()->destroy();
    return redirect()->to('/');
});