<?php

$file = 'CheatBlocker_data_dictionary.csv';
$path = $module->getURL($file);

header('Content-Type: application/csv');
header('Content-Disposition: attachment; filename="'.$file.'"');
header('Content-Transfer-Encoding: binary');
header('Accept-Ranges: bytes');

@readfile($path);

?>
