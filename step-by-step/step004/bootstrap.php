<?php

foreach (glob("*.php") as $item) {
    if ($item == "bootstrap.php") {
        continue;
    }
    include_once $item;
}

$request = <<<EOF
GET /wp-content/uploads/2010/03/hello-kitty-darth-vader-pink.jpg HTTP/1.1\r\n
Host: www.kittyhell.com\r\n
Usr-Agent: Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10.6; ja-JP-mac; rv:1.9.2.3) Gecko/20100401 Firefox/3.6.3
Pathtraq/0.9\r\n
Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8\r\n
Accept-Language: ja,en-us;q=0.7,en;q=0.3\r\n
Accept-Encoding: gzip,deflate\r\n
Accept-Charset: Shift_JIS,utf-8;q=0.7,*;q=0.7\r\n
Keep-Alive: 115\r\n
Connection: keep-alive\r\n
Cookie: wp_ozh_wsa_visits=2; wp_ozh_wsa_visit_lasttime=xxxxxxxxxx;
__utma=xxxxxxxxx.xxxxxxxxxx.xxxxxxxxxx.xxxxxxxxxx.xxxxxxxxxx.x;
__utmz=xxxxxxxxx.xxxxxxxxxx.x.x.utmccn=(referral)|utmcsr=reader.livedoor.com|utmcct=/reader/|utmcmd=referral\r\n
\r\n
EOF;

$rawHttp = <<<EOF
HTTP/1.1 100 Continue
HTTP/1.1 200 OK
Date: Tue, 12 Apr 2016 13:58:01 GMT
Server: Apache/2.2.14 (Ubuntu)
X-Powered-By: PHP/5.3.14 ZendServer/5.0
Set-Cookie: ZDEDebuggerPresent=php,phtml,php3; path=/
Set-Cookie: PHPSESSID=6sf8fa8rlm8c44avk33hhcegt0; path=/; HttpOnly
Expires: Thu, 19 Nov 1981 08:52:00 GMT
Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0
Pragma: no-cache
Vary: Accept-Encoding
Content-Encoding: gzip
Content-Length: 192
Content-Type: text/xml
EOF;

$parseRequest = new \Minbaby\HttpParse\ParseRequest($request);
$request = $parseRequest->parse();

var_dump($request);

$response = new \Minbaby\HttpParse\Response(200, "Ok", "HTTP/1.1", '', ["A" => "B"]);

var_dump($response->__toString());
