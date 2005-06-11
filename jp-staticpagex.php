<?php
/*
Plugin Name: Static Page eXtended
Version: Beta 2
Plugin URI: http://jp.jixor.com/
Author: Stephen Ingram
Author URI: http://jp.jixor.com/
Description: External pages may replace static pages. This is done via a filter so don't worry no templates to edit. <a href="/wp-content/plugins/jp-staticpagex.php">Manage Static Page Files</a>
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

// -- BEGIN SETUP TESTS --

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
		if(!@mkdir('../wp-content/staticpages/')) {
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

} elseif ($action == 'create') {

?>

<div class="wrap">
<h2>Manage 'JP-Static Page eXtended': Create New Page</h2>

<?php
	if(!$file_handler = fopen('../wp-content/staticpages/'.$create.'.php', 'wb')) {
?>

<p>
Unable to create the static page file for &quot;<?php echo $create ?>&quot;.
</p>

<?php
	} else {
		fclose($file_handler);
?>

<p>
Created the static page file for &quot;<?php echo $create ?>&quot;.
</p>

<?php
	}
?>
</div>

<?php

}

?>

<div class="wrap">
<h2>Manage 'JP-Static Page eXtended': Bindings</h2>

<p>
Remember a static page file will overwrite any content created on that static page.
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

include("admin-footer.php");

}

// -- END ADMIN MODE --

// THE FILTER
function jp_staticpagex($content) {
	global $post;

	if (preg_match('|<!--#include file="(.*?)" -->|i',$content,$inlineincludes)) {
		unset($inlineincludes[0]);
		foreach($inlineincludes as $include) {
			ob_start();
			include($include);
			$includedoutput = ob_get_contents();
			ob_end_clean();
			$content = preg_replace('|(<!--#include file="'.$include.'" -->)|i',$includedoutput,$content);
		}
	}

	if (file_exists('wp-content/staticpages/'.$post->post_name.'.php')) {
		include('wp-content/staticpages/'.$post->post_name.'.php');
		$content = '<!-- JP-StaticPageX applied to this page. -->';
		return $content;
	} else {
		return $content;
	}
}

// ADD THE FILTER
add_filter('the_content', 'jp_staticpagex');

?>
