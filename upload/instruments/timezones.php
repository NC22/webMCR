<?php
/*
date_default_timezone_set('Asia/Vladivostok');

$now = new \DateTime();
$mins = $now->getOffset() / 60;
$sgn = ($mins < 0 ? -1 : 1);
$mins = abs($mins);
$hrs = floor($mins / 60);
$mins -= $hrs * 60;
$offset = sprintf('%+d:%02d', $hrs*$sgn, $mins);
echo $offset; 

echo '<select>'.TimezoneList().'</select>';
*/

/* WEB-APP : WebMCR (С) 2013 NC22 | License : GPLv3 */

if (!defined('MCR')) exit;

$timezones = array (

	'(GMT-12:00) Линия перемены даты' => 'Pacific/Wake',
	'(GMT-11:00) Остров Мидуэй, Самоа' => 'Pacific/Apia',
	'(GMT-10:00) Гавайи' => 'Pacific/Honolulu',
	'(GMT-09:00) Аляска' => 'America/Anchorage',
	'(GMT-08:00) Тихоокеанское время (США и Канада), Тихуана' => 'America/Los_Angeles',
	'(GMT-07:00) Горное время (США и Канада), Аризона' => 'America/Phoenix',
	'(GMT-07:00) Чиуауа' => 'America/Chihuahua',
	'(GMT-07:00) Ла-Пас' => 'America/Chihuahua',
	'(GMT-07:00) Масатлан' => 'America/Chihuahua',
	'(GMT-07:00) Горное время (США и Канада)' => 'America/Denver',
	'(GMT-06:00) Центральная Америка' => 'America/Managua',
	'(GMT-06:00) Центральное время (США и Канада)'	=> 'America/Chicago',
	'(GMT-06:00) Гвадалахара' => 'America/Mexico_City',
	'(GMT-06:00) Центральное время (США и Канада), Мехико' => 'America/Mexico_City',
	'(GMT-06:00) Монтеррей' => 'America/Mexico_City',
	'(GMT-06:00) Саскачеван' => 'America/Regina',
	'(GMT-05:00) Богота' => 'America/Bogota',
	'(GMT-05:00) Восточное время (США и Канада)' => 'America/New_York',
	'(GMT-05:00) Индиана (Восток)' => 'America/Indiana/Indianapolis',
	'(GMT-05:00) Лима' => 'America/Bogota',
	'(GMT-05:00) Кито' => 'America/Bogota',
	'(GMT-04:00) Атлантическое время (Канада)' => 'America/Halifax',
	'(GMT-04:00) Каракас' => 'America/Caracas',
	'(GMT-04:00) Ла-Пас' => 'America/Caracas',
	'(GMT-04:00) Сантьяго' => 'America/Santiago',
	'(GMT-03:30) Ньюфаундленд' => 'America/St_Johns',
	'(GMT-03:00) Бразилия' => 'America/Sao_Paulo',
	'(GMT-03:00) Буэнос-Айрес' => 'America/Argentina/Buenos_Aires',
	'(GMT-03:00) Джорджтаун' => 'America/Argentina/Buenos_Aires',
	'(GMT-03:00) Гренландия' => 'America/Godthab',
	'(GMT-02:00) Среднеатлантическое время' => 'America/Noronha',
	'(GMT-01:00) Азорские острова, ' => 'Atlantic/Azores',
	'(GMT-01:00) Острова Зелёного Мыса' => 'Atlantic/Cape_Verde',
	'(GMT) Касабланка' => 'Africa/Casablanca',
	'(GMT) Эдинбург' => 'Europe/London',
	'(GMT) Время по Гринвичу: Дублин' => 'Europe/London',
	'(GMT) Лиссабон' => 'Europe/London',
	'(GMT) Лондон' => 'Europe/London',
	'(GMT) Монровия' => 'Africa/Casablanca',
	'(GMT+01:00) Амстердам' => 'Europe/Berlin',
	'(GMT+01:00) Белград' => 'Europe/Belgrade',
	'(GMT+01:00) Берлин' => 'Europe/Berlin',
	'(GMT+01:00) Берн' => 'Europe/Berlin',
	'(GMT+01:00) Братиславе' => 'Europe/Belgrade',
	'(GMT+01:00) Брюссель' => 'Europe/Paris',
	'(GMT+01:00) Будапеште' => 'Europe/Belgrade',
	'(GMT+01:00) Копенгаген' => 'Europe/Paris',
	'(GMT+01:00) Любляна' => 'Europe/Belgrade',
	'(GMT+01:00) Мадрид' => 'Europe/Paris',
	'(GMT+01:00) Париж' => 'Europe/Paris',
	'(GMT+01:00) Прага' => 'Europe/Belgrade',
	'(GMT+01:00) Рим' => 'Europe/Berlin',
	'(GMT+01:00) Сараево' => 'Europe/Sarajevo',
	'(GMT+01:00) Скопье' => 'Europe/Sarajevo',
	'(GMT+01:00) Стокгольм' => 'Europe/Berlin',
	'(GMT+01:00) Вена' => 'Europe/Berlin',
	'(GMT+01:00) Варшава' => 'Europe/Sarajevo',
	'(GMT+01:00) Центральная Америка' => 'Africa/Lagos',
	'(GMT+01:00) Загреб' => 'Europe/Sarajevo',  
	'(GMT+02:00) Афины' => 'Europe/Istanbul',
	'(GMT+02:00) Бухарест' => 'Europe/Bucharest',
	'(GMT+02:00) Каир' => 'Africa/Cairo',
	'(GMT+02:00) Хараре' => 'Africa/Johannesburg',
	'(GMT+02:00) Хельсинки' => 'Europe/Helsinki',
	'(GMT+02:00) Стамбул' => 'Europe/Istanbul',
	'(GMT+02:00) Иерусалим' => 'Asia/Jerusalem',
	'(GMT+02:00) Киев' => 'Europe/Helsinki',	
	'(GMT+02:00) Претория' => 'Africa/Johannesburg',
	'(GMT+02:00) Рига' => 'Europe/Helsinki',
	'(GMT+02:00) София' => 'Europe/Helsinki',
	'(GMT+02:00) Таллин' => 'Europe/Helsinki',
	'(GMT+02:00) Вильнюс' => 'Europe/Helsinki',
	'(GMT+03:00) Минск' => 'Europe/Istanbul',
	'(GMT+03:00) Багдад' => 'Asia/Baghdad',
	'(GMT+03:00) Кувейт' => 'Asia/Riyadh',
	'(GMT+03:00) Найроби' => 'Africa/Nairobi',
	'(GMT+03:00) Эр-Рияд' => 'Asia/Riyadh',
	'(GMT+03:30) Тегеран' => 'Asia/Tehran',
	'(GMT+04:00) Москва' => 'Europe/Moscow',	
	'(GMT+04:00) Санкт-Петербург' => 'Europe/Moscow',
	'(GMT+04:00) Волгоград' => 'Europe/Moscow',
	'(GMT+04:00) Абу-Даби' => 'Asia/Muscat',
	'(GMT+04:00) Баку' => 'Asia/Tbilisi',
	'(GMT+04:00) Мускат' => 'Asia/Muscat',
	'(GMT+04:00) Тбилиси' => 'Asia/Tbilisi',
	'(GMT+04:00) Ереван' => 'Asia/Tbilisi',
	'(GMT+04:30) Кабул' => 'Asia/Kabul',	
	'(GMT+05:00) Исламабад' => 'Asia/Karachi',
	'(GMT+05:00) Карачи' => 'Asia/Karachi',
	'(GMT+05:00) Ташкент' => 'Asia/Karachi',
	'(GMT+05:30) Ченнай' => 'Asia/Calcutta',
	'(GMT+05:30) Калькутте' => 'Asia/Calcutta',
	'(GMT+05:30) Мумбаи' => 'Asia/Calcutta',
	'(GMT+05:30) Нью-Дели' => 'Asia/Calcutta',
	'(GMT+05:45) Катманду' => 'Asia/Katmandu',
	'(GMT+06:00) Екатеринбург' => 'Asia/Yekaterinburg',
	'(GMT+06:00) Алматы' => 'Asia/Novosibirsk',
	'(GMT+06:00) Астана' => 'Asia/Dhaka',
	'(GMT+06:00) Дакка' => 'Asia/Dhaka',
	'(GMT+06:00) Новосибирск' => 'Asia/Novosibirsk',
	'(GMT+06:00) Шри-Джаяварденепура-Котте' => 'Asia/Colombo',
	'(GMT+06:30) Рангун' => 'Asia/Rangoon',
	'(GMT+07:00) Бангкок' => 'Asia/Bangkok',
	'(GMT+07:00) Ханой' => 'Asia/Bangkok',
	'(GMT+07:00) Джакарта' => 'Asia/Bangkok',
	'(GMT+07:00) Красноярск' => 'Asia/Krasnoyarsk',
	'(GMT+08:00) Пекин' => 'Asia/Hong_Kong',
	'(GMT+08:00) Чунцин' => 'Asia/Hong_Kong',
	'(GMT+08:00) Гонконг' => 'Asia/Hong_Kong',
	'(GMT+08:00) Иркутск' => 'Asia/Irkutsk',
	'(GMT+08:00) Куала-Лумпур' => 'Asia/Singapore',
	'(GMT+08:00) Перт' => 'Australia/Perth',
	'(GMT+08:00) Сингапур' => 'Asia/Singapore',
	'(GMT+08:00) Тайбэй' => 'Asia/Taipei',
	'(GMT+08:00) Улан-Батор' => 'Asia/Irkutsk',
	'(GMT+08:00) Урумчи' => 'Asia/Hong_Kong',
	'(GMT+09:00) Осака' => 'Asia/Tokyo',
	'(GMT+09:00) Саппоро' => 'Asia/Tokyo',
	'(GMT+09:00) Сеул' => 'Asia/Seoul',
	'(GMT+09:00) Токио' => 'Asia/Tokyo',	
	'(GMT+09:30) Аделаида' => 'Australia/Adelaide',
	'(GMT+09:30) Дарвин' => 'Australia/Darwin',
	'(GMT+10:00) Якутск' => 'Asia/Yakutsk',
	'(GMT+10:00) Брисбен' => 'Australia/Brisbane',
	'(GMT+10:00) Канберра' => 'Australia/Sydney',
	'(GMT+10:00) Гуам' => 'Pacific/Guam',
	'(GMT+10:00) Хобарт' => 'Australia/Hobart',
	'(GMT+10:00) Мельбурн' => 'Australia/Sydney',
	'(GMT+10:00) Порт-Морсби' => 'Pacific/Guam',
	'(GMT+10:00) Сидней' => 'Australia/Sydney',
	'(GMT+11:00) Владивосток' => 'Asia/Vladivostok',	
	'(GMT+11:00) Новая Каледония' => 'Asia/Magadan',
	'(GMT+11:00) Соломоновы Острова' => 'Asia/Magadan',
	'(GMT+12:00) Магадан' => 'Asia/Magadan',
	'(GMT+12:00) Окленд' => 'Pacific/Auckland',
	'(GMT+12:00) Фиджи' => 'Pacific/Fiji',
	'(GMT+12:00) Камчатка' => 'Pacific/Fiji',
	'(GMT+12:00) Маршалловы Острова' => 'Pacific/Fiji',
	'(GMT+12:00) Веллингтон' => 'Pacific/Auckland',
	'(GMT+13:00) Нукуалофа' => 'Pacific/Tongatapu',
);

function TimezoneList($selected = false) {
global $timezones;

$html = '';

foreach ($timezones as $key => $value)
	$html .= '<option value="'.$value.'" '.(($value == $selected)?'selected':'').'>'.$key.'</option>'.PHP_EOL;
	
return $html;
}

function IsValidTimeZone($timezone) {
global $timezones;

foreach ($timezones as $key => $value) if ($value == $timezone) return $value;		
return false;
}
