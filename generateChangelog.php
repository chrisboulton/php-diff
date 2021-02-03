<?php

use DigiLive\GitChangelog\Renderers\MarkDown;

require 'vendor/autoload.php';

$changelogOptions = [
    'headTagName' => '2.3.1',
    'headTagDate' => '2021-02-03',
    'titleOrder' => 'ASC',
];
$changelogLabels = ['Add', 'Cut', 'Fix', 'Bump', 'Document','Optimize'];


$changeLog = new MarkDown();
$changeLog->commitUrl = 'https://github.com/JBlond/php-diff/commit/{hash}';
$changeLog->issueUrl  = 'https://github.com/JBlond/php-diff/issues/{issue}';
try {
    $changeLog->setOptions($changelogOptions);
} catch (Exception $exception) {
    echo $exception->getMessage();
}
$changeLog->setLabels(...$changelogLabels);
try {
    $changeLog->build();
} catch (Exception $exception) {
    echo $exception->getMessage();
}
$changeLog->save('changelog.md');
