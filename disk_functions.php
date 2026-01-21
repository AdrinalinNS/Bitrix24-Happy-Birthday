<?php
require_once 'bitrix24_config.php';

/**
 * Функция для загрузки файла через disk.folder.uploadfile
 *
 * @param int $folderId ID папки для загрузки
 * @param string $filePath Путь к файлу на сервере
 * @param string $fileName Имя файла
 * @return array|false Результат загрузки или false при ошибке
 */
function uploadFileToDisk($folderId, $filePath, $fileName) {
    global $bitrixWebhook;
    
    if (!file_exists($filePath)) {
        error_log("Файл не найден: {$filePath}");
        return false;
    }
    
    // Получаем содержимое файла
    $fileContent = file_get_contents($filePath);
    if ($fileContent === false) {
        error_log("Не удалось прочитать файл: {$filePath}");
        return false;
    }
    
    // Подготавливаем параметры для загрузки
    $params = [
        'id' => $folderId,
        'data' => [
            'NAME' => $fileName
        ],
        'fileContent' => base64_encode($fileContent)
    ];
    
    // Выполняем запрос к API через cURL
    $url = $bitrixWebhook . 'disk.folder.uploadfile.json';
    $postData = http_build_query($params);
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/x-www-form-urlencoded',
        'Content-Length: ' . strlen($postData)
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($httpCode === 200 && !empty($response)) {
        $result = json_decode($response, true);
        if (isset($result['result']['ID'])) {
            return $result['result'];
        } else {
            error_log("Ошибка API Bitrix24 при загрузке файла: " . json_encode($result));
            return false;
        }
    } else {
        error_log("Ошибка cURL при загрузке файла: HTTP $httpCode, ошибка: $error, ответ: $response");
        return false;
    }
}

/**
 * Функция для отправки файла в чат через im.disk.file.commit
 *
 * @param int $chatId ID чата
 * @param int $uploadId ID загруженного файла
 * @param string $message Описание файла (опционально)
 * @return array|false Результат отправки или false при ошибке
 */
function commitFileToChat($chatId, $uploadId, $message = '') {
    global $bitrixWebhook;
    
    $params = [
        'CHAT_ID' => $chatId,
        'UPLOAD_ID' => $uploadId
    ];
    
    if (!empty($message)) {
        $params['MESSAGE'] = $message;
    }
    
    // Выполняем запрос к API через cURL
    $url = $bitrixWebhook . 'im.disk.file.commit.json';
    $postData = http_build_query($params);
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/x-www-form-urlencoded',
        'Content-Length: ' . strlen($postData)
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($httpCode === 200 && !empty($response)) {
        $result = json_decode($response, true);
        
        // Проверяем успешность операции по наличию результата
        if (isset($result['result'])) {
            // Если в результате есть MESSAGE_ID, значит операция успешна
            if (isset($result['result']['MESSAGE_ID'])) {
                return $result;
            }
            // Если результат true, значит операция успешна
            if ($result['result'] === true) {
                return $result;
            }
            // Если в результате есть FILES, значит операция успешна
            if (isset($result['result']['FILES'])) {
                return $result;
            }
        }
        
        error_log("Ошибка API Bitrix24 при отправке файла в чат: " . json_encode($result));
        return false;
    } else {
        error_log("Ошибка cURL при отправке файла в чат: HTTP $httpCode, ошибка: $error, ответ: $response");
        return false;
    }
}

/**
 * Функция для получения ID папки чата через im.disk.folder.get
 *
 * @param int $chatId ID чата
 * @return int|false ID папки или false при ошибке
 */
function getChatFolderId($chatId) {
    global $bitrixWebhook;
    
    // Проверка, что chatId не пустой
    if (empty($chatId)) {
        error_log("Ошибка: Пустой идентификатор чата (CHAT_ID_EMPTY)");
        logMessage(date('Y-m-d H:i:s') . " - Ошибка: Пустой идентификатор чата (CHAT_ID_EMPTY)");
        return false;
    }
    
    // Дополнительная проверка на тип и валидность chatId
    if (!is_string($chatId) && !is_numeric($chatId)) {
        error_log("Ошибка: Неверный тип идентификатора чата (CHAT_ID_INVALID_TYPE)");
        logMessage(date('Y-m-d H:i:s') . " - Ошибка: Неверный тип идентификатора чата (CHAT_ID_INVALID_TYPE)");
        return false;
    }
    
    // Преобразуем chatId в строку, если это число
    $chatId = (string)$chatId;
    
    // Добавляем отладочную информацию
    error_log("Отладка: chatId после преобразования = " . var_export($chatId, true));
    
    // Подготовка параметров для запроса
    error_log("Отладка: Перед вызовом API, chatId = " . var_export($chatId, true));
    $params = [
        'CHAT_ID' => $chatId
    ];
    
    // Выполняем запрос к API через cURL
    $url = $bitrixWebhook . 'im.disk.folder.get.json';
    $postData = http_build_query($params);
    error_log("Отладка: URL запроса = " . $url);
    error_log("Отладка: POST данные = " . $postData);
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/x-www-form-urlencoded',
        'Content-Length: ' . strlen($postData)
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    // Детальное логирование для диагностики ошибок доступа
    if ($httpCode === 200 && !empty($response)) {
        $result = json_decode($response, true);
        if (isset($result['result']['ID'])) {
            return $result['result']['ID'];
        } elseif (isset($result['error'])) {
            // Обработка ошибок API Bitrix24
            error_log("Ошибка API Bitrix24 при получении ID папки чата: код={$result['error']}, описание={$result['error_description']}");
            logMessage(date('Y-m-d H:i:s') . " - Ошибка API Bitrix24 при получении ID папки чата: код={$result['error']}, описание={$result['error_description']}");
            return false;
        } else {
            error_log("Не удалось получить ID папки чата: " . json_encode($result));
            logMessage(date('Y-m-d H:i:s') . " - Не удалось получить ID папки чата: " . json_encode($result));
            return false;
        }
    } else {
        error_log("Ошибка cURL при получении ID папки чата: HTTP $httpCode, ошибка: $error, ответ: $response");
        logMessage(date('Y-m-d H:i:s') . " - Ошибка cURL при получении ID папки чата: HTTP $httpCode, ошибка: $error, ответ: $response");
        return false;
    }
}

/**
 * Функция для загрузки файла по URL в папку через disk.folder.uploadfile
 *
 * @param int $folderId ID папки для загрузки
 * @param string $fileUrl URL файла для загрузки
 * @param string $fileName Имя файла (опционально)
 * @return array|false Результат загрузки или false при ошибке
 */
function uploadFileToFolder($folderId, $fileUrl, $fileName = null) {
    global $bitrixWebhook;
    
    // Получаем содержимое файла по URL
    $fileContent = @file_get_contents($fileUrl);
    if ($fileContent === false) {
        error_log("Не удалось загрузить файл по URL: {$fileUrl}");
        return false;
    }
    
    // Если имя файла не задано, извлекаем его из URL
    if (empty($fileName)) {
        $fileName = basename(parse_url($fileUrl, PHP_URL_PATH));
        // Убедимся, что имя файла не пустое
        if (empty($fileName)) {
            $fileName = 'open_card_' . time() . '.jpg';
        }
    }
    
    // Подготовка параметров для загрузки через cURL
    $params = [
        'id' => $folderId,
        'data' => [
            'NAME' => $fileName
        ],
        'fileContent' => base64_encode($fileContent)
    ];
    
    $url = $bitrixWebhook . 'disk.folder.uploadfile.json';
    $postData = http_build_query($params);
    
    // Выполнение запроса через cURL
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/x-www-form-urlencoded',
        'Content-Length: ' . strlen($postData)
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($httpCode === 200 && !empty($response)) {
        $result = json_decode($response, true);
        if (isset($result['result']['ID'])) {
            return $result['result'];
        } else {
            error_log("Ошибка при загрузке файла в папку: " . json_encode($result));
            return false;
        }
    } else {
        error_log("Ошибка cURL при загрузке файла: HTTP $httpCode, ошибка: $error, ответ: $response");
        return false;
    }
}