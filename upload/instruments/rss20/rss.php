<?php

require_once('../../system.php');
DBinit('rss.module');

$title = 'Сайт ' . $_SERVER['SERVER_NAME'];
$desc = 'Новости сайта ' . $_SERVER['SERVER_NAME'];

$rss_doc = '';

$site_news = 'http://' . str_replace('instruments/rss20/rss.php', '', $_SERVER['SERVER_NAME'] . $_SERVER['PHP_SELF']) . 'index.php';

$num_news = getDB()->fetchRow("SELECT COUNT(*) FROM `{$bd_names['news']}`", false, 'num');

if (!(int) $num_news[0])
    exit;

define('DATE_FORMAT_RFC822', 'r');

header("content-type: application/rss+xml; charset=utf-8");

$cur_date = getDB()->fetchRow("SELECT DATE_FORMAT(NOW(),'%a, %d %b %Y %T')", false, 'num');
$cur_date = $cur_date[0];

$result = getDB()->ask("SELECT * FROM `{$bd_names['news']}` ORDER by time DESC LIMIT 0,10");

ob_start();

include './rss_header.html';

while ($line = $result->fetch()) {

    $name = $line['title'];
    $date = date("r", strtotime($line['time']));
    $link = $site_news . '?id=' . $line['id'];
    $post = strip_tags(html_entity_decode($line['message']));

    include './rss.html';
}

include './rss_footer.html';

$rss_doc = '<?xml version="1.0" encoding="UTF-8"?>' . ob_get_clean();

echo $rss_doc;
