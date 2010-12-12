<?php
/*
 * Name: Holistic Networking Blurb
 * Description: Just a simple little class that outputs a standard HN blurb across all plugins. Because I'm a whore.
 */

$blurb	= new HolisticNetworkingBlurb;

class HolisticNetworkingBlurb {
	
	function __construct() {
		?>
		<div style="display:block; float:right; width:200px; background:#ffffff; border-radius:20px; -moz-border-radius:20px; padding: 5px; border: 2px solid #555555;">
			<h2>Holistic Networking</h2>
			<p>Thanks for using this Holistic Networking plugin. I hope you enjoy it and get good use out of it. Please consider <a href="http://twitter.com/tbelknap">following me</a> on Twitter. You can also check out Holistic Networking on FaceBook, <a href="http://www.facebook.com/pages/HolisticNetworking-Web-Design-WordPress-LAMP-and-CakePHP/118048434879611">here</a>.</p>
		</div>
		<?php
	}
}
?>
