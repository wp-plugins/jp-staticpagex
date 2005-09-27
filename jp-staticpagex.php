<?php
/*
Plugin Name: Static Page eXtended
Version: 1.0
Plugin URI: http://jp.jixor.com/plugins/static-page-extended/
Author: Stephen Ingram
Author URI: http://jp.jixor.com/
Description: Static files may replace static pages. This is done via a filter so don't worry no templates to edit. You may also do inline includes and inline PHP. (Inline PHP is buggy and dissabled by default.) You may turn functionality on/off and decide what level users must be to access various functionality in their authoring. <strong><a href="edit.php?page=jp-staticpagex.php">Manage Static Page eXtended</a> | <a href="options-general.php?page=jp-staticpagex.php">Static Page eXtended Options</a></strong>
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

// ISSUES:

// I have tested the plugin a little and have not been able to generate any problems, however I expect there may be a few bugs.

// TO DO:

// Add redirects feature, so if you move/delete a page you can automatically redirect, or show a redirect link.
//   The problem with this is that it seems that pages are wayyy too reliant on the .htaccess rewrite rules, this
//   is because if they were more generic it would require better handeling php side. I think they should make such
//   a change, a massive set of rewrite rules isn't the best system.
//   That said what about adding redirects if the page still exists?!
//     Easy to mark a page for redirect, hard to hide that page from the pages navigation without tedious manual
//     templating work, maybe, requires investigation.

// Add page redirects to article with id, most recent article in x ategory, or most recent article with x title.

class JPSPX {

function JPSPX()
{

	$this->get_option_jpspx();

	// ADD THE FILTER/ACTIONS
	add_filter('the_content', array(&$this, 'jp_staticpagex'), 1);
	add_action('admin_menu', array(&$this, 'jp_spx_addmenu'));
	
}

// ---------------------------------------------------------

function get_option_jpspx()
{

	$jpspxopt = get_option('JPSPX');

	if (empty($jpspxopt['JPSPX_MODIFY_PLUGIN_OPTIONS']))		$jpspxopt['JPSPX_MODIFY_PLUGIN_OPTIONS'] = 9; // User level of 9 required by default to modify plugin options.
	if (!array_key_exists('JPSPX_REPLACE_CONTENT',$jpspxopt))	$jpspxopt['JPSPX_REPLACE_CONTENT'] = TRUE;
	if (empty($jpspxopt['JPSPX_REPLACE_CONTENT_USER_LEVEL']))	$jpspxopt['JPSPX_REPLACE_CONTENT_USER_LEVEL'] = 8;
	if (empty($jpspxopt['JPSPX_REPLACE_CONTENT_FOLDER']))		$jpspxopt['JPSPX_REPLACE_CONTENT_FOLDER'] = 'wp-content/staticpages/';
	if (!array_key_exists('JPSPX_INLINE_INCLUDES',$jpspxopt))	$jpspxopt['JPSPX_INLINE_INCLUDES'] = TRUE;
	if (empty($jpspxopt['JPSPX_INLINE_INCLUDES_USER_LEVEL']))	$jpspxopt['JPSPX_INLINE_INCLUDES_USER_LEVEL'] = 8;
	if (!array_key_exists('JPSPX_EVAL_PHP',$jpspxopt))		$jpspxopt['JPSPX_EVAL_PHP'] = FALSE;
	if (empty($jpspxopt['JPSPX_EVAL_PHP_USER_LEVEL']))		$jpspxopt['JPSPX_EVAL_PHP_USER_LEVEL'] = 8;

	$this->jpspxopt = $jpspxopt;
	return true;

}

// ------------------------------------------------------------

function jp_spx_addmenu()
{
	add_options_page('\'JP-Static Page eXtended\' Options', 'Static Page eXtended', 0, 'jp-staticpagex.php', array(&$this, 'jp_spx_options'));
	add_management_page('Manage \'JP-Static Page eXtended\'', 'Static Page eXtended', 0, 'jp-staticpagex.php', array(&$this, 'jp_spx_manage'));
}

// ------------------------------------------------------------

function jp_spx_folder()
{

	global $action;

	$folder = $this->jpspxopt['JPSPX_REPLACE_CONTENT_FOLDER'];

	if ($action == 'createdefaultfolder') {

		if (!file_exists('wp-content/staticpages/')) {

			if(is_writable('wp-content/')) {
				umask(0000);
				if(!@mkdir('wp-content/staticpages/')) { // tatatee reported problems manipulation files so I'm going to explicitly state widest access to try to avoid this issue.

					?>

<div class="wrap">
<h2>Manage 'JP-Static Page eXtended': Create Folder</h2>

<p>
I should have been able to create the &quot;(Your WordPress root)/wp-content/staticpages/&quot; folder for you, however an unkonwn error occured.
</p>

</div>
					<?php
					return false;
				} else {

					?>

<div class="wrap">
<h2>Manage 'JP-Static Page eXtended': Create Folder</h2>

<p>
I have created the folder &quot;(Your WordPress root)/wp-content/staticpages/&quot; for you. You can now create content replacment pages for use with JP-Static Page eXtended.
</p>

</div>
					<?php

					$this->jpspxopt['JPSPX_REPLACE_CONTENT_FOLDER'] = 'wp-content/staticpages/';
					update_option('JPSPX',$this->jpspxopt);
					return true;

				}

			} else {

				?>

<div class="wrap">
<h2>Manage 'JP-Static Page eXtended': Create Folder</h2>

<p>
<strong>An error has occured.</strong> The folder &quot;(Your WordPress root)/wp-content/&quot; is not writable so I was not able to create the folder for you. You can either chmod wp-content to 666 or create staticpages manually. If you create it manually ensure you chmod it to 666.
</p>

</div>
				<?php
				return false;
			}

		} else {

			?>
<div class="wrap">
<h2>Manage 'JP-Static Page eXtended': Create Folder</h2>

<p>
The default folder already existed so I just reset the folder option.
</p>

</div>
			<?php

			$this->jpspxopt['JPSPX_REPLACE_CONTENT_FOLDER'] = 'wp-content/staticpages/';
			update_option('JPSPX',$this->jpspxopt);
			return true;

		}
	
	}

	if(!file_exists($folder)) {
		?>

<div class="wrap">
<h2>Manage 'JP-Static Page eXtended': Create Folder</h2>

<p>
The content replacment folder specified, &quot;<?php echo $folder ?>&quot;, doesn't exist. If you would like me to attempt to create the default folder (&quot;(Your WordPress root)/wp-content/staticpages/&quot;) and reset the folder option for you continue to; <a href="<?php echo $_SERVER['PHP_SELF'] ?>?page=jp-staticpagex.php&amp;action=createdefaultfolder" title="Create default staticpages folder.">Create default staticpages folder</a>.
</p>

</div>

		<?php
		return false;

	} elseif ( !is_writable($folder) ) {
		?>

<div class="wrap">
<h2>Manage 'JP-Static Page eXtended': Warning</h2>

<p>
The folder &quot;<?php echo $this->jpspxopt['JPSPX_REPLACE_CONTENT_FOLDER'] ?>&quot; is not writable, this means you won't be able to create new static pages.
</p>

</div>

		<?php
		return false;
	}
	return true;

}

// ------------------------------------------------------------

function jp_spx_manage()
{

	chdir('../');

	global $user_level, $action, $create, $delete;

	if ($user_level >= $this->jpspxopt['JPSPX_REPLACE_CONTENT_USER_LEVEL']) {

		if ( !$this->jp_spx_folder() ) return false;

		if ($action == 'delete') {

			?>

<div class="updated">
<p><strong>Manage 'JP-Static Page eXtended': Delete Page</strong></p>

			<?php
			if (!@unlink($this->jpspxopt['JPSPX_REPLACE_CONTENT_FOLDER'] . $delete . '.php')) {
				?>

<p>
Unable to delete the static page file &quot;<?php echo $delete ?>.php&quot;.
</p>

				<?php
			} else {
				?>

<p>
Deleted the static page file &quot;<?php echo $delete ?>.php&quot;.
</p>

				<?php
			}
			?>

</div>

			<?php

		} elseif ($action == 'create') { // END: DELETE PAGE, BEGIN: CREATE PAGE

			?>

<div class="updated">
<p><strong>Manage 'JP-Static Page eXtended': Create New Page</strong></p>

			<?php
			if(!file_exists($this->jpspxopt['JPSPX_REPLACE_CONTENT_FOLDER'] . $create . '.php')) {
				if(!$file_handler = fopen($this->jpspxopt['JPSPX_REPLACE_CONTENT_FOLDER'] . $create . '.php', 'wb')) {
					?>

<p>
Unable to create the static page file &quot;<?php echo $create ?>.php&quot;.
</p>

					<?php
				} else {
					fwrite($file_handler,"\n"); // To avoid error when opening for editing (Warning: fread(): Length parameter must be greater than 0. in...)
					fclose($file_handler);
					chmod($this->jpspxopt['JPSPX_REPLACE_CONTENT_FOLDER'] . $create . '.php', 0666); // re: tatatee problem above.
					?>

<p>
Created the static page file &quot;<?php echo $create ?>.php&quot;.
</p>

					<?php
				}
			} else {
?>

<p>
Unable to create the static page file &quot;<?php echo $create ?>.php&quot; as one already exists.
</p>

				<?php
			}
			?>
</div>

			<?php

		}

		?>

<div class="wrap">
<h2>Manage 'JP-Static Page eXtended': Content Replacement Bindings</h2>

<p>
<strong>Note:</strong> To modify plugin options see <a href="options-general.php?page=jp-staticpagex.php" title="Modify JPSPX Options">Options &gt; Static Page eXtended</a>.
</p>

<p>
Remember a content replacement file will completley replace any content created on it's relevant static page. If you want you can manually create files with post ids and this will work on regular posts also.
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
<tr class="' . $class . '">
<td>' . $page->ID . '</td><td>' . $page->post_title . '</td>';

	if (file_exists($this->jpspxopt['JPSPX_REPLACE_CONTENT_FOLDER'] . $page->ID . '.php')) {
		echo '
<td><a href="templates.php?file=' . $this->jpspxopt['JPSPX_REPLACE_CONTENT_FOLDER'] . $page->ID . '.php" class="edit">Edit</a></td>
<td><a href="' . $_SERVER['PHP_SELF'] . '?page=jp-staticpagex.php&amp;action=delete&amp;delete=' . $page->ID . '" class="delete" onclick="return confirm(\'You are about to delete the static page file \\\'' . $page->ID . '.php\\\' for \\\''.$page->post_title.'\\\'\n  \\\'OK\\\' to delete, \\\'Cancel\\\' to stop.\')">Delete</a></td>';
	} else {
		echo '
<td colspan="2"><a href="' . $_SERVER['PHP_SELF'] . '?page=jp-staticpagex.php&amp;action=create&amp;create=' . $page->ID . '" class="edit">Create Content Replacement File</a></td>';
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
<h2>Manage 'JP-Static Page eXtended': Content Replacemant User Level</h2>
<p>
Your user current level is not high enough to access this function.
</p>
</div>

		<?php
	} // END 'JPSPX_REPLACE_CONTENT_USER_LEVEL' CONTROLLED SECTION
	chdir('wp-admin/');

}

// ------------------------------------------------------------

function jp_spx_options()
{

	// Make cwd the wp root.
	chdir('../');

	global $user_level, $action;

	if ($user_level >= $this->jpspxopt['JPSPX_MODIFY_PLUGIN_OPTIONS']) {

		if ( ($action == 'modifypluginoptions') and (!empty($_POST)) ) {
			$numopts = array('JPSPX_MODIFY_PLUGIN_OPTIONS', 'JPSPX_REPLACE_CONTENT_USER_LEVEL', 'JPSPX_INLINE_INCLUDES_USER_LEVEL', 'JPSPX_EVAL_PHP_USER_LEVEL');
			$switchopts = array('JPSPX_REPLACE_CONTENT', 'JPSPX_INLINE_INCLUDES', 'JPSPX_EVAL_PHP');
			$stringopts = array('JPSPX_REPLACE_CONTENT_FOLDER');

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

			$notallowed =	'·ÈÌÛ˙‡ËÏÚ˘‰ÎÔˆ¸¡…Õ”⁄¿»Ã“ŸƒÀœ÷‹‚ÍÓÙ˚¬ Œ‘€ÒÁ«(){}[]|!@#$%^&:;<>';
			$allowed =	'aeiouaeiouaeiouaeiouaeiouaeiouaeiouaeiouncc..................';

			foreach ($stringopts as $key) {
				if (array_key_exists($key,$_POST)) {
					$jpspxopt[$key] = strtr($_POST[$key], $notallowed, $allowed);
				}
			}

			update_option('JPSPX',$jpspxopt);

			$this->jpspxopt = $jpspxopt;

			$jpspxoptupdated = TRUE;

		}

		$this->jp_spx_folder();

		if ($jpspxoptupdated) {
			?>

<div class="updated">
<p><strong>Options were successfully updated.</strong></p>
</div>

			<?php
		}

		?>

<div class="wrap">
<h2>Manage 'JP-Static Page eXtended': Options</h2>

<p>
<strong>Note:</strong> To modify content replacment bindings see <a href="edit.php?page=jp-staticpagex.php" title="Modify JPSPX Content Replacment Bindings.">Manage &gt; Static Page eXtended</a>.
</p>

<form method="post" action="options-general.php?page=jp-staticpagex.php&amp;action=modifypluginoptions" enctype="multipart/form-data" name="JPSPXOPT">

	<p>Be carefull who you assign these abilities to, all of them enable users to execute php within 'the loop'.</p>

	<p><label for="JPSPX_MODIFY_PLUGIN_OPTIONS" style="font-weight:bold;">User level required to modify this plugin's options.</label><br /><input type="text" name="JPSPX_MODIFY_PLUGIN_OPTIONS" id="JPSPX_MODIFY_PLUGIN_OPTIONS" value="<?php echo $this->jpspxopt['JPSPX_MODIFY_PLUGIN_OPTIONS'] ?>" /></p>

	<fieldset class="options" style="margin-top:2em;">
		<legend>Content Replacement Options</legend>

		<p><input type="checkbox" name="JPSPX_REPLACE_CONTENT" id="JPSPX_REPLACE_CONTENT"<?php if ($this->jpspxopt['JPSPX_REPLACE_CONTENT']) echo 'checked'; ?> /><label for="JPSPX_REPLACE_CONTENT" style="font-weight:bold;margin-left:4px;">Enable replace content functionality.</label></p>

		<p><label for="JPSPX_REPLACE_CONTENT_USER_LEVEL" style="font-weight:bold;display:block;margin-bottom:2px;">User level required to modify content replacement options.</label><input type="text" name="JPSPX_REPLACE_CONTENT_USER_LEVEL" id="JPSPX_REPLACE_CONTENT_USER_LEVEL" value="<?php echo $this->jpspxopt['JPSPX_REPLACE_CONTENT_USER_LEVEL'] ?>" /></p>

		<p><label for="JPSPX_REPLACE_CONTENT_FOLDER" style="font-weight:bold;display:block;margin-bottom:2px;">Folder where static pages for 'bindings' are stored.</label><input style="width:20em;" type="text" name="JPSPX_REPLACE_CONTENT_FOLDER" id="JPSPX_REPLACE_CONTENT_FOLDER" value="<?php echo $this->jpspxopt['JPSPX_REPLACE_CONTENT_FOLDER'] ?>" /></p>

	</fieldset>

	<fieldset class="options" style="margin-top:2em;">
		<legend>Inline Includes Options</legend>

		<p>Inline includes enables users to write include statments that will attach an external file where they are placed. They may use the syntax:<br />
		<code>&lt;!--#include file=&quot;path to your file from WordPress root&quot; --&gt;</code></p>

		<p><input type="checkbox" name="JPSPX_INLINE_INCLUDES" id="JPSPX_INLINE_INCLUDES"<?php if ($this->jpspxopt['JPSPX_INLINE_INCLUDES']) echo 'checked'; ?> /><label for="JPSPX_INLINE_INCLUDES" style="font-weight:bold;margin-left:4px;">Enable inline includes functionality.</label></p>

		<p><label for="JPSPX_INLINE_INCLUDES_USER_LEVEL" style="font-weight:bold;display:block;margin-bottom:2px;">User level required to create working inline includes.</label><input type="text" name="JPSPX_INLINE_INCLUDES_USER_LEVEL" id="JPSPX_INLINE_INCLUDES_USER_LEVEL" value="<?php echo $this->jpspxopt['JPSPX_INLINE_INCLUDES_USER_LEVEL'] ?>" /></p>

	</fieldset>

	<fieldset class="options" style="margin-top:2em;">
		<legend>Inline PHP Options</legend>

		<p>Inline PHP Enables users to put php code directly into their articles. They may use the syntax:<br />
		<code>&lt;!--PHP<br />
		(php code here)<br />
		--&gt;</code>
		</p>

		<p>This functionality is not fully implemented, you can do the odd thing but its very buggy, use at own risk.</p>

		<p><input type="checkbox" name="JPSPX_EVAL_PHP" id="JPSPX_EVAL_PHP"<?php if ($this->jpspxopt['JPSPX_EVAL_PHP']) echo 'checked'; ?> /><label for="JPSPX_EVAL_PHP" style="font-weight:bold;margin-left:4px;">Enable inline PHP functionality.</label></p>

		<p><label for="JPSPX_EVAL_PHP_USER_LEVEL" style="font-weight:bold;display:block;margin-bottom:2px;">User level required to create working inline PHP.</label><input type="text" name="JPSPX_EVAL_PHP_USER_LEVEL" id="JPSPX_EVAL_PHP_USER_LEVEL" value="<?php echo $this->jpspxopt['JPSPX_EVAL_PHP_USER_LEVEL'] ?>" /></p>

	</fieldset>

	<p class="submit"><input type="submit" name="submit" value="Modify Plugin Options &raquo;" /></p>

</form>

</div>

<?php
	} else {
		?>

<div class="wrap">
<h2>Manage 'JP-Static Page eXtended': Options User Level</h2>
<p>
Your user current level is not high enough to modify plugin options.
</p>
</div>

		<?php
	}

	chdir('wp-admin/');

}

// ------------------------------------------------------------

function jp_staticpagex($content) // THE FILTER
{

	global $post;

	// Get level of the user that created the article, if there is a better way let me know.
	$articleuserlevel = get_userdata($post->post_author);
	$articleuserlevel = $articleuserlevel->user_level;

	if (($this->jpspxopt['JPSPX_REPLACE_CONTENT']) and ($articleuserlevel >= $this->jpspxopt['JPSPX_REPLACE_CONTENT_USER_LEVEL'])) {

		if (file_exists($this->jpspxopt['JPSPX_REPLACE_CONTENT_FOLDER'] . $post->ID . '.php')) {
			include($this->jpspxopt['JPSPX_REPLACE_CONTENT_FOLDER'] . $post->ID . '.php');
			$content = '<!-- JP-StaticPageX content replacment applied to this article. -->';
			return $content;
		}

	}

	if (($this->jpspxopt['JPSPX_INLINE_INCLUDES']) and ($articleuserlevel >= $this->jpspxopt['JPSPX_INLINE_INCLUDES_USER_LEVEL'])) {

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

	if (($this->jpspxopt['JPSPX_EVAL_PHP']) and ($articleuserlevel >= $this->jpspxopt['JPSPX_EVAL_PHP_USER_LEVEL'])) {

	// Your php's output will be placed as if it were there in the first place.
	// As such most wp filtering will be applied to it. So it will be wrapped in <p></p> and stuff.
	// There is probally going to be a few big problems with this function, but remember this is an
	// alpha version, if that, for a reason.

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

// ------------------------------------------------------------

function jp_spx_redirect($option) // Redirect the content
{

	// Either a redirect via id, category or topic.
	// Still deciding on format, could make it slim by calling the function with a static page file, however that creates a set of problems.
	// May be able to redirect entierly to a different article and apply correct templating, would have to investigate, don't really want to use
	//   meta redirects for that.

}

// ------------------------------------------------------------

} // End class definition.

$JPSPX = new JPSPX;

?>
