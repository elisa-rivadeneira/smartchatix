<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MessengerController extends Controller
{
    // Método para la verificación inicial del webhook
    public function verifyWebhook(Request $request)
    {
        Log::info('Mensaje:::: recibido en Verify WebHook', $request->all());

        $verifyToken = env('MESSENGER_VERIFY_TOKEN'); // Coloca tu token en el .env

        if ($request->get('hub_verify_token') === $verifyToken) {
            return response($request->get('hub_challenge'), 200);
        }

        return response('Invalid verify token', 403);
    }

    // Método para manejar mensajes entrantes
    public function handleWebhook(Request $request)
    {
        $data = $request->all();

        if (isset($data['entry'])) {
            foreach ($data['entry'] as $entry) {
                if (isset($entry['messaging'])) {
                    foreach ($entry['messaging'] as $messageEvent) {
                        $senderId = $messageEvent['sender']['id'];
                        if (isset($messageEvent['message'])) {
                            $messageText = $messageEvent['message']['text'];

                            // Aquí puedes conectar tu lógica de agente con OpenAI
                            $responseText = $this->processAgentMessage($messageText);

                            $this->sendMessageToUser($senderId, $responseText);
                        }
                    }
                }
            }
        }

        return response('EVENT_RECEIVED', 200);
    }

    private function processAgentMessage($message)
    {
        // Aquí puedes conectar con OpenAI o cualquier otra API de tu agente
        return "Recibí tu mensaje: " . $message;
    }

    private function sendMessageToUser($recipientId, $messageText)
    {
        $pageAccessToken = env('MESSENGER_PAGE_ACCESS_TOKEN'); // Coloca tu token en el .env

        $url = 'https://graph.facebook.com/v12.0/me/messages?access_token=' . $pageAccessToken;

        $response = [
            'recipient' => ['id' => $recipientId],
            'message' => ['text' => $messageText],
        ];

        $client = new \GuzzleHttp\Client();
        $client->post($url, [
            'json' => $response,
        ]);
    }

    public function handleMessenger(Request $request)
{

//    dd($request->all()); // Para ver la solicitud completa

    Log::info('Mensaje recibido', $request->all());
    $data = $request->all();

    // Procesa los eventos de Messenger (ejemplo: recibir mensajes de usuarios)
    if (isset($data['entry'])) {
        foreach ($data['entry'] as $entry) {
            foreach ($entry['messaging'] as $event) {
                if (isset($event['message'])) {
                    $senderId = $event['sender']['id'];
                    $message = $event['message']['text'];

                    // Aquí puedes enviar una respuesta usando tu agente de IA
                    $this->sendMessageToUser($senderId, 'Gracias por tu mensaje: ' . $message);
                }
            }
        }
    }

    return response('EVENT_RECEIVED', 200);
}
}