<?php

namespace App\Controllers;

use CodeIgniter\Controller;

class Webhook extends Controller
{
    public function index()
    {
        // Captura todos los parámetros que vengan por GET
        $data = $this->request->getGet();

        // (Opcional) Guarda en logs para ver qué datos llegan
        log_message('info', 'Webhook GET recibido correctamente: ' . json_encode($data));

        // Si quieres devolver una respuesta simple
        return $this->response->setJSON([
            'status' => 'ok',
            'received' => $data
        ]);
    }
}
