<?php
require_once 'disk_functions.php';
require_once 'chat_functions.php';

// Загружаем и отправляем открытки в чат
function sendCardsToChat($personsForCards) {
    global $chatFileId;
    
    // Проверяем, определена ли переменная chatFileId и не пустая ли она
    if (!isset($chatFileId) || empty($chatFileId)) {
        error_log("Ошибка: Не определен ID чата для файлов (chatFileId)");
        logMessage(date('Y-m-d H:i:s') . " - Ошибка: Не определен ID чата для файлов (chatFileId)");
        return;
    }
    
    foreach ($personsForCards as $person) {
        try {
            // Получаем ID папки чата
            error_log("Отладка: Перед вызовом getChatFolderId, chatFileId = " . var_export($chatFileId, true));
            error_log("Отладка: Тип переменной chatFileId = " . gettype($chatFileId));
            $folderResult = getChatFolderId($chatFileId);
            if ($folderResult === false) {
                logMessage(date('Y-m-d H:i:s') . " - Не удалось получить ID папки чата для {$person['FULL_NAME']}. Проверьте права доступа к чату и корректность ID чата в конфигурации.");
                continue;
            }
            
            $folderId = $folderResult;
            
            // Загружаем открытку в папку чата
            $uploadResult = uploadFileToFolder($folderId, $person['CARD_URL']);
            if ($uploadResult === false) {
                logMessage(date('Y-m-d H:i:s') . " - Не удалось загрузить открытку для {$person['FULL_NAME']}");
                continue;
            }
            
            $uploadId = $uploadResult['ID'];
            
            // Отправляем загруженный файл в чат
            $commitResult = commitFileToChat($chatFileId, $uploadId, "Поздравляем с днём рождения, {$person['FULL_NAME']}!");
            if ($commitResult === false) {
                logMessage(date('Y-m-d H:i:s') . " - Не удалось отправить открытку для {$person['FULL_NAME']}");
                continue;
            }
            
            logMessage(date('Y-m-d H:i:s') . " - Открытка успешно отправлена для {$person['FULL_NAME']}");
        } catch (Throwable $e) {
            error_log('Ошибка при обработке открытки для ' . $person['FULL_NAME'] . ': ' . $e->getMessage());
            logMessage(date('Y-m-d H:i:s') . " - Ошибка при обработке открытки для {$person['FULL_NAME']}: " . $e->getMessage());
        }
    }
}