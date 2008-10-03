<?php

/**
 * Plugin Name: Static Page eXtended
 * Version: 2.1
 * Plugin URI: http://jp.jixor.com/plugins/static-page-extended
 * Author: Stephen Ingram
 * Author URI: http://jp.jixor.com
 * Description: Static files may replace static pages, this is done via a filter. Posts and pages may redirect via id, category or title. You may also create inline includes. Please visit the setup page once activated: <a href="options-general.php?page=jp-staticpagex.php">Static Page eXtended Options</a>.
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

/**
 * JPSPX class runs all the plugins functionallity and administrative screens.
 *
 * @author Stephen Ingram <code@jixor.com>
 * @copyright Copyright (c) 2008, Stephen Ingram
 * @package JPSPX
 * @category plugin
 */
class JPSPX {

	/**
	 * @var array
	 */
	var $jpspxopt;



	/**
	 * PHP4 style constructor, adds filters, options, etc.
	 *
	 * @return void
	 */
    function JPSPX()
    {

    	$this->get_option_jpspx();

        // ADD THE FILTER/ACTIONS
        add_filter('the_content', array(&$this, 'jp_staticpagex'), 1);
        add_action('admin_menu', array(&$this, 'jp_spx_addmenu'));
        if ($this->jpspxopt['JPSPX_REDIRECT'])
            add_action('template_redirect', array(&$this, 'jp_spx_redirect'));

		return;

    }



    /**
     * Gets options, fills with defaults if not specified.
     *
     * @return void
     */
    function get_option_jpspx()
    {

        $jpspxopt = get_option('JPSPX');

        if (!isset($jpspxopt['JPSPX_MODIFY_PLUGIN_OPTIONS'])
            || empty($jpspxopt['JPSPX_MODIFY_PLUGIN_OPTIONS'])
            )
            $jpspxopt['JPSPX_MODIFY_PLUGIN_OPTIONS'] = 9; // User level of 9 required by default to modify plugin options.

		if (!isset($jpspxopt['JPSPX_REPLACE_CONTENT']))
            $jpspxopt['JPSPX_REPLACE_CONTENT'] = false;

		if (!isset($jpspxopt['JPSPX_REPLACE_CONTENT_USER_LEVEL'])
            || empty($jpspxopt['JPSPX_REPLACE_CONTENT_USER_LEVEL'])
            )
            $jpspxopt['JPSPX_REPLACE_CONTENT_USER_LEVEL'] = 8;

        if (!isset($jpspxopt['JPSPX_REPLACE_CONTENT_FOLDER'])
            || empty($jpspxopt['JPSPX_REPLACE_CONTENT_FOLDER'])
            )
            $jpspxopt['JPSPX_REPLACE_CONTENT_FOLDER'] = 'wp-content/staticpages/';

        if (!isset($jpspxopt['JPSPX_INLINE_INCLUDES']))
            $jpspxopt['JPSPX_INLINE_INCLUDES'] = false;

        if (!isset($jpspxopt['JPSPX_INLINE_INCLUDES_USER_LEVEL'])
            || empty($jpspxopt['JPSPX_INLINE_INCLUDES_USER_LEVEL'])
            )
            $jpspxopt['JPSPX_INLINE_INCLUDES_USER_LEVEL'] = 8;

        if (!isset($jpspxopt['JPSPX_EVAL_PHP']))
            $jpspxopt['JPSPX_EVAL_PHP'] = false;

        if (!isset($jpspxopt['JPSPX_EVAL_PHP_USER_LEVEL'])
            || empty($jpspxopt['JPSPX_EVAL_PHP_USER_LEVEL'])
            )
            $jpspxopt['JPSPX_EVAL_PHP_USER_LEVEL'] = 8;

        if (!isset($jpspxopt['JPSPX_REDIRECT']))
            $jpspxopt['JPSPX_REDIRECT'] = false;

        if (!isset($jpspxopt['JPSPX_REDIRECT_USER_LEVEL'])
            || empty($jpspxopt['JPSPX_REDIRECT_USER_LEVEL'])
            )
            $jpspxopt['JPSPX_REDIRECT_USER_LEVEL'] = 8;

        $this->jpspxopt = $jpspxopt;

        return;

    }



    function jp_spx_addmenu()
    {

        add_options_page(
            'Static Page eXtended Options',
            'SPX',
            0,
            'jp-staticpagex.php',
            array(&$this, 'jp_spx_options')
            );

        add_management_page(
            'Manage Static Page eXtended',
            'SPX',
            0,
            'jp-staticpagex.php',
            array(&$this, 'jp_spx_manage')
            );

    }



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

<div class="error"><p>
The content replacment folder specified, &quot;<?php echo $folder ?>&quot;,
doesn't exist. If you would like to create the default folder try
<a href="<?php echo $_SERVER['PHP_SELF'] ?>?page=jp-staticpagex.php&amp;action=createdefaultfolder" title="Create default staticpages folder.">Create default staticpages folder</a>.
</p></div>

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

		global $user_level;

		if (isset($_GET['edit']) && (int)$_GET['edit']) {
			echo $this->jp_spx_edit_crf((int)$_GET['edit']);
			return;
		}

		if ( !empty($_GET['action']) ) $action = $_GET['action'];
		if ( !empty($_GET['delete']) ) $delete = (int)$_GET['delete'];
		if ( !empty($_GET['create']) ) $create = (int)$_GET['create'];

		if ($user_level >= $this->jpspxopt['JPSPX_REPLACE_CONTENT_USER_LEVEL']) {

			if (!$this->jp_spx_folder())
				return;

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
<h2>Manage Static Page eXtended Content Replacement Bindings</h2>

<p>
<strong>Note:</strong> To modify plugin options see <a href="options-general.php?page=jp-staticpagex.php" title="Modify JPSPX Options">Settings &gt; SPX</a>.
</p>

<p>
Remember a content replacement file will completley replace any content created on it's relevant static page. If you want you can manually create files with post ids and this will work on regular posts also.
</p>

<p>
You don't have to replace entire pages with a static file, you can also do inline includes. An inline include will include your file right where you put the include statement, leaving the post intact. It even works for normal blog posts! The syntax is as follows:<br />
<code>&lt;!--#include file=&quot;(path to your file from WordPress root)&quot; --&gt;</code>
</p>

<p>
Note on templating/styling:<br />
On the default template the body of an article (posts and pages) is enclosed within a div with its class set to &quot;entrytext&quot;, you may want to include this in your file to ensure pages are inline with the styling of normal articles.
</p>

<table class="widefat">
	<thead>
		<tr>
			<th scope="col">ID</th>
			<th scope="col">Title</th>
			<th scope="col">File</th>
			<th scope="col" colspan="2">Admin</th>
		</tr>
	</thead>
	<tbody>

		<?php 
$pages = get_pages();

foreach($pages as $page) {
	$class = ('alternate' == $class) ? '' : 'alternate';

	echo '
<tr class="' . $class . '">
<th scope="row">' . $page->ID . '</th><td>' . $page->post_title . '</td><td>' . $this->jpspxopt['JPSPX_REPLACE_CONTENT_FOLDER'] . $page->ID . '.php</td>';

	if (file_exists($this->jpspxopt['JPSPX_REPLACE_CONTENT_FOLDER'] . $page->ID . '.php')) {
		echo '
<td><a href="' . $_SERVER['PHP_SELF'] . '?page=jp-staticpagex.php&amp;edit=' . $page->ID . '" class="edit">Edit</a></td>
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

	function jp_spx_edit_crf($id)
	{

		$file = $id . '.php';

		$filepath = $this->jpspxopt['JPSPX_REPLACE_CONTENT_FOLDER'] . $file;

		if (!file_exists($filepath))
			return 'File, &quot;' . $file . '&quot; doesn\'t exist!';

		if (!is_readable($filepath))
			return 'File, &quot;' . $file . '&quot; is not readable!';

		if (!empty($_POST)
			&& isset($_POST['file-content'])
			) {

			$filecontent = (get_magic_quotes_gpc()
				? stripslashes((string)$_POST['file-content'])
				: (string)$_POST['file-content']
				);

			file_put_contents($filepath, $filecontent);

			$out .= '<div id="message" class="updated fade"><p>File edited successfully.</p></div>';

	    }

		$out .= '<div class="wrap"><h2>Edit Content Replacement File</h2>
			<div class="tablenav">
				<big><strong>' . $file . '</strong></big>
			</div>

			<div>&nbsp;</div>

			<form method="POST"
				action="edit.php?page=jp-staticpagex.php&amp;edit=' . $id . '">
			<textarea cols="120" rows="25" tabindex="1" name="file-content">'
			. htmlentities(file_get_contents($filepath))
			. '</textarea>
			<p class="submit">
				<input type="submit" name="submit" value="Update File"
					tabindex="2" />
			</p>
			</form></div>';

	    return $out;

	}

    function jp_spx_options()
    {

	// Make cwd the wp root.
	chdir('../');

	global $user_level;

	if ( !empty($_GET['action']) ) $action = $_GET['action'];

	if ($user_level >= $this->jpspxopt['JPSPX_MODIFY_PLUGIN_OPTIONS']) {

		if ( ($action == 'modifypluginoptions') and (!empty($_POST)) ) {
			$numopts = array('JPSPX_MODIFY_PLUGIN_OPTIONS', 'JPSPX_REPLACE_CONTENT_USER_LEVEL', 'JPSPX_INLINE_INCLUDES_USER_LEVEL', 'JPSPX_EVAL_PHP_USER_LEVEL', 'JPSPX_REDIRECT_USER_LEVEL');
			$switchopts = array('JPSPX_REPLACE_CONTENT', 'JPSPX_INLINE_INCLUDES', 'JPSPX_EVAL_PHP', 'JPSPX_REDIRECT');
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
<h2>Static Page eXtended Options</h2>

<p>
<strong>Note:</strong> To modify content replacement bindings see <a href="edit.php?page=jp-staticpagex.php" title="Modify JPSPX Content Replacment Bindings.">Manage &gt; SPX</a>.
</p>

<p>
<strong>Warning:</strong> If you have a caching plugin installed you should only
use the redirect and inline PHP functionallity, the includes will interfear with
caching.
</p>

<form method="post" action="options-general.php?page=jp-staticpagex.php&amp;action=modifypluginoptions" enctype="multipart/form-data" name="JPSPXOPT">

	<p>Be carefull who you assign these abilities to, all of them enable users to execute php within 'the loop'.</p>

    <table class="form-table"><tbody>
		<tr class="form-field form-required">
			<th scope="row" valign="top"><label for="JPSPX_MODIFY_PLUGIN_OPTIONS">User level required to modify this plugin's options.</label></th>
			<td><input type="text" name="JPSPX_MODIFY_PLUGIN_OPTIONS" id="JPSPX_MODIFY_PLUGIN_OPTIONS" value="<?php echo $this->jpspxopt['JPSPX_MODIFY_PLUGIN_OPTIONS'] ?>" />
				<br />Reccommended setting of 9, 10 is the super administrator.</td>
		</tr>
	</tbody></table>

	<h3>Content Replacement Options</h3>

	<p>
		The content of pages is ignored, with the contents of a php file being
		used. Certainly you need to be sure that you trus user's who are allowed
		access to this functionallity.
	</p>

    <table class="form-table"><tbody>
		<tr class="form-field form-required">
			<th scope="row" valign="top"></th>
			<td><input type="checkbox" name="JPSPX_REPLACE_CONTENT" id="JPSPX_REPLACE_CONTENT"<?php if ($this->jpspxopt['JPSPX_REPLACE_CONTENT']) echo 'checked'; ?> /><label for="JPSPX_REPLACE_CONTENT">Enable replace content functionality.</label></td>
		</tr>
		<tr class="form-field form-required">
			<th scope="row" valign="top"><label for="JPSPX_REPLACE_CONTENT_USER_LEVEL">User level required to modify content replacement options.</label></th>
			<td><input type="text" name="JPSPX_REPLACE_CONTENT_USER_LEVEL" id="JPSPX_REPLACE_CONTENT_USER_LEVEL" value="<?php echo $this->jpspxopt['JPSPX_REPLACE_CONTENT_USER_LEVEL'] ?>" /></td>
		</tr>
		<tr class="form-field form-required">
			<th scope="row" valign="top"><label for="JPSPX_REPLACE_CONTENT_FOLDER">Folder where content replacement files are stored.</label></th>
			<td><input style="width:20em;" type="text" name="JPSPX_REPLACE_CONTENT_FOLDER" id="JPSPX_REPLACE_CONTENT_FOLDER" value="<?php echo $this->jpspxopt['JPSPX_REPLACE_CONTENT_FOLDER'] ?>" />
				<br />Defaults to &quot;wp-content/staticpages/&quot;, ensure that there is a trailing /</td>
		</tr>
	</tbody></table>



	<h3>Redirection Options</h3>

	<p>
		Redirect enables you to make redirects based on article ID, category or
		title. Category and title will redirect to the most recent match. Great
		for a plugin site, the plugin page may redirect to the newest release of
		that plugin without you having to edit the redirect setting!
		<br />Use this format, specify only one value:
	</p>
	<dl>
		<dt>Key</dt>
		<dd>redirect</dd>
		<dt>Value</dt>
		<dd>ID:[specity a valid post id, this can be a post or page]</dd>
		<dd>CATEGORY:[specify a valid category slug]</dd>
		<dd>TITLE:[specify a valid title]</dd>
	</dl>
	<p>
		This information is entered into the &quot;Custom Fields&quot; section
		of an article (post or page).
	</p>

    <table class="form-table"><tbody>
		<tr class="form-field form-required">
			<th scope="row" valign="top"></th>
			<td><input type="checkbox" name="JPSPX_REDIRECT" id="JPSPX_REDIRECT"<?php if ($this->jpspxopt['JPSPX_REDIRECT']) echo 'checked'; ?> /><label for="JPSPX_REDIRECT">Enable redirects functionality.</label></td>
		</tr>
		<tr class="form-field form-required">
			<th><label for="JPSPX_REDIRECT_USER_LEVEL">User level required to create working redirects.</label></th>
			<td><input type="text" name="JPSPX_REDIRECT_USER_LEVEL" id="JPSPX_REDIRECT_USER_LEVEL" value="<?php echo $this->jpspxopt['JPSPX_REDIRECT_USER_LEVEL'] ?>" />
				<br />User level applied to original article, not the article it redirects to.</td>
		</tr>
	</tbody></table>



	<h3>Inline Includes Options</h3>

	<p>
		Inline includes enables users to write include statments that will
		attach an external file where they are placed. They may use the
		syntax:<br />
		<code>&lt;!--#include file=&quot;path to your file from WordPress root&quot; --&gt;</code>
	</p>

    <table class="form-table"><tbody>
		<tr class="form-field form-required">
			<th scope="row" valign="top"></th>
			<td><input type="checkbox" name="JPSPX_INLINE_INCLUDES"
			id="JPSPX_INLINE_INCLUDES"<?php if ($this->jpspxopt['JPSPX_INLINE_INCLUDES']) echo 'checked'; ?> />
		<label for="JPSPX_INLINE_INCLUDES">Enable inline includes functionality.</label></td>
		</tr>
		<tr class="form-field form-required">
			<th><label for="JPSPX_INLINE_INCLUDES_USER_LEVEL">User level required to create working inline includes.</label></th>
			<td><input type="text" name="JPSPX_INLINE_INCLUDES_USER_LEVEL" id="JPSPX_INLINE_INCLUDES_USER_LEVEL" value="<?php echo $this->jpspxopt['JPSPX_INLINE_INCLUDES_USER_LEVEL'] ?>" /></td>
		</tr>
	</tbody></table>



	<h3>Inline PHP Options</h3>

	<p>
		Inline PHP Enables users to put php code directly into their articles.
		They must use the syntax:
	</p>
	<pre>
&lt;?php
	(PHP code &mdash; If you use caching ensure your php code does not utilize output buffering)
?&gt;</pre>

	<p>
		Its very important that the following rules are followed.
	</p>
	<dl>
		<dt>Echo/Print</dt>
		<dd>Never ever echo or print</dd>
		<dt>Return</dt>
		<dd>Always return output</dd>
		<dt>Editing</dt>
		<dd>Always edit in HTML mode, never click the 'Visual' tab as it will
			destroy your php.
		</dd>
		<dt>Advanced Script</dt>
		<dd>
			I strongly recommend you only use very simple scripting. If you want
			advanced functionallity consider defining a function elsewhere and
			calling that or use the inline include or content replacement
			methods.
		</dd>
	</dl>

	<p>This functionality may be buggy.</p>

    <table class="form-table"><tbody>
		<tr class="form-field form-required">
			<th scope="row" valign="top"></th>
			<td><input type="checkbox" name="JPSPX_EVAL_PHP" id="JPSPX_EVAL_PHP"<?php if ($this->jpspxopt['JPSPX_EVAL_PHP']) echo 'checked'; ?> />
				<label for="JPSPX_EVAL_PHP">Enable inline PHP functionality.</label></td>
		</tr>
		<tr class="form-field form-required">
			<th><label for="JPSPX_EVAL_PHP_USER_LEVEL">User level required to create working inline PHP.</label></th>
			<td><input type="text" name="JPSPX_EVAL_PHP_USER_LEVEL" id="JPSPX_EVAL_PHP_USER_LEVEL" value="<?php echo $this->jpspxopt['JPSPX_EVAL_PHP_USER_LEVEL'] ?>" /></td>
		</tr>
	</tbody></table>

	<p class="submit">
		<input type="submit" name="submit" value="Modify Plugin Options &raquo;" />
	</p>

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



    /**
     * The filter applies content replacement, inline includes and inline php
     *
     * @param string $content Page/post content supplied by WP
     * @return filtered $content
     */
    function jp_staticpagex($content)
    {

        global $post, $user_level;

        /**
         * Get level of the user that created the article, if there is a better
         * way let me know.
         */
        $articleuserlevel = get_userdata($post->post_author);
        $articleuserlevel = $articleuserlevel->user_level;

		if (!$user_level)
			$user_level = 0;



        /**
         * Check for a content replacement file created for this article, and the
         * article's author is a high enough level to use the feature.
         */
        if (($this->jpspxopt['JPSPX_REPLACE_CONTENT'])
            && ($articleuserlevel >= $this->jpspxopt['JPSPX_REPLACE_CONTENT_USER_LEVEL'])
			&& file_exists($this->jpspxopt['JPSPX_REPLACE_CONTENT_FOLDER'] . $post->ID . '.php')
            ) {

			include($this->jpspxopt['JPSPX_REPLACE_CONTENT_FOLDER'] . $post->ID . '.php');
			$content = '<!-- JP-StaticPageX content replacment applied to this article. -->';
			// if the currently logged in use is high enough give them a direct edit sp link.
			if ($user_level >= $this->jpspxopt['JPSPX_REPLACE_CONTENT_USER_LEVEL'])
				$content .= '<p class="postmetadata"><a href="' . get_settings('siteurl') . '/wp-admin/edit.php?page=jp-staticpagex.php&edit=' . $post->ID . '" title="Edit this article\'s content replacement file.">Edit CR File</a></p>';
			return $content;

		}



		if (($this->jpspxopt['JPSPX_INLINE_INCLUDES'])
			&& ($articleuserlevel >= $this->jpspxopt['JPSPX_INLINE_INCLUDES_USER_LEVEL'])
			&& preg_match('|<!--#include file="(.*?)".*-->|i', $content, $inlineincludes)
			) {

			unset($inlineincludes[0]);
			foreach($inlineincludes as $include) {
				ob_start();
				include($include);
				$includedoutput = ob_get_clean();
				$content = preg_replace(
					'|(<!--#include file="'.$include.'" -->)|i',
					$includedoutput,
					$content
					);
			}

		}



		/**
		 * Search for inline php and evaluate, if the article's author's user
		 * level is at least that specified. Note that eval usage requires code
		 * to return all output and never echo or print!
		 */
		if (($this->jpspxopt['JPSPX_EVAL_PHP'])
            && ($articleuserlevel >= $this->jpspxopt['JPSPX_EVAL_PHP_USER_LEVEL'])
            && preg_match('|(<\?php.*?\?>)|is', $content)
            ) {

			$content = preg_replace_callback(
				'|(<\?php.*?\?>)|is',
				array($this, 'callback_inline_php_eval'),
				$content
				);

		}

        return $content;

    }



    /**
	 * Callback method for inline php filter
     *
     * Evaluates match, first stripping out the php tags.
     *
     * @param array $matches
     * @return string Return value from eval()
     */
    function callback_inline_php_eval($matches)
    {

        return eval(substr($matches[1], 5, -3));

    }



	function jp_spx_redirect($option)
	{

		global $wp_query;

		if (!is_single() && !is_page())
			return;

        /**
         * Only allow redirects if the article's author is allowed to use the
         * feature.
         */
        if (get_userdata($wp_query->post_author) >= $jpspxopt['JPSPX_REDIRECT_USER_LEVEL']) {

			$option = get_post_meta($wp_query->post->ID, 'redirect', true);

			if (!empty($option)) {

                /**
                 * Work through the various types of redirection.
                 */
                if (preg_match('|^ID:(.*)|', $option, $match)) {

					$redirect = get_permalink($match[1]);

				} elseif (preg_match('|^CATEGORY:(.*)|', $option, $match)) {

					$result = get_posts('numberposts=1&category_name=' . $match[1]);
					if ($result)
						$redirect = get_permalink($result[0]->ID);

				} elseif (preg_match('|^TITLE:(.*)|', $option, $match)) {

					global $wpdb;

					if ($result = $wpdb->get_results(
						"SELECT DISTINCT * FROM {$wpdb->posts}
							WHERE post_date <= NOW()
								AND post_status = 'publish'
								AND post_title = '$match[1]'
							ORDER BY post_date DESC
							LIMIT 0, 1"
						) )
						$redirect = get_permalink($result[0]->ID);

				} else {

					die(
						'Invalid redirect option specified, option must be
							either &quot;ID&quot;, &quot;CATEGORY&quot; or
							&quot;TITLE&quot;.'
						);

				}

				if ($redirect)
					wp_redirect($redirect);

			}

		}

	}



// ------------------------------------------------------------

} // End class definition.



$JPSPX = new JPSPX;

?>
