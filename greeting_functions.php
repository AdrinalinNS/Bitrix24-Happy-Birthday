<?php
require_once 'yandex_gpt_config.php';

// Формируем индивидуальные поздравления через Yandex GPT для каждого именинника
function generateGreetings($birthdaysToday) {
    global $yandexApiUrl, $iamToken, $folderId;
    
    $messages = [];
    $personsForCards = [];
    
    foreach ($birthdaysToday as $person) {
        $prompt = trim(
            "Напиши тёплое и официальное персональное поздравление с днём рождения для сотрудника указав полное Фамилию и Имя {$person['FULL_NAME']}. " .
            "Начни с фразы: Сегодня празднует день рождения {$person['FULL_NAME']} {$person['POSITION']}. Следующий текст идет с новой строки" .
            "Стиль: дружелюбный, но деловой. Длина: 3–5 предложений. ".
            "Упомяни по полному фамилии и имени, обязательно укажи должность" .
            "Пожелай профессиональных достижений, личного счастья и исполнения мечт. " .
            "В конце используй фразу: С уважением и наилучшими пожеланиями, ваши коллеги из группы компаний Тринити!"
        );

        $yandexRequest = [
            'modelUri' => "gpt://{$folderId}/yandexgpt-lite/latest",
            'completionOptions' => [
                'temperature' => 0.7,
                'maxTokens' => 1000
            ],
            'messages' => [
                ['role' => 'user', 'text' => $prompt]
            ]
        ];

        // Отправляем запрос в Yandex GPT
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $yandexApiUrl);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $iamToken,
            'Content-Type: application/json'
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($yandexRequest));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // для тестов; в проде включите проверку

        $yandexResponse = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        // Обрабатываем ответ GPT
        if ($httpCode === 200) {
            $generation = json_decode($yandexResponse, true);
            if (!empty($generation['result']['alternatives'][0]['message']['text'])) {
                $greeting = preg_replace(
                    '/[\(\[\{].*?[\)\]\}]/u',
                    '',
                    $generation['result']['alternatives'][0]['message']['text']
                );
                $greeting = trim($greeting);
            } else {
                $greeting = null;
            }
        } else {
            error_log("Ошибка API Yandex GPT: HTTP $httpCode, ответ: $yandexResponse");
            $greeting = null;
        }

        // Резервное сообщение (если GPT не ответил)
        if (empty($greeting)) {
            $greeting = "🎉 Поздравляем с днём рождения, {$person['FULL_NAME']}!\n\n";
            $greeting .= "От всей души желаем вам крепкого здоровья, счастья, профессиональных достижений и исполнения всех заветных желаний! 🚀\n\n";
            $greeting .= "С уважением, сотрудники группы компании Тринити 🚀\n\n";
        }
        
        // Добавляем поздравление в массив сообщений и сохраняем информацию о персоне для загрузки открытки
        $messages[] = $greeting;
        $personsForCards[] = $person;
    }
    
    return ['messages' => $messages, 'personsForCards' => $personsForCards];
}