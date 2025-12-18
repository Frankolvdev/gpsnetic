<?php

namespace App\Controllers;

use CodeIgniter\Controller;

class Webhook extends Controller
{
    public function index()
    {
        // Captura todos los parámetros que vengan por GET
        $data = $this->request->getGet();

        log_message('info', 'Webhook GET recibido correctamente: ' . json_encode($data));

        return $this->response->setJSON([
            'status' => 'ok',
            'received' => $data
        ]);
    }

    // ─────────────────────────────────────────────
    // 🔔 ENVÍO DE PUSH NOTIFICATION (FCM v1)
    // ─────────────────────────────────────────────
    public function testFcm()
    {
         $projectId = 'gpsnetic-19c12';
        $accessToken = $this->getFirebaseAccessToken();

        $fcmToken = "cC7960sVSYaZCa7FT4Drh2:APA91bEztwquRkv35PRhef6JT1JRwzsBf6i6VqhM4ioUBlNe_4i9mYMqNAVtMhcf3d9eqcpnlrUobACEIXxz0v13Zcmho2ZBr5SjJjZ3uQHyg95IDaVgfSA";
        $title    = "test ttle";
        $body     = "test body";

        if (!$fcmToken) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'FCM token requerido'
            ])->setStatusCode(400);
        }

   

        if (!$accessToken) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'No se pudo obtener access token'
            ])->setStatusCode(500);
        }

       

        $url = "https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send";

        $payload = [
            'message' => [
                'token' => $fcmToken,
                'notification' => [
                    'title' => $title,
                    'body'  => $body,
                ],
                'android' => [
                    'notification' => [
                        'channel_id' => 'default_channel'
                    ]
                ]
            ]
        ];

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $accessToken,
                'Content-Type: application/json'
            ],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POSTFIELDS => json_encode($payload),
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return $this->response->setJSON([
            'status' => $httpCode === 200 ? 'ok' : 'error',
            'firebase_response' => json_decode($response, true)
        ]);
    }

    // ─────────────────────────────────────────────
    // 🔐 OBTENER ACCESS TOKEN (OAuth2)
    // ─────────────────────────────────────────────
    private function getFirebaseAccessToken()
    {
        $jsonKeyPath = WRITEPATH . 'firebase/service-account.json';

        if (!file_exists($jsonKeyPath)) {
            log_message('error', 'Service Account JSON no encontrado');
            return null;
        }

        $jsonKey = json_decode(file_get_contents($jsonKeyPath), true);

        $jwtHeader = base64_encode(json_encode([
            'alg' => 'RS256',
            'typ' => 'JWT'
        ]));

        $now = time();
        $jwtClaim = base64_encode(json_encode([
            'iss'   => $jsonKey['client_email'],
            'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
            'aud'   => 'https://oauth2.googleapis.com/token',
            'iat'   => $now,
            'exp'   => $now + 3600
        ]));

        $signatureInput = $jwtHeader . '.' . $jwtClaim;

        openssl_sign(
            $signatureInput,
            $signature,
            $jsonKey['private_key'],
            'SHA256'
        );

        $jwt = $signatureInput . '.' . base64_encode($signature);

        $ch = curl_init('https://oauth2.googleapis.com/token');
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => ['Content-Type: application/x-www-form-urlencoded'],
            CURLOPT_POSTFIELDS => http_build_query([
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                'assertion'  => $jwt
            ])
        ]);

        $result = curl_exec($ch);
        curl_close($ch);

        $response = json_decode($result, true);

        return $response['access_token'] ?? null;
    }
}
