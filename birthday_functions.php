<?php
require_once 'birthday_cards.php';
require_once '/root/helpers.php'; // функция определения пола

// Собираем всех именинников (с именем и фамилией)
function collectBirthdays($users, $today) {
    $birthdaysToday = [];
    
    foreach ($users as $user) {
        $birthday = $user['PERSONAL_BIRTHDAY'] ?? '';
        if (empty($birthday)) continue;
        
        // Берём только YYYY-MM-DD
        $datePart = substr($birthday, 0, 10);
        // Преобразуем в m-d
        $birthdayDate = date('m-d', strtotime($datePart));
        // Сравниваем дату рождения с сегодняшним днём
        if ($birthdayDate === $today) {
            // Формируем полное имя
            $fullName = trim($user['LAST_NAME'] . ' ' . ($user['NAME'] ?? ''));
            if (empty($fullName)) {
                $fullName = 'Уважаемый коллега'; // запасной вариант
            }
            //Получаем должность
            $position = $user['WORK_POSITION'] ?? '';
            // Определяем пол
            $gender = detectGender($user['NAME']); // ваша функция определения пола

            // Выбираем открытку
            switch ($gender) {
                case 'male':
                    $availableCards = $GLOBALS['cardsByGender']['male'];
                    break;
                case 'female':
                    $availableCards = $GLOBALS['cardsByGender']['female'];
                    break;
                case 'both':
                    $availableCards = $GLOBALS['cardsByGender']['both'];
                    break;
                default:
                    $availableCards = $GLOBALS['cardsByGender']['unknown'];
                    break;
            }

            $cardUrl = $availableCards[array_rand($availableCards)];

            // Заполняем массив именинников
            $birthdaysToday[] = [
                'ID' => $user['ID'],
                'FULL_NAME' => $fullName,
                'FIRST_NAME' => $user['NAME'],
                'LAST_NAME' => $user['LAST_NAME'],
                'GENDER' => $gender,
                'BIRTHDAY_DATE' => $birthdayDate,
                'POSITION' => $position,
                'CARD_URL' => $cardUrl // сохраняем URL открытки для каждого именинника
            ];
        }
    }
    
    return $birthdaysToday;
}