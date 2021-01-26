<?php

use DigiLive\GitChangelog\Renderers\MarkDown;

require 'vendor/autoload.php';

$changeLog = new MarkDown();
try {
    $changeLog->build();
} catch (Exception $exception) {
    echo $exception->getMessage();
}
$changeLog->save('changelog.md');
