<?php
// Функция для отправки сообщений в чат
function sendChatMessage($chatId, $message) {
    global $bitrixWebhook;
    
    $params = [
        'DIALOG_ID' => $chatId,
        'MESSAGE' => $message
    ];
    
    file_get_contents(
        $bitrixWebhook . 'im.message.add.json',
        false,
        stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => 'Content-type: application/x-www-form-urlencoded',
                'content' => http_build_query($params)
            ]
        ])
    );
}

// Функция для логирования
function logMessage($message) {
    error_log($message);
    
    // Проверяем, определена ли переменная logChatId и не пустая ли она
    if (!isset($GLOBALS['logChatId']) || empty($GLOBALS['logChatId'])) {
        // Если переменная не определена или пустая, пытаемся получить значение из конфигурации
        if (isset($logChatId) && !empty($logChatId)) {
            sendChatMessage($logChatId, $message);
        } else {
            // Если не можем получить ID чата для логов, выводим сообщение в error_log и прерываем выполнение
            error_log("Ошибка: Не определен ID чата для логов (logChatId)");
            return;
        }
    } else {
        sendChatMessage($GLOBALS['logChatId'], $message);
    }
}

// Функция для отправки сообщения с обработкой ошибок
function sendChatMessageWithErrorHandling($chatId, $message) {
    global $bitrixWebhook;
    
    $params = [
        'DIALOG_ID' => $chatId,
        'MESSAGE' => $message
    ];
    
    $context = stream_context_create([
        'http' => [
            'method' => 'POST',
            'header' => 'Content-type: application/x-www-form-urlencoded',
            'content' => http_build_query($params)
        ]
    ]);
    
    $result = file_get_contents($bitrixWebhook . 'im.message.add.json', false, $context);
    
    if ($result === false) {
        error_log("Не удалось отправить сообщение в чат: " . error_get_last()['message']);
        return false;
    }
    
    $response = json_decode($result, true);
    
    if (isset($response['error'])) {
        error_log("Ошибка API Bitrix24 при отправке сообщения: " . $response['error']);
        return false;
    }
    
    return true;
}