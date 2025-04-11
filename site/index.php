<?php

require_once __DIR__ . '/modules/database.php';
require_once __DIR__ . '/modules/page.php';
require_once __DIR__ . '/config.php';

$db = new Database($config["db"]["path"]);
$page = new Page(__DIR__ . '/templates/index.tpl');

$pageId = isset($_GET['page']) ? intval($_GET['page']) : 1;
$data = $db->Read("page", $pageId);

if (!$data) {
    $data = ["title" => "Page not found", "content" => "Sorry, the page you are looking for does not exist."];
}

echo $page->Render($data);
