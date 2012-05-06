<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
  <head>
    <meta http-equiv="Content-type" content="text/html; charset=utf-8"/>
    <title>PHP LibDiff - Examples</title>
    <?php
    $theme = isset($_GET['theme']) ? $_GET['theme'] : 'default';
    ?>
    <link rel="stylesheet" href="themes/<?php echo $theme;?>/php-diff.css" type="text/css" charset="utf-8"/>
  </head>
  <body>
    <h1>PHP LibDiff - Examples</h1>
    <h3>Theme: |
    <?php
    $dirs = scandir(__dir__.'/themes/');
    foreach($dirs as $theme_dir) {
      if(is_dir(__dir__.'/themes/'.$theme_dir) && $theme_dir!='.' && $theme_dir!='..') {
        echo '<a href="example.php?theme='.$theme_dir.'">'.$theme_dir.'</a> | ';
      }
    }
    ?></h3>
    <hr />

    <?php
    // Simple autoloader
    function __autoload($class)
    {
      $libPath = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR;
      require_once $libPath . str_replace('_', DIRECTORY_SEPARATOR, $class) . '.php';
    }

    // Include two sample files for comparison
    $a = explode("\n", file_get_contents(dirname(__FILE__).'/a.txt'));
    $b = explode("\n", file_get_contents(dirname(__FILE__).'/b.txt'));

    // Options for generating the diff
    $options = array(
      'context' => 1,
      //'ignoreWhitespace' => true,
      //'ignoreCase' => true,
      //'title_a' => 'some other title than "Old Version"',
      //'title_b' => 'some other title than "New Version"',
    );

    // Initialize the diff class
    $diff = new Diff_Diff($a, $b, $options);

    ?>
    <h2>Side by Side Diff</h2>
    <?php

    // Generate a side by side diff
    echo $diff->render('Html_SideBySide');

    ?>
    <h2>Inline Diff</h2>
    <?php

    // Generate an inline diff
    echo $diff->render('Html_Inline');

    ?>
    <h2>Unified Diff</h2>
    <pre><?php

    // Generate a unified diff
    echo htmlspecialchars($diff->render('Text_Unified'));

    ?>
    </pre>
    <h2>Context Diff</h2>
    <pre><?php

    // Generate a context diff
    echo htmlspecialchars($diff->render('Text_Context'));
    ?>
    </pre>
  </body>
</html>