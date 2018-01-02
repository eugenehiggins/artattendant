<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
	* {FEP-EMAIL-CONTENT} is replaced by the content entered in Front End PM > Settings > Emails
	* Template tags can be used both in that content and/or directly to this template

*/

?>
<!DOCTYPE html>
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
		<title><?php echo get_bloginfo( 'name' ); ?></title>
	</head>
	<body>
	{FEP-EMAIL-CONTENT}
    </body>
</html>

