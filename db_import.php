<?php

//echo '1111'; exit;
require_once 'vendor/autoload.php';

$db = new MysqliDb('127.0.0.1', 'test', '8iMVdxEIKwdupdP3', 'test');

$connection = new MongoClient();
$collection = $connection->parsing->pravda;
$f = fopen('file.txt', 'w+');

$cursor = $collection->find();
foreach ($cursor as $id => $value)
{

    /* URL */
    if($value['url']) {
        $url = str_replace('http://pravda-sotrudnikov.ru/company/', '', trim($value['url']));
    } else {
        $url = null;
    }

    /* VIEWS */
    if($value['views']) {
        $views = trim($value['views']);
    } else {
        $views = 0;
    }

    /* SITE */
    if($value['site']) {
        $site = trim($value['site']);
    } else {
        $site = null;
    }

    /* EMAIL */
    if($value['email']) {
        $email = trim($value['email']);
    } else {
        $email = null;
    }

    /* LOGO ID FROM pravda-sotrudnikov.ru */
    if($value['company_id']) {
        $logo = trim($value['company_id']);
    } else {
        $logo = null;
    }

    /* NAME */
    if($value['name']) {
        $name = str_replace(array('\n', '\t', '\r', '\\'), '', trim($value['name']));
    } else {
        $name = null;
    }

    /* OTHER NAME TO TITLE */
    if($value['other_name']) {
        $other_name = str_replace(array('\n', '\t', '\r', '\\'), '', trim($value['other_name']));
    } else {
        $other_name = null;
    }

    /* DESCRIPTION TO CONTENT WITH HTML */
    if($value['description']) {
        $description = str_replace(array('\n', '\t', '\r', '\\'), '', trim($value['description']));
    } else {
        $description = null;
    }

    /* PHONE ONLY FIRST */
    if($value['phone']) {
        if(@$value['phone'][0]){
            $phone = str_replace(array('\n', '\t', '\r', '\\'), '', trim($value['phone'][0]));
        } else {
            $phone = null;
        }
    } else {
        $phone = null;
    }

    /* CATEGORY ID FROM DB */
    if($value['category']) {
        $value['category'] = str_replace('Издательства, пресса, Интернет-порталы','Издательства+ пресса+ Интернет-порталы',$value['category']);
        $value['category'] = str_replace('Клубы, гостиницы, кинотеатры','Клубы+ гостиницы+ кинотеатры',$value['category']);
        $value['category'] = str_replace('Финансы: банки, страхование','Финансы: банки+ страхование',$value['category']);
        $cats_name = trim($value['category']);
        $cats_arr = explode(',', $cats_name);
        $cat = trim(str_replace('+', ',', end($cats_arr)));
        $db->where('name', $cat);
        $category = $db->getOne('catalog');
        if(empty($category['id'])) {
            $category_id = null;
        } else {
            $category_id = $category['id'];
        }
    } else {
        $category_id = null;
    }

    /* REGION ID FROM DB AND INSERT WHILE REGIONS */
    if($value['regions']) {
        foreach ($value['regions'] as $region) {
            $region_name = trim($region);
            $db->where('name', $region_name);
            $regions = $db->getOne('city');
            if(empty($regions['id'])) {
                $db->insert('company', [
                    'name' => $name,
                    'title' => $other_name,
                    'category_id' => $category_id,
                    'content' => $description,
                    'logo' => $logo,
                    'city_id' => $region_name,
                    'phone' => $phone,
                    'email' => $email,
                    'site' => $site,
                    'url' => $url,
                    'views' => $views,
                    'disabled' => 0
                ]);
            } else {
                $db->insert('company', [
                    'name' => $name,
                    'title' => $other_name,
                    'category_id' => $category_id,
                    'content' => $description,
                    'logo' => $logo,
                    'city_id' => $regions['id'],
                    'phone' => $phone,
                    'email' => $email,
                    'site' => $site,
                    'url' => $url,
                    'views' => $views,
                    'disabled' => 0
                ]);
            }

        }
    } else {
        $db->insert('company', [
            'name' => $name,
            'title' => $other_name,
            'category_id' => $category_id,
            'content' => $description,
            'logo' => $logo,
            'phone' => $phone,
            'email' => $email,
            'site' => $site,
            'url' => $url,
            'views' => $views,
            'disabled' => 0
        ]);
    }

    fwrite($f, $name . "\n\r");

    /* import cities
    if(is_array(@$value['regions'])) {
        foreach ($value['regions'] as $region) {
            $region_name = trim($region);
            $db->where('name', $region_name);
            $regions = $db->getOne('city');
            if (empty($regions['id'])) {
                $db->insert('city', ['litera' => strtoupper(mb_substr($region_name, 0, 1, 'UTF-8')), 'name' => $region_name]);

            }
            fwrite($f, $value['name'] . ' - ' . $region_name . "\n\r");
        }
    }
    */

    /* import categories
    $value['category'] = str_replace('Издательства, пресса, Интернет-порталы','Издательства+ пресса+ Интернет-порталы',$value['category']);
    $value['category'] = str_replace('Клубы, гостиницы, кинотеатры','Клубы+ гостиницы+ кинотеатры',$value['category']);
    $value['category'] = str_replace('Финансы: банки, страхование','Финансы: банки+ страхование',$value['category']);
    $cats_name = trim($value['category']);
    $cats_arr = explode(',', $cats_name);
    $id = 0;
    foreach($cats_arr as $cat_name){
        $cat = trim(str_replace('+',',',$cat_name));
        $db->where('name', $cat);
        $category = $db->getOne('catalog');
        if(empty($category['id'])) {
            $id = $db->insert('catalog', ['name' => $cat, 'parent_id' => @$id]);
        } else {
            $id = $category['id'];
        }
    }
    */

}

fclose($f);

print_r('999');
