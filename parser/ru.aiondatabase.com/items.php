<?php
namespace Parser;

require_once '../library/Config/Config.php';
require_once '../library/Config/Xml.php';

require_once '../library/Dom/Query.php';

class Items
{
    protected $_url = 'http://ru.aiondatabase.com';
    protected $_xml = 'itemlist.xml';

    protected $_itemUrl = 'http://ru.aiondatabase.com/item/{id}/';
    protected $_itemUrlCompare = 'http://ru.aiondatabase.com/item/{id}?compare';
    protected $_itemUrlJson = 'http://www.aiondatabase.com/res/tooltip/ru_RU/2600/item/js/{id}.js';

    const RACE_ASMO_ID = 1;
    const RACE_ASMO_NAME = 'асмодиан';

    const RACE_ELI_ID = 2;
    const RACE_ELI_NAME = 'элийцев';

    protected $_items = array();

    protected $_itemsType = array(
        'Тканые доспехи'        => 1,
        'Кожаные доспехи'       => 2,
        'Кольчужные доспехи'    => 3,
        'Латные доспехи'        => 4,
        'Щиты'                  => 5,
        'Головной убор'         => 6,

        'Копья'                 => 7,
        'Двуручные мечи'        => 8,
        'Мечи'                  => 9,
        'Кинжалы'               => 10,
        'Булавы'                => 11,
        'Посохи'                => 12,
        'Луки'                  => 13,
        'Орбы'                  => 14,
        'Гримуары'              => 15,

        'Серьги'                => 16,
        'Ожерелья'              => 17,
        'Кольца'                => 18,
        'Пояса'                 => 19,
    );

    protected $_skills = array(
        'Атака'             => 1,
        'Физическая атака'  => 2,
        'Маг. атака'        => 3,
        'Скор. атаки'       => 4,
        'Скор. магии'       => 5,
        'Точность'          => 6,
        'Точн. магии'       => 7,
        'Ф. крит.'          => 8,
        'М. крит.'          => 9,
        'Сила магии'        => 10,
        'Сила исцелен.'     => 11,

        'Парир.'            => 12,
        'Уклонение'         => 13,
        'Концентрац.'       => 14,
        'Блок урона'        => 15,
        'Блок щитом'        => 16,
        'Блок ф. крит.'     => 17,
        'Блок м. крит.'     => 18,

        'Физ. защита'       => 19,
        'Маг. защита'       => 20,
        'Защ. от земли'     => 21,
        'Защ. от возд.'     => 22,
        'Защ. от воды'      => 23,
        'Защ. от огня'      => 24,
        'Защита от ф. крит.' => 25,

        'Сопротивление оглушению'   => 26,
        'Сопротивление опрокидыванию' => 27,
        'Сопротивление отталкиванию' => 28,

        'Макс. HP'          => 29,
        'Макс. MP'          => 30,

        'Скор. полета'      => 31,
        'Время полета'      => 32,
        'Скор. движ.'       => 33,

        'Агрессия'          => 34,

        'ЛВК'               => 35,
    );

    protected $_slots = array(
        'Голова'            => 1,
        'Торс'              => 2,
        'Штаны'             => 3,
        'Ботинки'           => 4,
        'Наплечники'        => 5,
        'Перчатки'          => 6,

        'Ожерелья'          => 7,
        'Серьги'            => 8,
        'Кольца'            => 9,
        'Пояс'              => 10,

        'Крыло'             => 11,

        'Главная или Вторая Рука'   => 12,
        'Главная Рука'              => 13,

        'Одна'              => 13,
        'Обе'               => 12,
    );

    protected $_noFoundKey = array();

    public function addError($itemId, $key, $value) {
        
    }

    public function parserJson() {}
    public function parserPvP() {}
    public function parserHtml() {}


}