<?php


include('./vendor/autoload.php');
use League\Flysystem\Filesystem;
use League\Flysystem\Adapter\Local;
use Afosto\Acme\Client;

//Prepare flysystem
$adapter = new Local('data');
$filesystem = new Filesystem($adapter);
$isselfvalidated=0;
$isorderready=0;

$domainName=$_POST["domainName"];
$emailAddress=$_POST["emailAddress"];
$verificationType=$_POST["verificationType"];
$agreeTos=$_POST["agreeTos"];

if($agreeTos=="on"){
echo "Domain " . $domainName . "<br/>";
$dom = explode("," , $domainName);
foreach($dom as $domainvalue) {
    echo $domainvalue . "<br/>";
  }
  
echo "Email " . $emailAddress. "<br/>";
echo "Verification Type " . $verificationType. "<br/>";
echo "<hr/><br/>";



//MODE_LIVE 
//MODE_STAGING

//Construct the client
$client = new Client([
    'username' => $emailAddress,
    'fs'       => $filesystem,
    'mode'     => Client::MODE_LIVE,
]);
sleep(10); 

if($verificationType=="http") {
$order = $client->createOrder([$dom[0], $dom[1]]);
//print_r($order);
//echo "<br/>";
echo "<br/>Download and store file at example.org/.well-known/acme-challenge/* <br/><br/>";

$authorizations = $client->authorize($order);

foreach ($authorizations as $authorization) {
    $file = $authorization->getFile();
    file_put_contents($file->getFilename(), $file->getContents());
echo "<a target='about:blank' href='download.php?filename=" . $file->getFilename() . "'>Download File</a><br/>";
}
?>
<form method="post" action="order-init.php" >
<input id="domainName" type="hidden" name="domainName" value="<?php echo $domainName; ?>">
<input id="emailAddress" type="hidden" name="emailAddress" value="<?php echo $emailAddress; ?>">
<input id="verificationType" type="hidden" name="verificationType" value="<?php echo $verificationType; ?>">
<input id="orderId" type="hidden" name="orderId" value="<?php echo $order->getId(); ?>">
<input type="submit" class="ssl-submit" value="Click After Uploading Files To Generate Certificate">
</form>


<?php 

} else {
	
	?>
	<h3>Note</h3>
	1. Login to your domain host (or wherever service that is "in control" of your domain).<br/><br/>

2. Go to the DNS record settings and create a new TXT record.<br/><br/>

3. In the Name/Host/Alias field, enter the domain TXT record from below table for example: "_acme-challenge".<br/><br/>

4. In the Value/Answer field enter the verfication code from below table.<br/><br/>

5. Wait for few minutes for the TXT record to propagate. You can check if it worked by clicking on the "Check DNS" button. If you have multiple entries, make sure all of them are ok.
<br/><br/>
	<?php 
$order = $client->createOrder([$dom[0], $dom[1]]);
$authorizations = $client->authorize($order);

	foreach ($authorizations as $authorization) {
    $txtRecord = $authorization->getTxtRecord();
    
    //To get the name of the TXT record call:
    //$txtRecord->getName();

    //To get the value of the TXT record call:
    //$txtRecord->getValue();
	echo "Domain TXT record	" . $txtRecord->getName() ."<br/>";
	echo "Value	" . $txtRecord->getValue() ."<br/>";
	echo "<hr/>";
	
}

?>

<form method="post" action="order-init-dns.php" >
<input id="domainName" type="hidden" name="domainName" value="<?php echo $domainName; ?>">
<input id="emailAddress" type="hidden" name="emailAddress" value="<?php echo $emailAddress; ?>">
<input id="verificationType" type="hidden" name="verificationType" value="<?php echo $verificationType; ?>">
<input id="orderId" type="hidden" name="orderId" value="<?php echo $order->getId(); ?>">
<input type="submit" class="ssl-submit" value="Click After Uploading Files To Generate Certificate">
</form>

<?php 
	
}
} else {
	echo "You didnt agree to Terms and conditions";
}
?>