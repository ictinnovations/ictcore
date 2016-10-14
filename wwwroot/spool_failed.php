<?php
// remove wwwroot
include_once "../core/core.php";

$spool_id = $_POST['spool_id'];
$call_id = $_POST['call_id'];

$spool_status = $_POST['spool_status'];
$spool_error = $_POST['spool_error'];
$last_updated = isset($_POST['last_updated']) ? $_POST['last_updated'] : time();

$oSpool = new Spool($spool_id);

$data = array(
    'status' => $spool_status,
    'error' => $spool_error,
);

$output = $oSpool->process('spool_failed', $data);
?>
