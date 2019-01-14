<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8"/>
		<title>PHP LibDiff - Examples</title>
		<link rel="stylesheet" href="styles.css" type="text/css" charset="utf-8"/>
	</head>
	<body>
		<h1>PHP LibDiff - Examples</h1>
		<hr />
		<?php

		// Include the diff class
		require_once dirname(__FILE__).'/../lib/Diff.php';

		// Include two sample files for comparison
		$a = explode("\n", file_get_contents(dirname(__FILE__).'/a.txt'));
		$b = explode("\n", file_get_contents(dirname(__FILE__).'/b.txt'));

		// Options for generating the diff
		$options = array(
			//'ignoreWhitespace' => true,
			//'ignoreCase' => true,
		);

		// Initialize the diff class
		$diff = new \jblond\Diff($a, $b, $options);

		?>
		<h2>Side by Side Diff</h2>
		<?php

		// Generate a side by side diff
		require_once dirname(__FILE__).'/../lib/Diff/Renderer/Html/SideBySide.php';
        $renderer = new \jblond\Diff_Renderer_Html_SideBySide(array(
            'title_a' => 'Custom title for OLD version',
            'title_b' => 'Custom title for NEW version',
        ));
		echo $diff->Render($renderer);

		?>
		<h2>Inline Diff</h2>
		<?php

		// Generate an inline diff
		require_once dirname(__FILE__).'/../lib/Diff/Renderer/Html/Inline.php';
		$renderer = new \jblond\Diff_Renderer_Html_Inline;
		echo $diff->render($renderer);

		?>
		<h2>Unified Diff</h2>
		<pre><?php

		// Generate a unified diff
		require_once dirname(__FILE__).'/../lib/Diff/Renderer/Text/Unified.php';
		$renderer = new \jblond\Diff_Renderer_Text_Unified;
		echo htmlspecialchars($diff->render($renderer));

		?>
		</pre>
		<h2>Context Diff</h2>
		<pre><?php

		// Generate a context diff
		require_once dirname(__FILE__).'/../lib/Diff/Renderer/Text/Context.php';
		$renderer = new \jblond\Diff_Renderer_Text_Context;
		echo htmlspecialchars($diff->render($renderer));
		?>
		</pre>
	</body>
</html>
