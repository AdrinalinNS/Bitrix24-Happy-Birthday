
<?php
function detectGender($name) {
    // Проверка наличия функций mbstring
    if (function_exists('mb_strtolower')) {
        $name = mb_strtolower(trim($name), 'UTF-8');
    } else {
        $name = strtolower(trim($name));
    }

    // Список исключений
    $exceptions = [
        'саша' => 'both',
        'валя' => 'both',
        'женя' => 'both',
        'лёша' => 'male',
        'люба' => 'female',
        'юра'  => 'male',
        'аня'  => 'female',
        'оля'  => 'female',
        'илья' => 'male',
        'никита'=> 'male'
    ];

    if (isset($exceptions[$name])) {
        return $exceptions[$name];
    }

    // Базовые списки имён (нужно заполнить реальными именами)
    $femaleNames = ['анна', 'мария', 'наташа', 'олег', 'екатерина', 'марина', 'света', 'алена', 'дима', 'ира', 'таня', 'юля', 'ксюша', 'лена', 'соня', 'вика', 'катя', 'люба', 'надя', 'саша', 'женя', 'валя', 'оля', 'аня', 'юра', 'илья', 'никита'];
    $maleNames   = ['александр', 'сергей', 'дмитрий', 'андрей', 'алексей', 'евгений', 'владимир', 'павел', 'николай', 'михаил', 'артем', 'артур', 'денис', 'антон', 'игорь', 'роман', 'владислав', 'илья', 'никита', 'данил', 'данила', 'егор', 'степан', 'виктор', 'вадим', 'максим', 'константин', 'василий', 'геннадий', 'борис', 'ярослав', 'даниил', 'кирилл', 'данил', 'данила'];

    if (in_array($name, $femaleNames)) return 'female';
    if (in_array($name, $maleNames))  return 'male';

    // Правила по окончаниям
    $femaleEndings = ['а', 'я', 'ия', 'ья', 'ея'];
    $maleEndings   = ['й', 'ь', 'в', 'н', 'р', 'л', 'м', 'с', 'т', 'к', 'х'];

    // Проверка окончаний с учетом наличия mbstring
    if (function_exists('mb_substr') && function_exists('mb_strlen')) {
        foreach ($femaleEndings as $ending) {
            if (mb_substr($name, -mb_strlen($ending, 'UTF-8')) === $ending) {
                return 'female';
            }
        }

        foreach ($maleEndings as $ending) {
            if (mb_substr($name, -mb_strlen($ending, 'UTF-8')) === $ending) {
                return 'male';
            }
        }
    } else {
        // Альтернативная реализация без mbstring
        foreach ($femaleEndings as $ending) {
            if (substr($name, -strlen($ending)) === $ending) {
                return 'female';
            }
        }

        foreach ($maleEndings as $ending) {
            if (substr($name, -strlen($ending)) === $ending) {
                return 'male';
            }
        }
    }

    return 'unknown';
}
?>