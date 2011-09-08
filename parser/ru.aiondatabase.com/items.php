<?php
namespace Parser;

require_once '../library/Config/Config.php';
require_once '../library/Config/Xml.php';

require_once '../library/Cli.php';

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

    protected $_images = array();

    protected $_noFoundKey = array(
        'main'      => array(),
        'critical'  => array(),
    );

    public function addError($itemId, $key, $blockId = null, $value = null, $prioritet = 'main') {
        $errKey = $key;
        if ($value) $errKey .= ' - ' . $value;

        $errValue = $itemId;
        if ($blockId) $errValue .= ':' . $blockId;

        if (isset($this->_noFoundKey[$prioritet][$errKey])) {
            $this->_noFoundKey[$prioritet][$errKey][] = $errValue;
        } else {
            $this->_noFoundKey[$prioritet][$errKey] = array($errValue);
        }

        return $this;
    }

    public function parserHtml($itemId)
    {
        $url = str_replace('{id}', $itemId, $this->_itemUrl);
        $file = @file_get_contents($url);
        if (!$file) return false;

        $options = array(
            'image' => '',
            'ap_price' => array(
                'ap'            => 0,
                'medal'         => 0,
                'medal_name'    => ''
            ),
            'slot' => 0,
        );

        $p = new \Zend_Dom_Query();
        $p->setDocumentHtml($file);

        // get full Image
        $r = $p->query('.infobox-table .map_tooltip_border img');
        if ($r->count() && 'img' == $r->current()->nodeName) {
            $options['image'] = $r->current()->getAttribute('src');
        }

        // table
        $r = $p->query('.infobox-table table');
        if (!empty($options['image'])) $r->next();
        $table = $r->current();

        $td = $table->getElementsByTagName('td');
        $blocks = array();
        foreach ($td as $t) {
            $blocks[] = trim(str_replace(':', '', $t->textContent));
        }
        while ($key = array_shift($blocks)) {
            $value = array_shift($blocks);

            switch ($key) {
                case 'Можно покрасить':
                    if ('Да' == $value) $options['paint'] = true; else $options['paint'] = false;
                    break;

                case 'Слот инвентаря':

                    if (!isset($this->_slots[$value])) {
                        $this->_slots[$value] = sizeof($this->_slots);
                    }

                    $options['slot'] = $this->_slots[$value];
                    break;

                case 'Нужно очков бездны':
                    $options['ap_price']['ap'] = (int) $value;
                    break;

                case 'Покупается с':
                    $options['ap_price']['medal'] = (int) substr($value, 0, strpos($value, 'x'));
                    $options['ap_price']['medal_name'] = substr($value, strpos($value, ' '));
                    break;

                case 'Цена торговца':
                case 'Продается за':
                case 'Уровень':
                case 'Количество в Стеке':

                    break;

                default:
                    //$this->_noFoundKey['html'][$key . ' = ' . $value] = $id;
                    $this->addError($itemId, $key, null, $value);
                    break;
            }
        }

        return $options;
    }

    public function parserPvP($itemId)
    {
        $url = str_replace('{id}', $itemId, $this->_itemUrlCompare);
        $file = @file_get_contents($url);
        if (!$file) return false;
        
        $json = (array) json_decode($file);

        $options = array(
            'smallimage' => '',
            'pvp_atack' => 0,
            'pvp_protect' => 0,
            'q' => 0,
        );

        $options['smallimage'] = '/res/icons/40/' . $json['i'] . '.png';
        $options['q'] = $json['q'];

        foreach ($json['fields'] as $key => $value) {
            // атака
            if (48 == $value) $options['pvp_atack'] = $json['values'][$key];

            // защита
            if (49 == $value) $options['pvp_protect'] = $json['values'][$key];
        }

        return $options;
    }

    public function parserCompare($itemId)
    {
        $url = str_replace('{id}', $itemId, $this->_itemUrlJson);
        $file = @file_get_contents($url);
        if (!$file) {
            $this->addError($itemId, 'НЕ МОГУ ПОЛУЧИТЬ COMPARE СТРАНИЦУ', null, null, 'critical');
            return false;
        }


        if (false !== $pos = strpos($file, "content:'")) {
            $file = substr($file, $pos + strlen("content:'"), strpos($file, "', icon") - strlen("', icon") - $pos - 2);
        }
        $file = '<html><head><meta http-equiv="Content-Type" content="text/html; charset=UTF-8" ></head><body>' . $file . '</body></html>';

        $p = new \Zend_Dom_Query();
        $p->setDocumentHtml($file);

        $table = $p->query('table');
        if (!$table->count()) {
            $this->addError($itemId, 'НЕ МОГУ НАЙТИ TABLE', null, null, 'critical');
            return false;
        }

        $table = $table->current();

        $blocks = array();
        $stoun = 0;
        foreach($table->getElementsByTagName('tr') as $tr) {
            /**
             * @var \DOMElement $tr;
             */
            foreach ($tr->getElementsByTagName('td') as $td) {
                /**
                 * @var \DOMElement $td;
                 */

                if (4 == $td->getAttribute('colspan') && $td->getElementsByTagName('hr')->length) {
                    $blocks[] = 'block';
                }

                if ('aion_item_manastone' == $td->getAttribute('class')) {
                    $stoun++;
                    continue;
                }

                $v = trim($td->textContent, " ");
                if (!empty($v)) $blocks[] = $v;
            }
        }

        if ($stoun > 0) {
            $blocks[] = 'block';
            $blocks[] = 'stoun';
            $blocks[] = $stoun;
        }

        $item = array(
            'name'          => array_shift($blocks),
            'skills'        => '',
            'type'          => 0,
            'for'           => 0,
            'info'          => '',
            'dopinfo'       => '',
            'lvl'           => 0,
            'stoun'         => '',
            'magicstoun'    => '',
            'longatack'     => 0,
            'complect'      => '',
        );

        if (0 === strpos($item['name'], 'Рецепт:')) {
            $item['name'] == trim(substr($item['name'], strlen('Рецепт:')));
            $value = 'Рецепт';

            if (!isset($this->_itemsType[$value])) {
                $this->addError($itemId, 'НЕ ПОДХОДЯЩИЙ ТИП', null, $value);
                return false;
            }
        }

        $blocksId = 0;

        while ($key = array_shift($blocks)) {

            if ('block' == $key) {
                $blocksId++;
                continue;
            }

            //Блок имени, параменты [Обмен невозможен]...
            if (0 == $blocksId) {
                if ('Тип' == $key) {
                    $value = array_shift($blocks);

                    if (!isset($this->_itemsType[$value])) {
                        $this->addError($itemId, 'НЕ ПОДХОДЯЩИЙ ТИП', $blocksId, $value);
                        return false;
                    }

                    $item['type'] = $this->_itemsType[$value];

                } elseif (0 === strpos($key, '[')) {
                    $item['info'] = $key;

                } elseif (0 === strpos($key, 'Можно использовать с ')) {
                    $item['lvl'] = (int) substr($key, strlen('Можно использовать с '), strlen($key) - strlen('Можно использовать с ') - strpos($key, '-го') + 1);

                } elseif (0 === strpos($key, 'Только для ')) {
                    $item['for'] = (self::RACE_ASMO_NAME == substr($key, strlen('Только для '))) ? self::RACE_ASMO_ID : self::RACE_ELI_ID;

                } else {
                    $item[] = $key;
                }
                continue;
            }

            if (false !== strpos($key, 'Комплект') || false !== strpos($key, ' комплект ')) {
                $item['complect'] = array(
                    'name' => $key,
                    'items' => array(),
                    'pieces' => array()
                );

                $tBlock = 0;

                while ($tBlock != 2 && $key = array_shift($blocks)) {
                    if ('block' == $key) {
                        $tBlock++;
                        $blocksId++;
                        continue;
                    }

                    if (0 == $tBlock) $item['complect']['items'][] = $key;
                    else {
                        $piecesCount = (int) substr($key, 0, strpos($key, ' '));
                        $piecesSkills = trim(substr($key, strpos($key, ':') + 1));

                        foreach (explode(',', $piecesSkills) as $skill) {
                            $skill = trim($skill);
                            $value = trim(substr($skill, 0, strpos($skill, ' ')));
                            $name = trim(substr($skill, strpos($skill, ' ')));

                            if (array_key_exists($name, $this->_skills)) {
                                $item['complect']['pieces'][$piecesCount][$name] = $value;
                            } else {
                                $this->addError($itemId, $key, $blocksId);
                                /*
                                if (!isset($this->_noFoundKey[$blocksId . ':' . $key])) {
                                    $this->_noFoundKey[$blocksId . ':' . $key] = $itemId;
                                }
                                */
                            }
                        }
                    }
                }
                continue;
            }

            // Skills
            if (array_key_exists($key, $this->_skills)) {

                if (!is_array($item['skills'])) {
                    $item['skills'] =  array(
                        'main'  => array(),
                        'other' => array()
                    );
                }

                $value = array_shift($blocks);

                if (1 == $blocksId) {
                    $item['skills']['main'][$key] = $value;
                } else {
                    $item['skills']['other'][$key] = $value;
                }
                continue;
            }

            if (0 === strpos($key, 'Можно усилить магическими камнями ')) {
                if (!isset($item['stoun'])) {
                    $item['stoun'] = array();
                }

                $item['stoun']['lvl'] = (int) substr($key, strlen('Можно усилить магическими камнями '), strlen($key) - strlen('Можно усилить магическими камнями ') - strpos($key, '-го')+2);
                continue;

            } elseif ('stoun' == $key) {
                $value = array_shift($blocks);
                $item['stoun']['count'] = (int) $value;

                continue;
            } elseif (0 === strpos($key, 'Можно вставить божественный камень')) {
                $item['magicstoun'] = 1;

                continue;
            } elseif (0 === strpos($key, 'Дистанция ударов ближнего боя увеличена')) {
                $item['longatack'] = 1;

                continue;
            } elseif (false !== strpos($key, 'DelayTime') || false !== strpos($key, 'Combo')) {

                // Не игровая модель, не сохраняем
                return null;
            }

            $item['dopinfo'] .= (!empty($item['dopinfo'])) ? PHP_EOL . $key : $key;

            if (false !== strpos($key, 'оружие, кол-во ударов')) {
                continue;
            }

            $this->addError($itemId, $key, $blocksId);
        }

        return $item;
    }

    public function addImage($url)
    {
        if (empty($url)) return '';
        
        $fileUrl = trim(substr($url, strrpos($url, '/')), '/ ');
        $url = $this->_url . str_replace($this->_url, '', $url);

        if (!isset($this->_images[$fileUrl])) {
            $this->_images[$fileUrl] = $url;
        }
        return $fileUrl;
    }

    public function parser($itemId)
    {
        try {
            $item = $this->parserCompare($itemId);
        } catch (\Exception $e) {
            $this->addError($itemId, '!EXCEPTION COMPARE!');
            return false;
        }
        if (!$item) {
            $this->addError($itemId, 'НЕ МОГУ ПРОПАРСИТЬ COMPARE СТРАНИЦУ');
            return false;
        }

        try {
            $itemHtml = $this->parserHtml($itemId);
        } catch (\Exception $e) {
            $this->addError($itemId, '!EXCEPTION HTML!');
            return false;
        }
        if (!$itemHtml) {
            $this->addError($itemId, 'НЕ МОГУ ПРОПАРСИТЬ HTML СТРАНИЦУ');
        } else {
            $item = array_merge($item, $itemHtml);
        }

        try {
            $itemPvP = $this->parserPvP($itemId);
        } catch (\Exception $e) {
            $this->addError($itemId, '!EXCEPTION PvP!');
            return false;
        }
        if (!$itemPvP) {
            $this->addError($itemId, 'НЕ МОГУ ПРОПАРСИТЬ PVP СТРАНИЦУ');
        } else {
            $item = array_merge($item, $itemPvP);
        }

        try {
            $item['image'] = $this->addImage($item['image']);
            $item['smallimage'] = $this->addImage($item['smallimage']);
        } catch (\Exception $e) {
            $this->addError($itemId, '!EXCEPTION IMAGES!');
            return false;
        }

        $this->_items[$itemId] = $item;
        return $item;
    }

    public function getStatus()
    {
        return $this->_noFoundKey;
    }

    public function getImages()
    {
        return $this->_images;
    }

    public function getItems()
    {
        return $this->_items;
    }
}

$options = \Cli::getParams(array('start' => 0, 'end' => 40000), $argc, $argv);

$parser = new \Parser\Items();
$config = new \Yap\Config\Xml('itemlist.xml');
$i = 0;

echo "======START======\n";
foreach ($config as $id) {
    $i++;
    if ($i < $options['start'] || $i > $options['end']) continue;

    $x = (int) array_shift($id);
    $parser->parser($x);

    echo ($options['end'] - $i) . ' / ' . $i . ' / ' . $options['end'] . ' - ' . $x . PHP_EOL;
}

file_put_contents("db/items-{$options['start']}-{$options['end']}.php", "<?php return unserialize('" . serialize($parser->getItems()) . "');");
file_put_contents("db/images-{$options['start']}-{$options['end']}.php", "<?php return unserialize('" . serialize($parser->getImages()) . "');");
file_put_contents("db/status-{$options['start']}-{$options['end']}.txt", var_export($parser->getStatus(), true));
echo "======END======\n";
echo 'Parser ' . sizeof($parser->getItems()) . ' in ' . $all . PHP_EOL;