<?php
require_once 'bitrix24_config.php';

// Класс для работы с Bitrix24, формирует корректный URL запроса
class Bitrix24Client {
    private $webhook;

    public function __construct($webhook) {
        $this->webhook = $webhook;
    }

    public function call($method, $params = []) {
        $url = $this->webhook . $method . '.json';
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
        curl_close($ch);
        
        return json_decode($response, true);
    }
}

// Функция для получения всех пользователей постранично
function getAllUsers($b24Service) {
    $start = 0;
    $allUsers = [];
    $pageSize = 50;
    
    do {
        try {
            // Формируем параметры запроса
            $response = $b24Service->call(
                'user.get',
                [
                    'FILTER' => ['ACTIVE' => 'Y'],
                    'SORT' => 'ID',
                    'ORDER' => 'asc',
                    'start' => $start,
                    'select' => ['ID', 'NAME', 'LAST_NAME', 'PERSONAL_BIRTHDAY', 'WORK_POSITION', 'ACTIVE']
                ]
            );
            
            // Проверяем наличие ошибок
            if (isset($response['error'])) {
                error_log('Ошибка при получении пользователей: ' . $response['error']);
                break;
            }
            
            // Получаем данные
            $users = $response['result'] ?? [];
            
            // Добавляем в общий массив
            $allUsers = array_merge($allUsers, $users);
            
            // Обновляем позицию
            $start += $pageSize;
            
        } catch (Throwable $e) {
            error_log('Произошла ошибка: ' . $e->getMessage());
            break;
        }
        
    } while (count($allUsers) < ($start + $pageSize) && isset($response['total']));
    
    return $allUsers;
}