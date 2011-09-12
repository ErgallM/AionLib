<?php
if (false === strpos($_SERVER['HTTP_REFERER'], 'http://aionlib.local')) {
    die('GOOD BY!');
}
function mysqlConnect()
{
    $conn = mysql_connect("localhost", "root", "25459198");

    if (!$conn) {
        echo "Unable to connect to DB: " . mysql_error();
        exit;
    }

    if (!mysql_select_db("aion")) {
        echo "Unable to select mydbname: " . mysql_error();
        exit;
    }

    mysql_query("SET NAMES 'UTF8'");
    mysql_query ("set character_set_client='utf8'");
    mysql_query ("set character_set_results='utf8'");
    mysql_query ("SET NAMES utf8'");
    mysql_query ("set collation_connection='utf8_general_ci'");
}

function ser($value) {
    return (!empty($value)) ? unserialize($value) : '';
}

function getValue($value) {
    $value = htmlentities($value, ENT_QUOTES, 'utf-8');
    $value = strip_tags($value);
    return $value;
}

if (isset($_GET['data'])) {
    mysqlConnect();

    $options = array(
        'name' => (isset($_GET['data']['name'])) ? getValue($_GET['data']['name']) : '',
        'start' => (isset($_GET['start'])) ? (int) $_GET['start'] : 0,
        'slot' => (!empty($_GET['data']['slot'])) ? (int) $_GET['data']['slot'] : null,
        'type' => (!empty($_GET['data']['type'])) ? explode(',', $_GET['data']['type']) : null
    );

    echo get_items($options, 100);
    die();
}

function get_items($options, $post) {
    $start = $options['start'];

    $name = $options['name'];
    if (!empty($name)) {
        $name = "`name` LIKE '%$name%'";
    } else {
        $name = "`name` != ''";
    }


    $slot = '';
    if (!empty($options['slot'])) {
        if (12 == $options['slot'] || 13 == $options['slot']) {
            $slot = "AND (`slot` = 12 OR `slot` = 13)";
        } else {
            $slot = "AND `slot` = '{$options['slot']}'";
        }
    }

    if (empty($slot)) $slot = 'AND slot > 0';

    $type = '';
    if (isset($options['type']) && is_array($options['type']) && sizeof($options['type'])) {
        $type = "AND `type` IN ('" . implode("', '", $options['type']) . "')";
    }

    $sql = "SELECT * FROM `items` WHERE $name $slot $type ORDER BY q DESC, name LIMIT $start, $post";

    $result = mysql_query($sql);

    if (!$result) {
        echo "Could not successfully run query ($sql) from DB: " . mysql_error();
        exit;
    }

    if (mysql_num_rows($result) == 0) {
        echo "No rows found, nothing to print so am exiting";
        exit;
    }

    $items = array();
    while ($item = mysql_fetch_assoc($result)) {
        $item['skills'] = ser($item['skills']);
        $item['ap_price'] = ser($item['ap_price']);
        $item['stoun'] = ser($item['stoun']);
        $item['complect'] = ser($item['complect']);
        $item['smallimage'] = (!empty($item['smallimage'])) ? '/parser/ru.aiondatabase.com/images/' . $item['smallimage'] : '';
        $item['image'] = (!empty($item['smallimage'])) ? '/parser/ru.aiondatabase.com/images/' . $item['image'] : '';

        $items[] = $item;
    }
    return json_encode($items);
}

