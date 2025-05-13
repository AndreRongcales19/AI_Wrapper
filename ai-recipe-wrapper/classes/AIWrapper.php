<?php
class AIWrapper {
    private $ingredients = [];
    private $response = '';

    public function __construct() {
        // Controleer of config beschikbaar is
        if (!defined('API_KEY')) {
            require_once __DIR__ . '/../config/config.php';
        }
    }

    public function processInput($ingredients) {
        if (empty($ingredients)) {
            throw new Exception("Geen ingredienten opgegeven");
        }

        $this->ingredients = $ingredients;
        // Later hier API aanroepen
        return true;
    }

    public function callOpenAI($prompt, $apiKey, $model) {
        $url = 'https://api.openai.com/v1/chat/completions';
        $headers = [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $apiKey
        ];
        $data = [
            'model' => $model,
            'messages' => [['role' => 'system', 'content' => 'Je bent een behulpzame assistant.'],
                ['role' => 'user', 'content' => $prompt]]
        ];

        // API-verzoek versturen met cURL
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $response = curl_exec($ch);
        curl_close($ch);

        return json_decode($response, true);
    }

    public function getResponse() {
        // Voorlopig een standaard bericht teruggeven
        $ingredientsList = implode(', ', $this->ingredients);
        $this->response = "Recept met $ingredientsList wordt verwerkt";
        return $this->response;
    }
}
?>