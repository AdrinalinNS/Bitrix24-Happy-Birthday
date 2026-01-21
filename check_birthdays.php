<?php
require_once 'chat_functions.php';

// Проверяем, есть ли именинники
function checkBirthdays($birthdaysToday) {
    if (!empty($birthdaysToday)) {
        $names = array_column($birthdaysToday, 'FULL_NAME');
        $count = count($names);
        
        $logMessages = date('Y-m-d H:i:s') . ' - Сегодня день рождения у ' . $count . ' человек: ' . implode(", ", $names);
        logMessage($logMessages);
        
        return true;
    } else {
        // Если именинников нет
        logMessage(date('Y-m-d H:i:s') . ' - Сегодня нет именинников');
        error_log(date('Y-m-d H:i:s') . ' - Сегодня нет именинников');
        
        return false;
    }
}