<?php
namespace Parser;

class MergeItems
{
    public static $_items = array();
    public static $_images = array();

    protected static $_noFoundKey = array();

    public static function addError($fileName)
    {
        self::$_noFoundKey[] = $fileName;
    }

    public static function getErrors()
    {
        return var_export(self::$_noFoundKey, true);
    }

    public static function mergeItems()
    {
        if ($handle = opendir('db')) {
            echo "Directory handle: $handle\n";
            echo "Files: \n";

            function arrayMergeItem($array, $array2) {
                foreach ($array2 as $id => $item) {
                    $item['aion_id'] = $id;
                    $array[$id] = $item;
                }
                return $array;
            }

            while (false !== ($file = readdir($handle))) {
                if ('items-all.php' == $file || 'images-all.php' == $file) continue;
                
                if (0 === strpos($file, 'items-')) {
                    echo "\t$file\n";
                    $items = require_once('db/' . $file);

                    if (is_array($items)) {
                        self::$_items = arrayMergeItem(self::$_items, $items);
                    } else {
                        self::addError($file);
                    }
                }

                if (0 === strpos($file, 'images-')) {
                    echo "\t$file\n";
                    $images = require_once('db/' . $file);
                    if (is_array($images)) {
                        self::$_images = array_merge(self::$_images, $images);
                    } else {
                        self::addError($file);
                    }
                }
            }

            closedir($handle);
        }
    }

    public static function saveImagesToFile()
    {
        $images = '';
        foreach (self::$_images as $image) {
            $images .= "$image\n";
        }
        file_put_contents("db/images.txt", $images);
        system("wget -c --limit-rate=50k -i db/images.txt -P db/images");
    }

    public static function saveToSql($oneInsertRecordCount = 1000)
    {
        $handle = 'INSERT INTO `aion`.`items` (`aion_id`, `name`, `type`, `lvl`, `slot`, `q`, `skills`, `pvp_atack`, `pvp_protect`, `ap_price`, `stoun`, `magicstoun`, `longatack`, `complect`, `info`, `dopinfo`, `smallimage`, `image`) VALUES ';

        $dump = $handle . PHP_EOL;

        $options = array(
            'name'          => '',
            'type'          => 0,
            'for'           => 0,
            'lvl'           => 0,
            'slot'          => 0,
            'q'             => 0,

            'skills'        => '',

            'pvp_atack'     => 0,
            'pvp_protect'   => 0,

            'ap_price'      => '',

            'stoun'         => '',
            'magicstoun'    => 0,
            'longatack'     => 0,
            'complect'      => '',

            'info'          => '',
            'dopinfo'       => '',

            'smallimage'    => '',
            'image'         => '',
        );

        $i = 0;

        function ser($value) {
            return (!empty($value)) ? serialize($value) : '';
        }

        $s = false;

        foreach (self::$_items as $id => $item) {
            $i++;
            $item = array_merge($options, (array) $item);

            $item['skills'] = ser($item['skills']);
            $item['ap_price'] = ser($item['ap_price']);
            $item['stoun'] = ser($item['stoun']);
            $item['complect'] = ser($item['complect']);

            $dump .= "('$id', '{$item['name']}', '{$item['type']}', '{$item['lvl']}', '{$item['slot']}', '{$item['q']}', '{$item['skills']}', '{$item['pvp_atack']}', '{$item['pvp_protect']}', '{$item['ap_price']}', '{$item['stoun']}', '{$item['magicstoun']}', '{$item['longatack']}', '{$item['complect']}', '{$item['info']}', '{$item['dopinfo']}', '{$item['smallimage']}', '{$item['image']}')";

            if ((0 == ($i % 100)) && (0 != ($i % $oneInsertRecordCount))) {
                $dump .= ";\n\n {$handle}\n";
                $s = true;
            }

            if (0 == ($i % $oneInsertRecordCount)) {
                $dump .= ";\n\n";

                file_put_contents("db/dump-$i.sql", $dump);

                $dump = "{$handle}\n";
            } else {
                if ($i != sizeof(self::$_items)) {
                    if (false == $s) $dump .= ",\n";
                } else {
                    $dump .= ";\n\n";
                    file_put_contents("db/dump-$i.sql", $dump);
                }
            }
            $s = false;
        }

        return $dump;
    }
}

echo "======START======\n";
MergeItems::mergeItems();

file_put_contents("db/items-all.php", "<?php return unserialize('" . serialize(MergeItems::$_items) . "');");
file_put_contents("db/items-images.php", "<?php return unserialize('" . serialize(MergeItems::$_images) . "');");
MergeItems::saveToSql();
MergeItems::saveImagesToFile();
echo "======END======\n";
echo 'items: ' . sizeof(MergeItems::$_items) . PHP_EOL . 'images: ' . sizeof(MergeItems::$_images) . PHP_EOL;
echo 'Errors: ' . MergeItems::getErrors() . PHP_EOL;
