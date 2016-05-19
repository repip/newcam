<?php
require_once('../connection/connect.php');
include("class_lib.php");


$url = "http://www.chaletmoia.it/webcam/";
$content = file_get_contents($url);
preg_match('/a href=\"(.*)cgi.*\"/', $content, $matches);
$urllago = $matches[1] . "record/current.jpg?rand=" . rand(0, 999999);
$lago = new snapshot("lago", $urllago);
$lago->doSnapshot();


$test=new snapshot("lago",$urllago);
$snaps=$test->getSnaps();
foreach( $snaps as $key => $value) {
  echo "<br />".$value['tm'];
  echo "<br />".$value['file'];
}
echo "<br />".$test->count();
