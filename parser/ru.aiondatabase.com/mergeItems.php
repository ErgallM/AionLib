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

            while (false !== ($file = readdir($handle))) {
                if ('items-all.php' == $file || 'images-all.php' == $file) continue;
                
                if (0 === strpos($file, 'items-')) {
                    echo "\t$file\n";
                    $items = require_once('db/' . $file);

                    if (is_array($items)) {
                        self::$_items = array_merge(self::$_items, $items);
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
}

echo "======START======\n";
MergeItems::mergeItems();

file_put_contents("db/items-all.php", "<?php return unserialize('" . serialize(MergeItems::$_items) . "');");
file_put_contents("db/items-images.php", "<?php return unserialize('" . serialize(MergeItems::$_images) . "');");
echo "======END======\n";
echo 'items: ' . sizeof(MergeItems::$_items) . PHP_EOL . 'images: ' . sizeof(MergeItems::$_images) . PHP_EOL;
echo 'Errors: ' . MergeItems::getErrors() . PHP_EOL;
