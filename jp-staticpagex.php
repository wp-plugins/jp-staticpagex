<?php
/*
Plugin Name: Static Page eXtended
Version: Beta 3
Plugin URI: http://jp.jixor.com/plugins/static-page-extended/
Author: Stephen Ingram
Author URI: http://jp.jixor.com/
Description: External pages may replace static pages. This is done via a filter so don't worry no templates to edit. You may also do inline includes and inline PHP. You may turn functionality on/off and decide what level users must be to access various functionality in their authoring. <strong><a href="/wp-content/plugins/jp-staticpagex.php">Manage Static Page Files and Options</a></strong>
*/

/* ************************************************************************

JP_STATICPAGEX License (based on BSD license)
Copyright (c) 2005, jp.jixor.com, Stephen Ingram
All rights reserved.

	PREAMBLE

  Feel free to modify the source providing you stick the the following
conditions. Please also inform me of any modifications you make so that I
may possibly add them to the release on my site. I will credit authors
where appropriate. License may change. However this is fairly simple,
basically don't claim its your own and I'm not responsible for your use of
it.

---------------------------------------------------------------------------

	LICENSE

Redistribution and use in source and binary forms, with or without
modification, are permitted provided that the following conditions are met:

1) Redistributions of source code must retain the above copyright notice,
   this list of conditions and the following disclaimer.

2) Neither the name of jixor.com, nor the names of its
   contributors may be used to endorse or promote products derived from this
   software without specific prior written permission.

THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE
ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE
LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR
CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF
SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN
CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
POSSIBILITY OF SUCH DAMAGE.

************************************************************************ */

// -- BEGIN ADMIN MODE --

if (basename($_SERVER['SCRIPT_FILENAME']) == 'jp-staticpagex.php') {

chdir('../../wp-admin/');

require_once('admin.php');
$title = __('Manage \'JP-Static Page eXtended\'');
$parent_file = 	'plugins.php';

require_once('./admin-header.php');

$jpspxopt = get_option('JPSPX');
if (!$jpspxopt['JPSPX_MODIFY_PLUGIN_OPTIONS']) $jpspxopt['JPSPX_MODIFY_PLUGIN_OPTIONS'] = 9; // User level of 9 required by default to modify plugin options.
if (!$jpspxopt['JPSPX_REPLACE_CONTENT']) $jpspxopt['JPSPX_REPLACE_CONTENT'] = TRUE;
if (!$jpspxopt['JPSPX_REPLACE_CONTENT_USER_LEVEL']) $jpspxopt['JPSPX_REPLACE_CONTENT_USER_LEVEL'] = 9;
if (!$jpspxopt['JPSPX_INLINE_INCLUDES']) $jpspxopt['JPSPX_INLINE_INCLUDES'] = TRUE;
if (!$jpspxopt['JPSPX_INLINE_INCLUDES_USER_LEVEL']) $jpspxopt['JPSPX_INLINE_INCLUDES_USER_LEVEL'] = 9;
if (!$jpspxopt['JPSPX_EVAL_PHP']) $jpspxopt['JPSPX_EVAL_PHP'] = TRUE;
if (!$jpspxopt['JPSPX_EVAL_PHP_USER_LEVEL']) $jpspxopt['JPSPX_EVAL_PHP_USER_LEVEL'] = 9;

// -- BEGIN SETUP TESTS --

if ($user_level >= $jpspxopt['JPSPX_MODIFY_PLUGIN_OPTIONS']) {

if ($action == 'modifypluginoptions') {
	$numopts = array('JPSPX_MODIFY_PLUGIN_OPTIONS', 'JPSPX_REPLACE_CONTENT_USER_LEVEL', 'JPSPX_INLINE_INCLUDES_USER_LEVEL', 'JPSPX_EVAL_PHP_USER_LEVEL');
	$switchopts = array('JPSPX_REPLACE_CONTENT', 'JPSPX_INLINE_INCLUDES', 'JPSPX_EVAL_PHP');

	foreach ($numopts as $key) {
		if (array_key_exists($key,$_POST)) {
			if ((is_numeric($_POST[$key])) and ($_POST[$key] < 11) and ($_POST[$key] > -1)) {
				$jpspxopt[$key] = (int) $_POST[$key];
			}
		}
	}

	foreach ($switchopts as $key) {
		if (array_key_exists($key,$_POST)) {
			$jpspxopt[$key] = TRUE;
		} else {
			$jpspxopt[$key] = FALSE;
		}
	}

	update_option('JPSPX',$jpspxopt);

	$jpspxopt = get_option('JPSPX');

	$jpspxoptupdated = TRUE;

}

}

if(!file_exists('../wp-content/staticpages/')) {
	// The required folder 'staticpages' doesn't exist.
?>

<div class="wrap">
<h2>Manage 'JP-Static Page eXtended': Create Folder</h2>

<p>
The folder &quot;(Your WordPress root)/wp-content/staticpages/&quot; doesn't exist. I will attempt to create it for you...
</p>

<?php
	if(is_writable('../wp-content/')) {
		if(!@mkdir('../wp-content/staticpages/', 0777)) { // tatatee reported problems manipulation files so I'm going to explicitly state widest access to try to avoid this issue.
?>

<p>
I should have been able to create the &quot;staticpages&quot; folder for you, however an unkonwn error occured.
</p>

<?php
		} else {
?>

<p>
I have created the folder &quot;staticpages&quot; for you. You can now create pages for use with JP-Static Page eXtended.
</p>

<?php
		}
	} else {
?>

<p>
<strong>An error has occured.</strong> The folder &quot;(wordpress)/wp-content/&quot; is not writable so I was not able to create the folder for you. You can either chmod wp-content to 666 or create staticpages manually. If you create it manually ensure you chmod it to 666.
</p>

<?php
	}
?>

</div>

<?php
}

if (!is_writable('../wp-content/staticpages/')) {
?>

<div class="wrap">
<h2>Manage 'JP-Static Page eXtended': Warning</h2>

<p>
The folder &quot;(Your WordPress root)/wp-content/staticpages/&quot; is not writable, this means you won't be able to create new pages.
</p>

<?php
}

// -- END SETUP TESTS --

// -- BEGIN ADMIN FUNCTIONS --

if ($user_level >= $jpspxopt['JPSPX_REPLACE_CONTENT_USER_LEVEL']) {

if ($action == 'delete') {

?>

<div class="wrap">
<h2>Manage 'JP-Static Page eXtended': Delete Page</h2>

<?php
	if (!unlink('../wp-content/staticpages/'.$delete.'.php')) {
?>

<p>
Unable to delete the static page file for &quot;<?php echo $delete ?>&quot;.
</p>

<?php
	} else {
?>

<p>
Deleted the static page file for &quot;<?php echo $delete ?>&quot;.
</p>

<?php
	}
?>

</div>

<?php

} elseif ($action == 'create') { // END: DELETE PAGE, BEGIN: CREATE PAGE

?>

<div class="wrap">
<h2>Manage 'JP-Static Page eXtended': Create New Page</h2>

<?php
	if(!file_exists('../wp-content/staticpages/'.$create.'.php')) {
		if(!$file_handler = fopen('../wp-content/staticpages/'.$create.'.php', 'wb')) {
?>

<p>
Unable to create the static page file for &quot;<?php echo $create ?>&quot;.
</p>

<?php
		} else {
			fwrite($file_handler,"\n"); // To avoid error when opening for editing (Warning: fread(): Length parameter must be greater than 0. in...)
			fclose($file_handler);
			chmod('../wp-content/staticpages/'.$create.'.php', 0777); // re: tatatee problem above.
?>

<p>
Created the static page file for &quot;<?php echo $create ?>&quot;.
</p>

<?php
		}
	} else {
?>

<p>
Unable to create the static page file for &quot;<?php echo $create ?>&quot; as one already exists.
</p>

<?php
	}
?>
</div>

<?php

} // END: CREATE PAGE

?>

<div class="wrap">
<h2>Manage 'JP-Static Page eXtended': Bindings</h2>

<p>
Remember a static page file will overwrite any content created on that static page.
</p>

<p>
You don't have to replace entire pages with a static file, you can also do inline includes. An inline include will include your file right where you put the include statement, leaving the post intact. It even works for normal blog posts! The syntax is as follows:<br />
<code>&lt;!--#include file=&quot;(path to your file from WordPress root)&quot; --&gt;</code>
</p>

<table width="100%" cellpadding="3" cellspacing="3">
<thead>
<tr>
<th>ID</th><th>Title</th><th colspan="3">Admin</th>
</tr>
</thead>
<tbody>

<?php 
$pages = get_pages();

foreach($pages as $page) {
	$class = ('alternate' == $class) ? '' : 'alternate';

	echo '
<tr class="'.$class.'">
<td>'.$page->ID.'</td><td>'.$page->post_title.'</td>';

	if (file_exists('../wp-content/staticpages/'.$page->post_name.'.php')) {
		echo '
<td><a href="../../wp-admin/templates.php?file=wp-content/staticpages/'.$page->post_name.'.php" class="edit">edit</a></td>
<td><a href="?action=delete&amp;delete='.$page->post_name.'" class="delete" onclick="return confirm(\'You are about to delete the static page file for \\\''.$page->post_title.'\\\'\n  \\\'OK\\\' to delete, \\\'Cancel\\\' to stop.\')">delete</a></td>';
	} else {
		echo '
<td colspan="2"><a href="?action=create&amp;create='.$page->post_name.'" class="edit">create static page x</a></td>';
	}

	echo '
</tr>';

}
?>

</tbody>
</table>

</div>

<?php
} else {
?>

<div class="wrap">
<h2>Manage 'JP-Static Page eXtended': Content Replacmant User Level</h2>
<p>
Your user current level is not high enough to access this function.
</p>
</div>

<?php
} // END 'JPSPX_REPLACE_CONTENT_USER_LEVEL' CONTROLLED SECTION

if ($user_level >= $jpspxopt['JPSPX_MODIFY_PLUGIN_OPTIONS']) {

if ($jpspxoptupdated) {
?>

<div class="wrap">
<h2>Manage 'JP-Static Page eXtended': Options Updated</h2>
<p>Options were successfully updated.</p>
</div>

<?php
}

?>

<div class="wrap">
<h2>Manage 'JP-Static Page eXtended': Options</h2>

<form method="post" action="?action=modifypluginoptions" enctype="multipart/form-data" name="JPSPXOPT">

	<p><label for="JPSPX_MODIFY_PLUGIN_OPTIONS" style="font-weight:bold;">User level required to modify this plugin's options.</label><br /><input type="text" name="JPSPX_MODIFY_PLUGIN_OPTIONS" id="JPSPX_MODIFY_PLUGIN_OPTIONS" value="<?php echo $jpspxopt['JPSPX_MODIFY_PLUGIN_OPTIONS'] ?>" /></p>

	<fieldset class="options" style="margin-top:2em;">
		<legend>Content Replacment Options</legend>

		<p><input type="checkbox" name="JPSPX_REPLACE_CONTENT" id="JPSPX_REPLACE_CONTENT"<?php if ($jpspxopt['JPSPX_REPLACE_CONTENT']) echo 'checked'; ?> /><label for="JPSPX_REPLACE_CONTENT" style="font-weight:bold;margin-left:4px;">Enable replace content functionality.</label></p>

		<p><label for="JPSPX_REPLACE_CONTENT_USER_LEVEL" style="font-weight:bold;display:block;margin-bottom:2px;">User level required to modify content replacment options.</label><input type="text" name="JPSPX_REPLACE_CONTENT_USER_LEVEL" id="JPSPX_REPLACE_CONTENT_USER_LEVEL" value="<?php echo $jpspxopt['JPSPX_REPLACE_CONTENT_USER_LEVEL'] ?>" /></p>

	</fieldset>

	<fieldset class="options" style="margin-top:2em;">
		<legend>Inline Includes Options</legend>

		<p>Inline includes enables users to write include statments that will attach an external file where they are placed. They may use the syntax:<br />
		<code>&lt;!--#include file=&quot;path to your file from WordPress root&quot; --&gt;</code></p>

		<p><input type="checkbox" name="JPSPX_INLINE_INCLUDES" id="JPSPX_INLINE_INCLUDES"<?php if ($jpspxopt['JPSPX_INLINE_INCLUDES']) echo 'checked'; ?> /><label for="JPSPX_INLINE_INCLUDES" style="font-weight:bold;margin-left:4px;">Enable inline includes functionality.</label></p>

		<p><label for="JPSPX_INLINE_INCLUDES_USER_LEVEL" style="font-weight:bold;display:block;margin-bottom:2px;">User level required to create working inline includes.</label><input type="text" name="JPSPX_INLINE_INCLUDES_USER_LEVEL" id="JPSPX_INLINE_INCLUDES_USER_LEVEL" value="<?php echo $jpspxopt['JPSPX_INLINE_INCLUDES_USER_LEVEL'] ?>" /></p>

	</fieldset>

	<fieldset class="options" style="margin-top:2em;">
		<legend>Inline PHP Options</legend>

		<p>Inline PHP Enables users to put php code directly into their articles. They may use the syntax:<br />
		<code>&lt;!--PHP<br />
		(php code here)<br />
		--&gt;</code><br />
		Just be carefull who you assign this ability to!</p>

		<p><input type="checkbox" name="JPSPX_EVAL_PHP" id="JPSPX_EVAL_PHP"<?php if ($jpspxopt['JPSPX_EVAL_PHP']) echo 'checked'; ?> /><label for="JPSPX_EVAL_PHP" style="font-weight:bold;margin-left:4px;">Enable inline PHP functionality.</label></p>

		<p><label for="JPSPX_EVAL_PHP_USER_LEVEL" style="font-weight:bold;display:block;margin-bottom:2px;">User level required to create working inline PHP.</label><input type="text" name="JPSPX_EVAL_PHP_USER_LEVEL" id="JPSPX_EVAL_PHP_USER_LEVEL" value="<?php echo $jpspxopt['JPSPX_EVAL_PHP_USER_LEVEL'] ?>" /></p>

	</fieldset>

	<p class="submit"><input type="submit" name="submit" value="Modify Plugin Options &raquo;" /></p>

</form>

</div>

<?php
} // END 'JPSPX_MODIFY_PLUGIN_OPTIONS' CONTROLLED SECTION

include("admin-footer.php");

}

// -- END ADMIN MODE --

// THE FILTER
function jp_staticpagex($content) {

	global $post;

	$jpspxopt = get_option('JPSPX');
	if (!$jpspxopt['JPSPX_MODIFY_PLUGIN_OPTIONS']) $jpspxopt['JPSPX_MODIFY_PLUGIN_OPTIONS'] = 9; // User level of 9 required by default to modify plugin options.
	if (!$jpspxopt['JPSPX_REPLACE_CONTENT']) $jpspxopt['JPSPX_REPLACE_CONTENT'] = TRUE;
	if (!$jpspxopt['JPSPX_REPLACE_CONTENT_USER_LEVEL']) $jpspxopt['JPSPX_REPLACE_CONTENT_USER_LEVEL'] = 9;
	if (!$jpspxopt['JPSPX_INLINE_INCLUDES']) $jpspxopt['JPSPX_INLINE_INCLUDES'] = TRUE;
	if (!$jpspxopt['JPSPX_INLINE_INCLUDES_USER_LEVEL']) $jpspxopt['JPSPX_INLINE_INCLUDES_USER_LEVEL'] = 9;
	if (!$jpspxopt['JPSPX_EVAL_PHP']) $jpspxopt['JPSPX_EVAL_PHP'] = TRUE;
	if (!$jpspxopt['JPSPX_EVAL_PHP_USER_LEVEL']) $jpspxopt['JPSPX_EVAL_PHP_USER_LEVEL'] = 9;

	// Get level of the user that created the article, if there is a better way let me know.
	$articleuserlevel = get_userdata($post->post_author);
	$articleuserlevel = $articleuserlevel->user_level;

	if (($jpspxopt['JPSPX_REPLACE_CONTENT']) and ($articleuserlevel >= $jpspxopt['JPSPX_REPLACE_CONTENT_USER_LEVEL'])) {

		if (file_exists('wp-content/staticpages/'.$post->post_name.'.php')) {
			include('wp-content/staticpages/'.$post->post_name.'.php');
			$content = '<!-- JP-StaticPageX content replacment applied to this article. -->';
			return $content;
		}

	}

	if (($jpspxopt['JPSPX_INLINE_INCLUDES']) and ($articleuserlevel >= $jpspxopt['JPSPX_INLINE_INCLUDES_USER_LEVEL'])) {

		if (preg_match('|<!--#include file="(.*?)".*-->|i',$content,$inlineincludes)) {
			unset($inlineincludes[0]);
			foreach($inlineincludes as $include) {
				ob_start();
				include($include);
				$includedoutput = ob_get_clean();//ob_get_contents();
				//ob_end_clean();
				$content = preg_replace('|(<!--#include file="'.$include.'" -->)|i',$includedoutput,$content);
			}
		}

	}

	if (($jpspxopt['JPSPX_EVAL_PHP']) and ($articleuserlevel >= $jpspxopt['JPSPX_EVAL_PHP_USER_LEVEL'])) {

	// Your php's output will be placed as if it were there in the first place.
	// As such most wp filtering will be applied to it. So it will be wrapped in <p></p> and stuff.
	// There is probally going to be a few big problems with this function, but remember this is a
	// beta version for a reason.

		if (preg_match('|(<!--PHP.*?-->)|is',$content,$evalphp)) {
			unset($evalphp[0]);
			foreach($evalphp as $phpsnip) {
				$phptoexec = substr($phpsnip, 7, -3);
				ob_start();
				eval($phptoexec);
				$output = ob_get_clean();
				if ($output) {
					$content = preg_replace('|('.$phpsnip.')|is',$output,$content);
				} else {
					$content = preg_replace('|('.$phpsnip.')|is','<p>The php you attempted to include failed.</p>',$content);
				}
			}
		}

	}

	return $content;

}

// ADD THE FILTER
add_filter('the_content', 'jp_staticpagex',1);

?>
