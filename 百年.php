<pre>
<?php
$json= file_get_contents("http://bcel1985.blogspot.hk/feeds/posts/default?alt=json");

$json_a = json_decode($json);
//	print_r($json_a->{"feed"}->{"entry"}[0]);
echo "更新時間 ".$json_a->{"feed"}->{"entry"}[0]->{"published"}->{'$t'}."</br>";
$htmlContent = $json_a->{"feed"}->{"entry"}[0]->{"content"}->{'$t'};

		
	$dom = new DOMDocument();
	$dom->loadHTML('<?xml encoding="utf-8" ?>' .$htmlContent);
	
	 //discard white space 
  $dom->preserveWhiteSpace = false; 

  //the table by its tag name
  $tables = $dom->getElementsByTagName('table'); 

  //get all rows from the table
  $rows = $tables->item(0)->getElementsByTagName('tr'); 

$i =0;
  // loop over the table rows
  foreach ($rows as $row) 
  { 
   // get each column by tag name
      $cols = $row->getElementsByTagName('td'); 
	  $heds = $row->getElementsByTagName('th'); 
   // echo the values  
	if($i>=2 && (isset($_GET["curr"]) == FALSE || strpos($heds->item(0)->nodeValue,$_GET["curr"]) !== false)){
	  echo $heds->item(0)->nodeValue;
      echo ",買入參考價(CASH):".$cols->item(0)->nodeValue; 
      echo ",賣出參考價(CASH):".$cols->item(1)->nodeValue; 
      echo ",賣出參考價(T/T&D/D:)".$cols->item(2)->nodeValue.'<br />';
	}
	$i = $i +1;
	} 

?>

</pre>