curlbro
=======

curl convenience class for php 
 facilitates xpath queries and persistent HTTP sessions via cookies

written by Paul D Bird II 
http://katmore.com
released under 2-clause FreeBSD license

to use:
<?php

$curlbro = new curlbro();

$curlbro->curlexec("http://example.com");

$result = $curlbro->simplexpath("//a");
//(array of SimpleXMLElement) see http://php.net/manual/en/simplexmlelement.xpath.php

echo "<pre>--begin http response--</pre>";
echo "<pre>";
echo nl2br(htmlentities($curlbro->document));
echo "<pre>";
echo "<pre>--end http response--</pre>";
?>
