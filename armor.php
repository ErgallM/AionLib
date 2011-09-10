<?php
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


$number_of_posts = 100;
if (isset($_GET['name']) && isset($_GET['start'])) {
    mysqlConnect();
    echo get_items($_GET['name'], $_GET['start'], $number_of_posts);
    die();
}

function get_items($name, $start, $post) {
    $sql = "SELECT * FROM `items` WHERE `name` LIKE '%$name%' AND slot > 0 ORDER BY q DESC, name LIMIT $start, $post";

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

