<?php
namespace Parser;

require_once '../library/Config/Config.php';
require_once '../library/Config/Xml.php';

require_once '../library/Dom/Query.php';

class RuAionDatabase
{
    protected $_url = 'http://ru.aiondatabase.com';

    //protected $_xml = 'http://ru.aiondatabase.com/xml/ru_RU/items/itemlist.xml';
    protected $_xml = 'itemlist.xml';

    protected $_itemUrl = 'http://ru.aiondatabase.com/item/{id}/';
    protected $_itemUrlCompare = 'http://ru.aiondatabase.com/item/{id}?compare';
    protected $_itemUrlJson = 'http://www.aiondatabase.com/res/tooltip/ru_RU/2600/item/js/{id}.js';

    const RACE_ASMO_ID = 1;
    const RACE_ASMO_NAME = 'асмодиан';

    const RACE_ELI_ID = 2;
    const RACE_ELI_NAME = 'элийцев';

    protected $_items = array();
    protected $_itemsType = array();
    protected $_skills = array(
        'Атака'             => 0,
        'Скор. атаки'       => 1,
        'Точность'          => 2,
        'Ф. крит.'          => 3,
        'Парир.'            => 4,
        'Точн. магии'       => 5,
        'Макс. HP'          => 6,
        'Физическая атака'  => 7,
        'Парир.'            => 8,
        'Макс. MP'          => 9,
        'Блок щитом'        => 10,
        'Сила магии'        => 11,
        'М. крит.'          => 12,
        'Скор. магии'       => 13,
        'Маг. атака'        => 14,
        'Уклонение'         => 15,
        'Физ. защита'       => 16,
        'Маг. защита'       => 17,
        'Концентрац.'       => 18,
        'Скор. полета'      => 19,
        'Время полета'      => 20,
        
        'Агрессия'          => 21,
        'Скор. движ.'       => 22,
        'ЛВК'               => 23,
    );

    protected $_slots = array();

    protected $_images = array();

    protected $_noFoundKey = array();

    public function parserXml()
    {
        $xml = new \Yap\Config\Xml($this->_xml);
        return $xml->toArray();
    }

    /**
     * @param $id
     * @return DOMDocument
     */
    public function getPageContent($id, $type = 'html')
    {
        $dom = new \DOMDocument();

        if ('html' == $type) {
            $url = str_replace('{id}', $id, $this->_itemUrl);
            @$dom->loadHTMLFile($url);
        } elseif ('json' == $type) {
            $url = str_replace('{id}', $id, $this->_itemUrlJson);
            @$file = file_get_contents($url);

            if (false !== $pos = strpos($file, "content:'")) {
                $file = substr($file, $pos + strlen("content:'"), strpos($file, "', icon") - strlen("', icon") - $pos - 2);
            }
            $file = '<html><head><meta http-equiv="Content-Type" content="text/html; charset=UTF-8" ></head><body>' . $file . '</body></html>';
            $dom->loadHTML($file);
        }
        return $dom;
    }

    public function parser(\DOMDocument $dom, $id)
    {
        $contant = $dom->getElementsByTagName('table');
        $contantTable = null;
        if (1 == $contant->length) {
            $contantTable = $contant->item(0);
        } else {
            foreach ($contant as $table) {
                /**
                 * @var \DOMElement $table
                 */
                if (false !== strpos($table->getAttribute('class'), 'aion_tooltip_container')) {
                    $contantTable = $table->getElementsByTagName('table')->item(0);
                    break;
                }
            }
        }

        if (null === $contantTable) {
            return;
        }

        /**
         * @var \DOMElement $contant;
         */
        $blocks = array();
        $stoun = 0;
        foreach($contantTable->getElementsByTagName('tr') as $tr) {
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

        return $this->parsetBlocks($blocks, $id);
    }

    public function parsetBlocks(array $blocks, $itemId)
    {
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
                        $this->_itemsType[$value] = sizeof($this->_itemsType);
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
                                if (!isset($this->_noFoundKey[$blocksId . ':' . $key])) {
                                    $this->_noFoundKey[$blocksId . ':' . $key] = $itemId;
                                }
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

            $item['dopinfo'] .= $key . PHP_EOL;

            if (!isset($this->_noFoundKey[$blocksId . ':' . $key])) {
                $this->_noFoundKey[$blocksId . ':' . $key] = $itemId;
            }
        }

        $item = array_merge($item, $this->parserHtml($itemId), $this->parserPVP($itemId));

        if (!empty($item['image'])) {
            if (!isset($this->_images[$item['image']])) {
                $this->_images[$item['image']] = $item['image'];
            }
        }

        if (!empty($item['smallimage'])) {
            if (!isset($this->_images[$item['smallimage']])) {
                $this->_images[$item['smallimage']] = $item['smallimage'];
            }
        }

        $this->_items[$itemId] = $item;
        return $item;
    }

    public function parserHtml($id)
    {
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
        $url = str_replace('{id}', $id, $this->_itemUrl);
        @$file = file_get_contents($url);
        if (!$file) return array();

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

                default:
                    $this->_noFoundKey['html'][$key . ' = ' . $value] = $id;
                    break;
            }
        }

        return $options;
    }

    public function parserPVP($id)
    {
        $url = str_replace('{id}', $id, $this->_itemUrlCompare);
        $json = (array) json_decode(file_get_contents($url));

        $options = array(
            'smallimage' => '',
            'pvp_atack' => 0,
            'pvp_protect' => 0,
            'q' => 0,
        );

        $options['smallimage'] = '/res/icons/40/' . $json['i'];
        $options['q'] = $json['q'];

        foreach ($json['fields'] as $key => $value) {
            // атака
            if (48 == $value) $options['pvp_atack'] = $json['values'][$key];

            // защита
            if (49 == $value) $options['pvp_protect'] = $json['values'][$key];
        }

        return $options;
    }


    public function getStatus() {
        return var_export(array($this->_noFoundKey, $this->_itemsType, $this->_skills), true);
    }

    public function getItems()
    {
        return $this->_items;
    }

    public function getOptions()
    {
        return array(
            'itemsType' => $this->_itemsType,
            'skills'    => $this->_skills,
            'slots'     => $this->_slots
        );
    }

    public function getImages()
    {
        return $this->_images;
    }

    public function getImagesAsString()
    {
        $result = '';
        foreach ($this->_images as $image) {
            $result .= 'http://ru.aiondatabase.com' . $image . PHP_EOL;
        }

        return $result;
    }
}

$options = array(
    'start' => 1,
    'file' => 'status.txt'
);

if ($argc > 1) {
    foreach ($argv as $com) {
        if (0 === strpos($com, '-')) {
            $varName = substr($com, 1, strpos($com, '=') - 1);
            $varValue = substr($com, strpos($com, '=') + 1);

            if (isset($options[$varName])) $options[$varName] = $varValue;
        }
    }
}

$parser = new RuAionDatabase();
$i = 0;

$x = 100000000;

//var_dump($parser->parser($parser->getPageContent($x, 'json'), $x));
//$parser->parserHtml($x);
//$parser->parserPVP($x);
//var_dump($parser->getStatus());

/*
$items = $parser->parserXml();
$all = sizeof($items);
foreach($items as $itemName => $ids) {
    if (sizeof($ids) > 1) {
        echo $itemName . ' - ' . var_export($ids);
    }

    $i++;
    $x = array_shift($ids);
    
    $parser->parser($parser->getPageContent($x, 'json'), $x);
    echo "$all/$i - $x\n";
}
*/
$all = 200000000 - 100000000;
for ($x = 100000000; $x <= 200000000; $x++) {
    $i++;

    $item = $parser->parser($parser->getPageContent($x, 'json'), $x);
    if (null === $item) {
        echo "$all/$i - $x - null\n";
    } else {
        echo "$all/$i - $x\n";
    }
}

file_put_contents('status.txt', $parser->getStatus());
file_put_contents('options.php', "<?php return " . serialize($parser->getOptions()));
file_put_contents('db.php', "<?php return " . serialize($parser->getItems()));
file_put_contents('images.txt', $parser->getImagesAsString());


echo '100%' . PHP_EOL;
