<?php

require __DIR__ . '/vendor/autoload.php';

$mirrorIP = "98.208.56.56";
//$mirrorIP = "192.168.0.16";
$base = "http://$mirrorIP";
$mailchimp = "907d6c8d55950ce2b1d855aad7f2f09c-us8";
$listId = "d61b64d495";
$identifyingCookie = "FlickerstripLightworkEditor";

/*
$mc = new Mailchimp($mailchimp);
$members = $mc->lists->members($listId);

$emails = array_map(create_function('$o', 'return $o["email"];'), $members['data']);
echo "<pre>";
print_r($emails);
*/

if(!isset($_COOKIE[$identifyingCookie])) {
    $uid = substr( md5(rand()), 0, 60);
    setcookie($identifyingCookie, $uid, time() + (86400 * 30 * 60), "/"); // 86400 = 1 day
} else {
    $uid = $_COOKIE[$identifyingCookie];
}

if (isset($_GET['add'])) {
    $b64 = file_get_contents("php://input");

    $ch = curl_init();
    $url = "$base/add?ip=".$_SERVER['REMOTE_ADDR']."&uid=".$uid;
    if (isset($_GET['email'])) $url .= "&email=".$_GET['email'];
    curl_setopt($ch,CURLOPT_URL,$url);
    curl_setopt($ch,CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST,1);
    curl_setopt($ch, CURLOPT_POSTFIELDS,$b64);

    $data = curl_exec($ch);

    $http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    if ($http_status != 200) {
        http_response_code($http_status);
        return;
    }

    print $data;
} else if (isset($_GET['get'])) {
    $id = $_GET['id'];

    $gifDir = "/tmp/gifcache";
    if (file_exists("$gifDir/$id.gif")) {
        header("Content-Type:image/gif");
        echo readfile("$gifDir/$id.gif");
    } else {
        if (!is_dir("$gifDir")) {
            mkdir("$gifDir", 0777);
        }

        $ch = curl_init();
        $url = "$base/get?id=$id";
        curl_setopt($ch,CURLOPT_URL,$url);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER, 1);
        
        $body = curl_exec($ch);
        $content_type = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if ($httpcode == 200) {
            file_put_contents("$gifDir/$id.gif",$body);

            header("Content-Type:$content_type");
            print $body;
        } else {
            http_response_code($httpcode);
        }
    }
} else if (isset($_GET['check'])) {
    $id = $_GET['id'];

    $ch = curl_init();
    $url = "$base/status?id=$id";
    curl_setopt($ch,CURLOPT_URL,$url);
    curl_setopt($ch,CURLOPT_RETURNTRANSFER, 1);
    
    $body = curl_exec($ch);
    print $body;
} else if (isset($_GET['random'])) {
    $ch = curl_init();
    $url = "$base/random";
    curl_setopt($ch,CURLOPT_URL,$url);
    curl_setopt($ch,CURLOPT_RETURNTRANSFER, 1);
    
    $body = curl_exec($ch);
    print $body;
} else if (isset($_GET['recent'])) {
    $ch = curl_init();
    $url = "$base/recent";
    curl_setopt($ch,CURLOPT_URL,$url);
    curl_setopt($ch,CURLOPT_RETURNTRANSFER, 1);

    $body = curl_exec($ch);

    $content_type = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
    header("Content-Type:$content_type");
    print $body;
}

?>

