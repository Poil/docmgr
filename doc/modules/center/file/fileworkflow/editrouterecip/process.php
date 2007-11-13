<?
$hideHeader = 1;
$routeId = $_REQUEST["routeId"];

$sql = "SELECT status,account_id,email_notify FROM dm_workflow WHERE id='$routeId'";
$info = single_result($conn,$sql);

$curstatus = $info["status"];
$curaccount = $info["account_id"];
$curnotify = $info["email_notify"];
