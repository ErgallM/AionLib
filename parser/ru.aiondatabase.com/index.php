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

    protected $params = array();

    public function parserXml()
    {
        $xml = new \Yap\Config\Xml($this->_xml);
        return $xml->toArray();
    }

    /**
     * @param $id
     * @return DOMDocument
     */
    public function getPageContent($id)
    {
        $url = str_replace('{id}', $id, $this->_itemUrl);

        $dom = new \DOMDocument();
        @$dom->loadHTMLFile($url);
        return $dom;
    }

    public function parser(\DOMDocument $dom)
    {
        $contant = $dom->getElementsByTagName('table');
        $contantTable = null;
        foreach ($contant as $table) {
            /**
             * @var \DOMElement $table
             */
            if ('aion_tooltip_container' == $table->getAttribute('class')) {
                echo "ok\n";
                $contantTable = $table->getElementsByTagName('table')->item(0);
                break;
            }
        }

        if (null === $contantTable) return ;

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

        // parser params
        $params = array();
        /*
         *
         $params['name'] = $blocks[0]; unset($blocks[0]);
        $params['stoun_count'] = $stoun;
*/
        var_dump($blocks);

    }

}

$parser = new RuAionDatabase();
$parser->parser($parser->getPageContent(100000893));
