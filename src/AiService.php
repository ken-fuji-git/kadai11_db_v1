<?php
require_once __DIR__ . '/Env.php';
Env::loadForHost(__DIR__ . '/..');

class AiService
{
    private $apiKey;
    private $model;
    private $apiUrl = 'https://api.openai.com/v1/chat/completions';

    public function __construct()
    {
        $this->apiKey = Env::get('AI_API_KEY');
        $this->model = Env::get('OPENAI_MODEL', 'gpt-4o-mini');
    }

    public function generateChat($diaryContent)
    {
        // Check if API key is not set or default
        if (!$this->apiKey || $this->apiKey === 'your_api_key_here') {
            return $this->getMockResponse($diaryContent);
        }

        // Define Penguin Personalities
        $systemPrompt = "
        あなたは「肯定するペンギンたちの会議」の脚本家です。
        ユーザーの日記（今日がんばったこと）に対して、10羽のペンギンが会議をし、ユーザーを褒め、励まし、愛着を感じさせる会話を作成してください。
        
        【キャラクター設定】
        1. ペンギン長老: 
           - 物知りだが、少し抜けている。おとぼけキャラ。
           - 語尾は「〜じゃ」「〜のう」。
           - ユーザーの行動を深く（時には深読みしすぎて）解釈する。
        
        2. わんぱくペンギン: 
           - 元気いっぱい。単純。
           - 語尾は「〜だッ！」「〜だね！」。
           - とにかく褒める。すごいすごいと言う。
        
        3. インテリペンギン:
           - 冷静で分析的。メガネをかけている（設定）。
           - 語尾は「〜ですね」「〜と思われます」。
           - 具体的な行動を褒める。

        上記を参考に、残りの7羽のキャラクターを補完して会話を補ってください。

        【シナリオ構成】
        - 10羽がわらわらと集まり、ユーザーの日記について議論する。
        - 議論はすべて肯定的（ポジティブ）。
        - ユーザーが「失敗した」と書いても、「挑戦が素晴らしい」と変換する。
        - 全体で6〜10ターン程度の会話。
        - JSON形式で出力すること。

        【出力フォーマット (JSON)】
        [
            {\"speaker\": \"ペンギン長老\", \"message\": \"...\"},
            {\"speaker\": \"わんぱくペンギン\", \"message\": \"...\"},
            ...
        ]
        ";

        $data = [
            'model' => $this->model,
            'messages' => [
                ['role' => 'system', 'content' => $systemPrompt],
                ['role' => 'user', 'content' => $diaryContent]
            ],
            'temperature' => 0.7,
            // 'response_format' => ['type' => 'json_object'] // Use this if moving to GPT-4o or latest 3.5 turbo
        ];

        $ch = curl_init($this->apiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->apiKey
        ]);

        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            // Fallback to mock on connection error
            curl_close($ch);
            return $this->getMockResponse($diaryContent . " (Connection Error)");
        }

        curl_close($ch);

        $result = json_decode($response, true);

        // Check for OpenAI Error
        if (isset($result['error'])) {
            return $this->getMockResponse($diaryContent . " (API Error: " . $result['error']['message'] . ")");
        }

        if (isset($result['choices'][0]['message']['content'])) {
            $content = $result['choices'][0]['message']['content'];
            // Basic cleanup to ensure JSON
            $jsonStart = strpos($content, '[');
            $jsonEnd = strrpos($content, ']');
            if ($jsonStart !== false && $jsonEnd !== false) {
                $content = substr($content, $jsonStart, $jsonEnd - $jsonStart + 1);
                $parsed = json_decode($content, true);
                if ($parsed) {
                    // Add sequence numbers
                    foreach ($parsed as $i => &$msg) {
                        $msg['sequence'] = $i + 1;
                    }
                    return $parsed;
                }
            }
        }

        // Fallback if parsing fails
        return $this->getMockResponse($diaryContent . " (AI Parsing Error)");
    }

    private function getMockResponse($diaryContent)
    {
        return [
            [
                'speaker' => 'ペンギン長老',
                'message' => '（モック）ほう…今日は「' . mb_substr($diaryContent, 0, 10) . '...」などをがんばったのか。APIキーを設定するともっと賢くなるぞ。',
                'sequence' => 1
            ],
            [
                'speaker' => 'わんぱくペンギン',
                'message' => 'すごい！すごいよ！がんばったね！',
                'sequence' => 2
            ],
            [
                'speaker' => 'インテリペンギン',
                'message' => '継続は力なり、ですね。素晴らしい進捗です。',
                'sequence' => 3
            ],
            [
                'speaker' => 'ペンギン長老',
                'message' => 'ゆっくり休むのも仕事のうちじゃよ。',
                'sequence' => 4
            ]
        ];
    }
}
