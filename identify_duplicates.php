<?php

$is_duplicate = $module->check_for_duplicates($_GET);
$content = json_encode($is_duplicate);

RestUtility::sendResponse(200, $content);

?>
