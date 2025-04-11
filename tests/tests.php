<?php

require_once __DIR__ . '/testframework.php';

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../modules/database.php';
require_once __DIR__ . '/../modules/page.php';

$tests = new TestFramework();

//
// TESTS FOR Database
//
function testDbConnection() {
    global $config;
    $db = new Database($config["db"]["path"]);
    return $db instanceof Database;
}

function testDbCount() {
    global $config;
    $db = new Database($config["db"]["path"]);
    $db->Execute("DELETE FROM page");
    return $db->Count("page") === 0;
}

function testDbCreate() {
    global $config;
    $db = new Database($config["db"]["path"]);
    $data = ["title" => "Test Title", "content" => "Test Content"];
    $id = $db->Create("page", $data);
    return is_int($id) && $id > 0;
}

function testDbRead() {
    global $config;
    $db = new Database($config["db"]["path"]);
    $data = ["title" => "Read Title", "content" => "Read Content"];
    $id = $db->Create("page", $data);
    $row = $db->Read("page", $id);
    return $row['title'] === "Read Title" && $row['content'] === "Read Content";
}

function testDbUpdate() {
    global $config;
    $db = new Database($config["db"]["path"]);
    $id = $db->Create("page", ["title" => "Old Title", "content" => "Old Content"]);
    $db->Update("page", $id, ["title" => "New Title", "content" => "New Content"]);
    $row = $db->Read("page", $id);
    return $row['title'] === "New Title" && $row['content'] === "New Content";
}

function testDbDelete() {
    global $config;
    $db = new Database($config["db"]["path"]);
    $id = $db->Create("page", ["title" => "To Delete", "content" => "This will go"]);
    $db->Delete("page", $id);
    $row = $db->Read("page", $id);
    return empty($row);
}

//
// TEST FOR Page
//
function testPageRender() {
    $template = __DIR__ . '/../templates/index.tpl';
    file_put_contents($template, "<h1>{{title}}</h1><p>{{content}}</p>");
    $page = new Page($template);
    $html = $page->Render(["title" => "Hello", "content" => "World"]);
    return strpos($html, "<h1>Hello</h1>") !== false && strpos($html, "<p>World</p>") !== false;
}

//
// Register tests
//
$tests->add('Database connection', 'testDbConnection');
$tests->add('Database count()', 'testDbCount');
$tests->add('Database create()', 'testDbCreate');
$tests->add('Database read()', 'testDbRead');
$tests->add('Database update()', 'testDbUpdate');
$tests->add('Database delete()', 'testDbDelete');
$tests->add('Page render()', 'testPageRender');

//
// Run and output results
//
$tests->run();

echo $tests->getResult();
