<?php
if(($_GET['s']??'')!=='wai2026'){http_response_code(401);die('no');}
header('Content-Type: text/plain');
$c=@fsockopen('127.0.0.1',3000,$e,$s,2);
echo is_resource($c)?"PORT 3000 OPEN\n":"PORT 3000 CLOSED: $s\n";
if(is_resource($c))fclose($c);
$ch=curl_init("http://127.0.0.1:3000/health");
curl_setopt_array($ch,[CURLOPT_RETURNTRANSFER=>true,CURLOPT_TIMEOUT=>3]);
echo "Health: ".(curl_exec($ch)?:curl_error($ch))."\n";
curl_close($ch);
echo "Node: ".(shell_exec('ps aux|grep node|grep -v grep 2>&1')?:"No node\n");
echo "LARAVEL_URL: ".(shell_exec('grep LARAVEL_URL /var/www/whatsapp-ai/bot/.env 2>&1'));
