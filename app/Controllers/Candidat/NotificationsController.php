<?php

namespace App\Controllers\Candidat;

use App\Controllers\BaseController;
use App\Models\NotificationModel;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

class NotificationsController extends BaseController
{
    protected $db;

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);
        $this->db = \Config\Database::connect();
    }

    public function index()
{
    return view('candidat/notifications/index', ['title' => 'Mes notifications']);
}
    public function show($id)
    {
        $idUtilisateur = (int) session()->get('id_utilisateur');
        $id = (int) $id;

        $model = new NotificationModel();
        $notif = $model->trouverPour($id, $idUtilisateur);

        if (! $notif) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound("Notification $id introuvable");
        }

        // Marque automatiquement comme lue à l'ouverture
        $model->marquerLue($id, $idUtilisateur);

        return view('candidat/notifications/show', [
            'title'  => $notif['titre'],
            'notif'  => $notif,
        ]);
    }

    public function marquerLue($id)
    {
        $idUtilisateur = (int) session()->get('id_utilisateur');
        $ok = (new NotificationModel())->marquerLue((int) $id, $idUtilisateur);

        if ($this->request->getHeaderLine('Accept') === 'application/json') {
            return $this->response->setJSON(['ok' => $ok]);
        }
        return redirect()->back()->with('success', $ok ? 'Notification marquée comme lue.' : 'Notification introuvable.');
    }

    public function toutMarquerLu()
    {
        $idUtilisateur = (int) session()->get('id_utilisateur');
        $n = (new NotificationModel())->marquerToutLue($idUtilisateur);

        if ($this->request->getHeaderLine('Accept') === 'application/json') {
            return $this->response->setJSON(['ok' => true, 'marquees' => $n]);
        }
        return redirect()->to('/candidat/notifications')
                         ->with('success', "$n notification(s) marquée(s) comme lue(s).");
    }
}