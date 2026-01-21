<?php
// Настройки Bitrix24
$bitrixWebhook = 'https://your-domain.bitrix24.ru/rest/1/token/';  // замените на ссылку на вебхук
// необходимы права на чтение пользователей, их полей, чата, диска

// выбор чатов для отправки
$generalChatId = 2; // укажите ID вашего общего чата без префикса chat, нужно для отправки файла в чат
$chatMessageId = 'chat2'; // укажите ID вашего общего чата с префиксом chat
$logChatId = 'chat6946'; // укажите ID чата для логов с префиксом chat
$chatFileId = $generalChatId; 
// примеры чатов
// chat2 - общий, chat6946 - информация и логи в чат