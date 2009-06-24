<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
	<head>
		<meta http-equiv="Content-type" content="text/html; charset=utf-8"/>
		<title>PHP LibDiff - Examples</title>
		<link rel="stylesheet" href="styles.css" type="text/css" charset="utf-8"/>
	</head>
	<body>
		<h1>PHP LibDiff - Examples</h1>
		<hr />
		<?php

		// Include the diff class
		require_once dirname(__FILE__).'/../difflib.php';

		// Include two sample files for comparison
		$a = explode("\n", file_get_contents(dirname(__FILE__).'/a.txt'));
		$b = explode("\n", file_get_contents(dirname(__FILE__).'/b.txt'));

		// Options for generating the diff
		$options = array(
		);

		// Initialize the diff class
		$diff = new DiffLib($a, $b, $options);

		?>
		<h2>Side by Side Diff</h2>
		<?php

		// Generate a side by side diff
		require_once dirname(__FILE__).'/../renderer/sidebyside_html.php';
		$renderer = new DiffLib_Renderer_SideBySide_Html;
		echo $diff->Render($renderer);

		?>
		<h2>Inline Diff</h2>
		<?php

		// Generate an inline diff
		require_once dirname(__FILE__).'/../renderer/inline_html.php';
		$renderer = new DiffLib_Renderer_Inline_Html;
		echo $diff->Render($renderer);

		?>
		<h2>Unified Diff</h2>
		<pre><?php

		// Generate a unified diff
		require_once dirname(__FILE__).'/../renderer/unified.php';
		$renderer = new DiffLib_Renderer_Unified;
		echo htmlspecialchars($diff->Render($renderer));

		?>
		</pre>
		<h2>Context Diff</h2>
		<pre><?php

		// Generate a context diff
		require_once dirname(__FILE__).'/../renderer/context.php';
		$renderer = new DiffLib_Renderer_Context;
		echo htmlspecialchars($diff->Render($renderer));
		?>
		</pre>
	</body>
</html>