<?php
require_once 'bitrix24_config.php';

// Отправляем все текстовые сообщения в общий чат
function sendMessagesToChat($messages) {
    global $bitrixWebhook, $chatMessageId;
    
    foreach ($messages as $message) {
        $params = [
            'DIALOG_ID' => $chatMessageId,
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
}