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
        
        // Create the prompt for the recipe
        $ingredientsList = implode(', ', $ingredients);
        $prompt = "Geef een recept met de volgende ingrediënten: $ingredientsList. " .
                 "Geef het recept in het volgende format:\n" .
                 "1. Titel van het gerecht\n" .
                 "2. Benodigde ingrediënten (inclusief hoeveelheden)\n" .
                 "3. Bereidingswijze in stappen\n" .
                 "4. Tips en variaties (indien van toepassing)";

        // Call OpenAI API
        $response = $this->callOpenAI($prompt, API_KEY, MODEL);
        
        if (isset($response['choices'][0]['message']['content'])) {
            $this->response = $response['choices'][0]['message']['content'];
        } else {
            throw new Exception("Kon geen recept genereren. Probeer het later opnieuw.");
        }

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
            'messages' => [
                ['role' => 'system', 'content' => 'Je bent een ervaren chef-kok die creatieve en lekkere recepten maakt.'],
                ['role' => 'user', 'content' => $prompt]
            ],
            'temperature' => 0.7,
            'max_tokens' => 1000
        ];

        // API-verzoek versturen met cURL
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);

        $response = curl_exec($ch);
        
        if (curl_errno($ch)) {
            throw new Exception('API Error: ' . curl_error($ch));
        }
        
        curl_close($ch);

        $result = json_decode($response, true);
        
        if (isset($result['error'])) {
            throw new Exception('API Error: ' . $result['error']['message']);
        }

        return $result;
    }

    public function getResponse() {
        return $this->response;
    }
}
?>