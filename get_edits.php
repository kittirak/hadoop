#!/usr/bin/php
<?php
/* for Hadoop HDFS recovery
use below command for convert namenode metadata to XML
hdfs oev -i /hadoop/name/current/edits... -o /root/edits.xml
*/

$xml=simplexml_load_file($argv[1]) or die("Error: Cannot create object");

/* For each <character> node, we echo a separate <name>. */
foreach ($xml->RECORD as $records) {
	if ($records->OPCODE==('OP_CLOSE')) {
		$block_count=0;
		foreach($records->DATA->BLOCK as $blocks) {
			$block_count++;
			if($block_count > 1) echo ", ";
			echo $blocks->BLOCK_ID;
		}
		echo "\t";
		echo $records->DATA->PERMISSION_STATUS->USERNAME."\t";
		echo $records->DATA->PERMISSION_STATUS->GROUPNAME."\t";
		echo $records->DATA->PERMISSION_STATUS->MODE."\t";
	}
	
	if ($records->OPCODE==('OP_RENAME_OLD')) {
		echo $records->DATA->DST."\n";
	}
}
?>
