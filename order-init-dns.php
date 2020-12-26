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


foreach ($authorizations as $authorization) {
    $txtRecord = $authorization->getTxtRecord();
    
	echo "Domain TXT record	" . $txtRecord->getName() ."<br/>";
	echo "Value	" . $txtRecord->getValue() ."<br/>";
	echo "<hr/>";
	

}

if (!$client->selfTest($authorization, Client::VALIDATION_DNS)) {
    //throw new \Exception('Could not verify ownership via DNS');
	echo "<br/><br/>Could not verify ownership via DNS<br/><br/>";
	 $isselfvalidated=1;
} else  {
	 echo "Self Verified ownership successfully<br/>";
	$isselfvalidated=1;
}


sleep(30); // this further sleep is recommended, depending on your DNS provider, see below
//$isselfvalidated=1;
if($isselfvalidated==1) {
foreach ($authorizations as $authorization) {
    $client->validate($authorization->getDnsChallenge(), 15);
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
} else {
	echo "Isnt validated ";
}

}





?>