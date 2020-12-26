<?php


include('./vendor/autoload.php');
use League\Flysystem\Filesystem;
use League\Flysystem\Adapter\Local;
use Afosto\Acme\Client;

$orderId=$_POST["orderId"];
$emailAddress=$_POST["emailAddress"];

//Prepare flysystem
$adapter = new Local('data');
$filesystem = new Filesystem($adapter);
$isselfvalidated=0;
$isorderready=0;

//Construct the client
//MODE_LIVE 
//MODE_STAGING
$client = new Client([
    'username' => $emailAddress,
    'fs'       => $filesystem,
    'mode'     => Client::MODE_LIVE,
]);

$order = $client->getOrder($orderId);

$authorizations = $client->authorize($order);

echo "<br/>In case you missed to download files in previous steps <br/>(load at example.org/.well-known/acme-challenge/*) <br/><br/>";
foreach ($authorizations as $authorization) {
    $file = $authorization->getFile();
   file_put_contents($file->getFilename(), $file->getContents());
echo "<a target='about:blank'  href='download.php?filename=" . $file->getFilename() . "'>Download File</a><br/>";
}


if (!$client->selfTest($authorization, Client::VALIDATION_HTTP)) {
   // throw new \Exception('Could not verify ownership via HTTP');
   echo "Self Could not verify ownership<br/>";
} else {
	
   echo "Self Verified ownership successfully<br/>";
   $isselfvalidated=1;
}


if($isselfvalidated==1) {
foreach ($authorizations as $authorization) {
    $client->validate($authorization->getHttpChallenge(), 15);
}


if ($client->isReady($order)) {
	$isorderready=1;
    //The validation was successful.
	echo "<br/>letsencrypt validation successful<br/><br/>";
	echo "<br/>Paste below stuffs inside your cpanel<br/><br/>";
	$certificate = $client->getCertificate($order);
//file_put_contents('certificate.cert', $certificate->getCertificate());
//file_put_contents('private.key', $certificate->getPrivateKey());
echo "Certificate(CRT+BUNDLE)&nbsp;&nbsp;<br/><textarea rows=30 cols=100>" .  $certificate->getCertificate()  . "</textarea>";
echo "<br/>Private key&nbsp;&nbsp;&nbsp;<br/><textarea rows=30 cols=100>" .  $certificate->getPrivateKey()  . "</textarea>";

echo "<br/>Certificate and key generated";
}

}


?>