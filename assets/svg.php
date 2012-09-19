<?
//header("Content-type: image/svg+xml");

$pcb = file_get_contents($_GET['url']);

echo $pcb;

?>