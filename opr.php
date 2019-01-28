<?php
require_once 'PlistParser.php';
$plistParser = new PlistParser;
$result = $plistParser->plistToArray($_GET["filename"]);


$filename = "certificate.crt";
$handle = fopen($filename, "r");
$contents = fread($handle, filesize($filename));
fclose($handle);

$crtRawData = openssl_x509_parse($contents,false);
$crtRawData["PayloadDisplayName"] = "Certificate";
$crtRawData["PayloadDescription"] = "Certificate information";
$crtRawData["PayloadType"] = "com.openssl.certificate";
array_push($result["PayloadContent"],arraymerge($crtRawData,""));
echo json_encode($result,true);

function arraymerge($array,$arrayName) {
	$result = array(); 
	foreach ($array as $key => $value) { 
    if (is_array($value)) {
		if($arrayName == "purposes"){
			$result["purpose"] = $result["purpose"].$value[2].",";			
		}else{
			$result = array_merge($result, arraymerge($value,$key));
		}
    }else{
		if($value !== false && $value !== true){
				$result[$key] = $value;
		}	  
    } 
  } 
  return $result; 
} 
//openssl smime -inform DER -verify -in wifi_1.mobileconfig -noverify -out de-signed.mobileconfig  -signer designed.pem
//openssl x509 -in designed.pem -text -noout 
?>