<?php

// Working but as a proof of concept, not for production environment due to lack of security checks, 
// no OOP, and no refactoring.

// Also, needs a scheduled tast to weed out any stale sessions, e.g. not logged in for more than x days.
$session_expiration_days = 14;
if (count($argv) > 1) {
    echo "yo more args dude\n";
}

$user = null;
$db_connection = new mysqli("localhost", "username", "password", "database");
function main() {
    global $user;
    echo "<html><body>";
    echo "<script>";
    echo "function url(link) { window.history.pushState({},'', link); }";
    echo "</script>";
    echo "Welcome <a href='mailto:{$user->email}'>{$user->name}</a>!<br>";
    url('/');
    echo "</body></html>";

function url($link) {
    echo "<script>url('{$link}')</script>";
}

if($_GET['login']) {
    $hash = make_hash($_GET['login'], $_SERVER['REMOTE_ADDR']);
    save_hash(3, $hash);
    send_link($_GET['login'], $hash);
    echo "sent a link to your email to log in, check your inbox.";
   die();
}
    
login();
   
function login() {
    global $user;

    $user_id = check_hash()->user_id;
    if ($user_id) {
        $user = get_user($user_id);
        query("UPDATE sessions SET login_at=NOW() WHERE hash='{$_GET['hash']}' LIMIT 1");
        main();
        return;
   }
   echo "No user found.";
}

function send_link($email, $hash) {
    mail($email, 'Sagikos Generator Login Link', "Login link: https://generator.sagikos.com/?hash={$hash}", 'From: Generator@SAGIKOS <generator@generator.sagikos.com>');
}
    
function get_user($user_id) {
    return get("SELECT * FROM users WHERE id='{$user_id}'");
}
    
function make_hash($email, $ip) {
    $string = "$email $ip";
    $hash = password_hash($string, PASSWORD_BCRYPT);
    return $hash;
}
    
function save_hash($user_id, $hash) {
    $query = "INSERT INTO sessions (user_id, hash) VALUES({$user_id}, '{$hash}')";
    query($query);
}

function check_hash() {
    // first check if we have a cookie
    if ($_COOKIE) {
        if ($_COOKIE['hash'] != '') {
            $user = get("SELECT user_id FROM sessions WHERE hash='{$_COOKIE['hash']}'");
            if (!$user) {
                echo "No such user. Cookie";
                die();
            }
        }
    }
    if (!$user) {
        $user = get("SELECT user_id FROM sessions WHERE hash='{$_GET['hash']}'");
        if (!$user) {
            echo "Login? <form type=post>Email: <input name=login type=email required><button type=submit>Send</button></form>";
            die();
        }
        setcookie("hash", $_GET['hash'], time()+60*60*24*14);
    }
    return $user;
}

function get($query) {
    global $db_connection;
    return $db_connection->query($query)->fetch_object();
}
    
function query($query) {
    global $db_connection;
    $results = $db_connection->query($query);
    if ($db_connection->error) { echo $query . "\n<hr>"; dd($db_connection->error); }
    return $results;
}
    
function dd($text) {
    d($text);
    die();
}

function d($text) {
    var_dump($text);
}

?>