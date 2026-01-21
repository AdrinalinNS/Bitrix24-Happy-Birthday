# Поздравление с днем Рождения в чат Битрикс24

## Краткое описание

Программа автоматически определяет сотрудников, у которых сегодня день рождения, генерирует персонализированные 
поздравления с помощью Yandex GPT и отправляет их в общий чат Битрикс24 вместе с праздничными открытками.

## Основные функции

- Автоматическое определение сотрудников с сегодняшним днем рождения
- Генерация персонализированных поздравлений с помощью Yandex GPT
- Отправка поздравлений в общий чат Bitrix24
- Отправка праздничных открыток в чат
- Логирование процесса работы

##  Основные модули PHP 8.2, используемые в проекте:

cURL - для выполнения HTTP-запросов к API Bitrix24 и Yandex GPT:
    Используется в user_functions.php, greeting_functions.php, disk_functions.php
    Функции: curl_init(), curl_setopt(), curl_exec(), curl_close()
    
JSON - для обработки JSON-данных:
    Используется во всех файлах для работы с API
    Функции: json_decode(), json_encode()
    
Multibyte String (mbstring) - для работы с UTF-8 строками:
    Используется в helpers.php для определения пола по имени
    Функции: mb_strtolower(), mb_substr(), mb_strlen()
    
File System - для работы с файлами:
    Используется в disk_functions.php
    Функции: file_get_contents(), file_exists()
    
Base64 - для кодирования содержимого файлов:
    Используется в disk_functions.php
    Функции: base64_encode()
    
HTTP Functions - для выполнения HTTP-запросов:
    Используется в message_functions.php, chat_functions.php
    Функции: file_get_contents() с контекстом потока
    
Date/Time - для работы с датами:
    Используется в main.php, birthday_functions.php
    Функции: date(), strtotime()
    
Error Handling - для логирования ошибок:
    Используется во всех файлах
    Функции: error_log(), error_get_last()
    
Внешние зависимости:
    Bitrix24 REST API - для работы с пользователями и чатами
    Yandex GPT API - для генерации персонализированных поздравлений

## Отредактируйте php.ini для корректного времени 

пример

date.timezone = "Asia/Vladivostok"


## Структура проекта

```
main.php
├── bitrix24_config.php
├── yandex_gpt_config.php
├── birthday_cards.php
├── chat_functions.php
├── helpers.php
├── disk_functions.php
│   └── bitrix24_config.php
├── user_functions.php
│   └── bitrix24_config.php
├── birthday_functions.php
│   ├── birthday_cards.php
│   └── helpers.php
├── check_birthdays.php
│   └── chat_functions.php
├── greeting_functions.php
│   └── yandex_gpt_config.php
├── message_functions.php
│   └── bitrix24_config.php
└── card_functions.php
    ├── disk_functions.php
    │   └── bitrix24_config.php
    └── chat_functions.php
```

## Описание файлов

1. **main.php** - Главный файл программы, координирует работу всех компонентов
2. **bitrix24_config.php** - Содержит настройки подключения к Bitrix24 (вебхук, ID чатов)
3. **yandex_gpt_config.php** - Содержит настройки для работы с Yandex GPT API
4. **birthday_cards.php** - Содержит массив ссылок на праздничные открытки, сгруппированных по полу
5. **chat_functions.php** - Функции для отправки сообщений в чаты Bitrix24 и логирования
6. **helpers.php** - Вспомогательные функции, включая определение пола по имени
7. **disk_functions.php** - Функции для работы с файловым хранилищем Bitrix24 (загрузка и отправка файлов)
8. **user_functions.php** - Функции для получения списка пользователей из Bitrix24
9. **birthday_functions.php** - Функции для определения именинников среди сотрудников
10. **check_birthdays.php** - Функции для проверки наличия именинников и логирования
11. **greeting_functions.php** - Функции для генерации персонализированных поздравлений через Yandex GPT
12. **message_functions.php** - Функции для отправки текстовых сообщений в общий чат
13. **card_functions.php** - Функции для загрузки и отправки праздничных открыток в чат

## Рабочий процесс

1. Программа запускается и отправляет уведомление о начале работы в лог-чат
2. Получает список всех пользователей из Bitrix24
3. Определяет сотрудников, у которых сегодня день рождения
4. Генерирует персонализированные поздравления через Yandex GPT
5. Отправляет текстовые поздравления в общий чат
6. Загружает и отправляет праздничные открытки в чат
7. Отправляет уведомление об окончании работы в лог-чат

## Требуемые настройки

Убедитесь что вебхук имеет достаточно прав.
Создание вебхука - <Разработчикам> - внизу списка "Другое" - входящий вебхук. Включем права user, user.userfields, disk, im.
Изменение - <Разработчикам> - <Интеграции> - выбираем вебхук, редактируем. (три полоски у вебхука слева).

Для корректной работы программы необходимо настроить следующие конфигурационные файлы:

### bitrix24_config.php
```php
<?php
$bitrixWebhook = 'https://your-domain.bitrix24.ru/rest/1/token/';
$generalChatId = _; 
$chatMessageId = '__';
$logChatId = '__';
$chatFileId = $generalChatId; 
?>
```

### yandex_gpt_config.php
```php
<?php
$iamToken = 'your_yandex_gpt_api_key';
$yandexGptFolderId = 'your_yandex_cloud_folder_id';
?>
```

### Описание параметров bitrix24_config.php

- `$bitrixWebhook` - URL вебхука для доступа к Bitrix24 API
- `$generalChatId` - ID общего чата без префикса "chat", используется для отправки файлов в чат
- `$chatMessageId` - ID общего чата с префиксом "chat" и в одинарных ковычках, используется для отправки текстовых сообщений
- `$logChatId` - ID чата для логов с префиксом "chat" и в одинарных ковычках
- `$chatFileId` - ID чата для отправки файлов (по умолчанию равен $generalChatId)
