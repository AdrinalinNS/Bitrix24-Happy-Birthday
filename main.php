<?php
require_once 'bitrix24_config.php';
require_once 'yandex_gpt_config.php';
require_once 'birthday_cards.php';
require_once 'chat_functions.php';
require_once 'helpers.php';
require_once 'disk_functions.php';
require_once 'user_functions.php';
require_once 'birthday_functions.php';
require_once 'check_birthdays.php';
require_once 'greeting_functions.php';
require_once 'message_functions.php';
require_once 'card_functions.php';

// Уведомление о старте скрипта в лог-чат
logMessage(date('Y-m-d H:i:s') . ' - Ищу именинников');

$today = date('m-d'); // какая сегодня дата м-д

// Получаем всех пользователей
$b24Service = new Bitrix24Client($bitrixWebhook);
$users = getAllUsers($b24Service);

if (empty($users)) {
    error_log('Не удалось получить список пользователей');
    logMessage(date('Y-m-d H:i:s') . ' - Не удалось получить список пользователей');
    exit;
}

// Собираем всех именинников (с именем и фамилией)
$birthdaysToday = collectBirthdays($users, $today);

// Проверяем, есть ли именинники
if (!checkBirthdays($birthdaysToday)) {
    exit;
}

// Формируем индивидуальные поздравления через Yandex GPT для каждого именинника
$greetingsData = generateGreetings($birthdaysToday);
$messages = $greetingsData['messages'];
$personsForCards = $greetingsData['personsForCards'];

// Отправляем все текстовые сообщения в общий чат
sendMessagesToChat($messages);

// Загружаем и отправляем открытки в чат
sendCardsToChat($personsForCards);

// Уведомление об окончании работы в лог-чат
logMessage(date('Y-m-d H:i:s') . ' - Работа завершена');