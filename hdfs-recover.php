#!/usr/bin/php
<?php
/* must run updatedb before use this code */

if ( ! isset($argv[1])) {
   $argv[1] = null;
}

if(!file_exists($argv[1])) {
	echo "Usage $argv[0] [INPUT FILE]\n";
	exit(1);
}

$list = file($argv[1]);
$tempConcatfile = "/tmp/hdfsconcat.tmp";

for($i=0;$i<sizeof($list);$i++) {
  $col=explode("\t",$list[$i]);
  $BLKID=$col[0];
  if (!$BLKID=="") {
    if (strpos($BLKID,",")) {
	$blocks=explode(",",$BLKID);
        $BLKPATH=exec("locate $blocks[0] | head -n 1");

	echo "Concat ",sizeof($blocks)," Blocks\n";
	echo "cp $BLKPATH $tempConcatfile \n";	
	system("cp $BLKPATH $tempConcatfile");	

	for($j=1;$j<sizeof($blocks);$j++) {
          $BLKPATH=exec("locate $blocks[$j] | head -n 1");
	  echo ("cat $BLKPATH >> $tempConcatfile \n");	
	  system("cat $BLKPATH >> $tempConcatfile");	
	}
	$BLKID=$tempConcatfile;
    }
    $OWNER=$col[1];
    $GROUP=$col[2];
    $PATH=$col[4];
    $BLKPATH=exec("locate $BLKID | head -n 1");
    echo "hadoop fs -put $BLKPATH $PATH \n";
    echo "hadoop fs -chown $OWNER:$GROUP $PATH \n";

    if(file_exists($tempConcatfile)) {
	unlink($tempConcatfile);
    }
  }
}
?>
