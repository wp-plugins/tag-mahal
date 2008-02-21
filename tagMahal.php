<?php 
/*
Plugin Name: Tag Mahal
Plugin URI: http://tagger.flaptor.com/tagmahal
Description: This plugin suggests tags for a post using the Flaptor Tagger API. The tags provided by the API are based on an intelligent system that learns from large amounts of content tagged by human authors. As a result, it may suggest words that are not present in the text but are considered relevant to the topic. 
Version: 1.0
Author: Flaptor
Author URI: http://www.flaptor.com
*/

// Hook the retrieveTags function to the ajax action
add_action('wp_ajax_TagMahalFindTags', array('FlaptorTagMahalSuggester','retrieveTags'));
// Hook the printSideBarToolbox function to the dbx_post_sidebar to render the toolbox 
add_action('dbx_post_sidebar', array('FlaptorTagMahalSuggester','printSideBarToolbox'));
// Hook the printJavascript function to the admin_print_scripts action to write the javascript functions in the page (and include SACK library)
add_action('admin_print_scripts', array('FlaptorTagMahalSuggester','printJavascript'));

/**
 * FlaptorTagMahalSuggester - TagMahal Suggester class with all the methods it uses. 
 */
class FlaptorTagMahalSuggester {
	/**
	 * FlaptorTagMahalSuggester.retrieveTags() - Connect to the TagMahal API.
	 * It calls the Flaptor TagMahal API with the special parameter "output=json" to
	 * get a JSON response and parse it directly in javascript.
	 * (hooked with wp_ajax_TagMahalFindTags)
	 */
	function retrieveTags() {
		$text = $_REQUEST['text'];
		$apiHost = "tagger.apis.flaptor.com";
		$apiURL = "/";
		$appKey = "-";
		$text = urlencode($text);
		$blogUrl = get_bloginfo('wpurl');
		$blogName = get_bloginfo( 'name' );		
		$data = "key=" . $appKey . "&input=html&output=json&text=" . $text . "&blogUrl=" . $blogUrl . "&blogName=" . $blogName . "&version=1.0";
	
		$connection = fsockopen($apiHost, 80, $errno, $errstr, 30);
		if (!$connection) die("$errstr ($errno)\n");

		fputs($connection, "POST $apiURL HTTP/1.0\r\n");
		fputs($connection, "Host: $apiHost\r\n");
		fputs($connection, "Content-type: application/x-www-form-urlencoded\r\n");
		fputs($connection, "Content-length: " . strlen($data) . "\r\n");
		fputs($connection, "Accept: */*\r\n");
		fputs($connection, "\r\n");
		fputs($connection, "$data\r\n");
		fputs($connection, "\r\n");

		while ($str = trim(fgets($connection, 4096))) {
		}

		while (!feof($connection)) {
		  $response .= fgets($connection, 4096);
		}
		
		fclose($connection);
		
		die("flaptorTagMahal_showTags(eval(" . $response . "));");
	}

	/**
	 * FlaptorTagMahalSuggester.printSideBarToolbox() - Prints the form for the TagMahal suggester (hooked with dbx_post_sidebar) 
	 */
	function printSideBarToolbox() {
		?>

		<fieldset id="TagMahalSuggester" class="dbx-box">
			<h3 class="dbx-handle"><?php _e('TagMahal Suggester') ?></h3>
			<div class="dbx-content">
				<table width="100%" align="center">
					<tr valign="middle">
						<td valign="middle" align="right">
							<input name="TagMahalSuggester" id="TagMahalSuggesterButton" class="button" type="button" onClick="flaptorTagMahal_loading();flaptorTagMahal_RetrieveTags();" value="<?php _e('Suggest tags') ?>">
						</td>
						<td align="left">
							<a style="border-bottom: 0px" target="_blank" href="http://tagger.flaptor.com/tagmahal"><img title="Powered by Flaptor" border="0" src="http://tagger.flaptor.com/static/images/square_logo.gif"></a> 
						</td>
					</tr>
				</table>
				
				<div id="suggestedTags"></div>
			</div>
		</fieldset>

		<?
	}

	/**
	 * FlaptorTagMahalSuggester.printJavascript() - Print the javascript functions and include the SACK AJAX library.
	 */
	function printJavascript() {
	  // use JavaScript SACK library for AJAX
	  wp_print_scripts( array( 'sack' ));

	  // Define custom JavaScript function
		?>
		<script type="text/javascript">
		//<![CDATA[
		
		/** 
		 * flaptorTagMahal_RetrieveTags() - Call the retrieveTags php method through SACK.
		 */	
		function flaptorTagMahal_RetrieveTags()
		{
		   var adminAjax = new sack("<?php bloginfo( 'wpurl' ); ?>/wp-admin/admin-ajax.php" );    

		  // to get the content of the post while it is being written, it checks if the tinyMCE instance is on or
		  // if it has to get the content from the TextArea.
		  text = "";
		   
		  if (tinyMCE.getInstanceById("content")) {
		  	text = tinyMCE.getInstanceById("content").contentWindow.document.body.innerHTML;
		  } 
		  
		  if (text == "") {
		  	text = document.getElementById("content").value;
		  } 
	
		  adminAjax.execute = 1;
		  adminAjax.method = 'POST';
		  adminAjax.setVar( "action", "TagMahalFindTags" );
		  adminAjax.setVar( "text", text);
		  adminAjax.encVar( "cookie", document.cookie, false );
		  adminAjax.onError = function() { alert('AJAX error in looking up tag suggestions' )};
		  adminAjax.runAJAX();
			
		  return true;
		}
		
		/** 
		 * flaptorTagMahal_showTags - Print the tags suggested in the toolbox using the objects parsed from the JSON. 
		 */
		function flaptorTagMahal_showTags(response) {
			html = "";
			fontWeights = ['xx-small', 'x-small', 'small', 'medium', 'large'];
	
			if(response && response.tags.length > 0) {
				
				html += "<table width='100%' align=\"center\">";
				max = response.tags[0].score;
				for(i = 0; i < response.tags.length; i++) {		
				
					fontSize = fontWeights[Math.round(4 * response.tags[i].score/max)];
						
					html += "<tr><td align=\"center\" width='100%'>";
					html += "<span style='font-size: " + fontSize + "'>";
					html += "<a href=\"javascript:flaptorTagMahal_useTag('" + response.tags[i].name +"')\" style=\"text-decoration: none; border-bottom: 0px\">";
					html += response.tags[i].name;
					html += "</a></span>";
					html += "</td></tr>";
				}
	
				html += "</table>";
	
			} else {
				html = "<table width='100%' align=\"center\">";
				html += "<tr><td align=\"center\" width='100%'>";
				html += "<?php _e('No Tags') ?>";
				html += "</td></tr>";
				html += "</table>";
			}
			suggestedTags = document.getElementById("suggestedTags");
			suggestedTags.innerHTML = html;

			// Remove the "loading" message off the button.
			flaptorTagMahal_loaded();
		}
		
		/** 
		 * flaptorTagMahal_useTag - Adds the specified tag to the end of the tag input field.
		 */
		function flaptorTagMahal_useTag(tag) {
			if (document.getElementById('tags-input').value == "") {
				document.getElementById('tags-input').value = tag;
			} else {
				document.getElementById('tags-input').value += ", " + tag;
			}
		}
		
		/**
		* flaptorTagMahal_loading - Event that indicates that the call to Flaptor TagMahal API is being processed
		*/
	    function flaptorTagMahal_loading() {
	        document.getElementById("TagMahalSuggesterButton").value='Loading...';
	    }
	
		/**
		* flaptorTagMahal_loaded - Event that indicates that the call to Flaptor TagMahal API is already processed
		*/
	    function flaptorTagMahal_loaded() {
	        document.getElementById("TagMahalSuggesterButton").value='Refresh tags';
	    }
	    
		//]]>
		</script>
		<?php
	}
}
?>