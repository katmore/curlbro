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
