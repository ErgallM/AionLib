<?php
namespace Parser;

require_once '../library/Config/Config.php';
require_once '../library/Config/Xml.php';

class RuAionDatabase
{
    protected $_url = 'http://ru.aiondatabase.com';

    //protected $_xml = 'http://ru.aiondatabase.com/xml/ru_RU/items/itemlist.xml';
    protected $_xml = 'itemlist.xml';

    protected $_itemUrl = 'http://ru.aiondatabase.com/item/{id}/';
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
        'Парир.'            => 8
    );

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
            $file = file_get_contents($url);

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
            $blocks[] = 'stoun';
            $blocks[] = $stoun;
        }

        $this->parsetBlocks($blocks, $id);
    }

    public function parsetBlocks(array $blocks, $itemId)
    {
        $item = array(
            'name' => array_shift($blocks),
            'skills' => '',
            'type'  => 0,
            'for'   => 0,
            'info'  => '',
            'lvl'   => 0,
            'stoun' => '',
            'magicstoun' => ''

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
            }

            if (!isset($this->_noFoundKey[$blocksId . ':' . $key])) {
                $this->_noFoundKey[$blocksId . ':' . $key] = $itemId;
            }
        }

        $this->_items[$itemId] = $item;
    }


    public function getStatus() {
        return var_export(array($this->_noFoundKey, $this->_itemsType, $this->_skills), true);
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

file_put_contents('/home/ergallm/status.txt', $parser->getStatus());
echo '100%' . PHP_EOL;


