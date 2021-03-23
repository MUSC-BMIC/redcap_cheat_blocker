<?php

$duplicate_array = $module->check_for_duplicates($_GET);

$content = json_encode($duplicate_array);
RestUtility::sendResponse(200, $content);

?>
