<?php
/*
Plugin Name: My YouTube Videos
Plugin URI: http://www.sebs-studio.com/blog/category/wp-plugins/
Description: Displays your latest video uploads from your YouTube account.
Author URI: http://www.sebs-studio.com/
Author: Sebs Studio (Sebastien)
Version: 1.1
Tags: Videos, Thumbnails, Playlists, YouTube, HD, Widgets
License: GPL3
*/

function fetch_my_youtube_vids(){
	require_once(ABSPATH.WPINC.'/rss.php');
	$rss = fetch_rss("http://gdata.youtube.com/feeds/base/users/".get_option('myyt_username')."/uploads?alt=rss&v=2&orderby=published&client=ytapi-youtube-profile");
	$content = '<table width="120px" cellpadding="2px" cellspacing="2px">';
	if(!empty($rss)){
		$items = array_slice($rss->items, 0, get_option('myyt_display_many'));
		foreach($items as $item){
			$content .= '<tr><td valign="top">';
			$video_link = clean_url($item['link'], $protocolls = null, 'display');
			$video_id = str_replace("http://www.youtube.com/watch?v=", "", "$video_link"); /* Removes the beginning part of the video url. */
			$video_id = str_replace("feature=youtube_gdata", "", "$video_id"); /* Removes the end of the video url. */
			if(get_option('myyt_enable_hd') == 'yes'){
				$enableHD = str_replace("feature=youtube_gdata", "feature=youtube_gdata&hd=1", $video_link); // Removes the end of the video url.
				$video_link = $enableHD;
			}
			$video_id = preg_replace('/&/', '&amp;', $video_id); /* Rewrites the '&' symbol. */
			$video_id = str_replace('&amp;', '', $video_id); /* Removes the '&' symbol. */
			$video_id = str_replace('#038;', '', $video_id); /* Removes the garbage put in place of '&' . */
			if(get_option('myyt_display_thumb') == 'yes'){ $content .= '<a target="_blank" href="'.$video_link.'" title="'.htmlentities($item['title']).'"><img class="latest_yt" src="http://i.ytimg.com/vi/'.$video_id.'/default.jpg" width="120" height="90" /></a><br />'; } /* Displays latest video thumbnail linked to the video. */
			$content .= '<a target="_blank" href="'.$video_link.'" title="'.htmlentities($item['title']).'">'.htmlentities($item['title']).'</a><br />';
			if(get_option('myyt_display_dateadded') == 'yes'){ $content .= 'Added: <em>('.date('M j, Y', strtotime($item['pubdate'])).')</em>'; /* Date of video uploaded. */ }
			//$content .= '<span style="font-size:12px;">'.$item['description'].'</span><br /><br />';
			$content .= "</td></tr>\n";
		}
	}
	else{
		$content .= "<tr>\n";
		$content .= "<td>YouTube Feed not found! Please try again later</td>\n";
		$content .= "</tr>\n";
	}
	$content .= '</table>';
	return $content; // Displays the YouTube video feed.
}
/* Add [my_youtube_videos] to your post or page to display your videos. */
if(function_exists('fetch_my_youtube_vids')){
	/* Only works if plugin is active. */
	add_shortcode('my_youtube_videos', 'fetch_my_youtube_vids');
}

/* Runs when plugin is activated */
register_activation_hook(__FILE__,'my_youtube_vids_install'); 

/* Runs on plugin deactivation*/
register_deactivation_hook( __FILE__, 'my_youtube_vids_remove' );

function my_youtube_vids_install(){
	/* Creates new database field */
	add_option("myyt_display_many", '10', '', 'yes');
	add_option("myyt_display_thumb", 'yes', '', 'yes');
	add_option("myyt_display_dateadded", 'yes', '', 'yes');
	add_option("myyt_enable_hd", 'no', '', 'yes');
}

function my_youtube_vids_remove(){
	/* Deletes the database field */
	delete_option('myyt_username');
	delete_option('myyt_display_many');
	delete_option('myyt_display_thumb');
	delete_option('myyt_display_dateadded');
	delete_option('myyt_enable_hd');
}

if(is_admin()){
	function my_youtube_vids_menu(){
		add_options_page('My YouTube Videos', 'My YouTube Videos', 'manage_options', __FILE__, 'my_youtube_vids_settings');
	}
	add_action('admin_menu', 'my_youtube_vids_menu');
}

function my_youtube_vids_settings(){
	$myyt_username = get_option('myyt_username');
	$display_many_yt = get_option('myyt_display_many');
	$thumbnail = get_option('myyt_display_thumb');
	$dateuploaded = get_option('myyt_display_dateadded');
	$myyt_orderby = get_option('myyt_orderby');
	$myyt_hd = get_option('myyt_enable_hd');
?>
<div class="wrap">
<div id="icon-options-general" class="icon32"><br></div>
<h2>My YouTube Videos</h2>
<form method="post" action="options.php">
<?php wp_nonce_field('update-options'); ?>
<table class="form-table">
<tr valign="top">
<th scope="row">Username</th>
	<td><input type="text" name="myyt_username" value="<?php echo $myyt_username; ?>" /></td>
</tr> 
<tr valign="top">
<th scope="row">Display How Many?</th>
<td>
<select name="myyt_display_many" size="1">
<?php
for($show=1; $show<=20; $show++){
	echo '<option value="'.$show.'"';
	if($display_many_yt == $show){ echo ' selected="selected"'; }
	echo '>'.$show.'</option>';
}
?>
</select>
</td>
</tr> 
<tr valign="top">
<th scope="row">Display Thumbnails?</th>
<td>
<select name="myyt_display_thumb" size="1">
<option value="yes"<?php if($thumbnail == 'yes'){ echo ' selected="selected"'; } ?>>Yes</option>
<option value="no"<?php if($thumbnail == 'no'){ echo ' selected="selected"'; } ?>>No</option>
</select>
</td>
</tr>
</tr> 
<tr valign="top">
<th scope="row">Display Uploaded Date?</th>
<td>
<select name="myyt_display_dateadded" size="1">
<option value="yes"<?php if($dateuploaded == 'yes'){ echo ' selected="selected"'; } ?>>Yes</option>
<option value="no"<?php if($dateuploaded == 'no'){ echo ' selected="selected"'; } ?>>No</option>
</select>
</td>
</tr>
<tr valign="top">
<th scope="row">Enable HD Videos?</th>
<td>
<select name="myyt_enable_hd" size="1">
<option value="yes"<?php if($myyt_hd == 'yes'){ echo ' selected="selected"'; } ?>>Yes</option>
<option value="no"<?php if($myyt_hd == 'no'){ echo ' selected="selected"'; } ?>>No</option>
</select>
</td>
</tr>
</table>
<input type="hidden" name="action" value="update" />
<input type="hidden" name="page_options" value="myyt_username, myyt_display_many, myyt_display_thumb, myyt_display_dateadded, myyt_orderby, myyt_enable_hd" />
<p class="submit"><input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" /></p>
</form>
<h3>Preview</h3>
<?php echo fetch_my_youtube_vids(); ?>
</div>
<?php
}

/*---------------------------------------------------------------------------------*/
/* My YouTube Videos Widget */
/*---------------------------------------------------------------------------------*/
class OceanThemes_My_YouTube_Videos_Widget extends WP_Widget{
	function OceanThemes_My_YouTube_Videos_Widget(){

		/* Widget settings. */
		$widget_ops = array('classname' => 'widget_my_youtube', 'description' => 'Display your latest videos from your YouTube account in the sidebar.');
		/* Widget control settings. */
		$control_ops = array('id_base' => 'oceanthemes-my_youtube-widget');
		/* Create the widget. */
		$this->WP_Widget('oceanthemes-my_youtube-widget', 'My YouTube Videos', $widget_ops, $control_ops);
	}

	function widget($args, $instance){
		extract($args);
		$title = apply_filters('widget_title', $instance['title']);
		$username = $instance['username'];
		$display_many_yt = $instance['myyt_display_many'];
		$thumbnail = $instance['myyt_display_thumb'];
		$dateuploaded = $instance['myyt_display_dateadded'];
		$enable_hd = $instance['myyt_enable_hd'];
	
		$children = '<div id="my_youtube_videos" class="widget">';
		$children .= "<ul>";
		/* Require RSS Feed */
		require_once(ABSPATH.WPINC.'/rss.php');
		$rss = fetch_rss("http://gdata.youtube.com/feeds/base/users/".$username."/uploads?alt=rss&v=2&orderby=published&client=ytapi-youtube-profile");
		if(!empty($rss)){
			$items = array_slice($rss->items, 0, $display_many_yt);
			foreach($items as $item){
				$video_link = clean_url($item['link'], $protocolls = null, 'display');
				$video_id = str_replace("http://www.youtube.com/watch?v=", "", "$video_link");					 /* Removes the beginning part of the video url. */
				$video_id = str_replace("feature=youtube_gdata", "", "$video_id");								 /* Removes the end of the video url. */
				if($enable_hd == 'yes'){
					$enableHD = str_replace("feature=youtube_gdata", "feature=youtube_gdata&hd=1", $video_link); /* Removes the end of the video url. */
					$video_link = $enableHD;
				}
				$children .= "<li>";
				$video_id = preg_replace('/&/', '&amp;', $video_id);/* Rewrites the '&' symbol. */
				$video_id = str_replace('&amp;', '', $video_id);	/* Removes the '&' symbol. */
				$video_id = str_replace('#038;', '', $video_id);	/* Removes the garbage put in place of '&' . */
				if($thumbnail == 'yes'){							/* Displays latest video thumbnail linked to the video. */
					$children .= '<a target="_blank" href="'.$video_link.'" title="'.htmlentities($item['title']).'"><img class="latest_yt" src="http://i.ytimg.com/vi/'.$video_id.'/default.jpg" width="120" height="90" /></a><br />';
				}
				/* Title of Video */
				$children .= '<a target="_blank" href="'.$video_link.'" title="'.htmlentities($item['title']).'">'.htmlentities($item['title']).'</a><br />';
				/* Date of video uploaded. */
				if($dateuploaded == 'yes'){ $children .= 'Added: <em>('.date('M j, Y', strtotime($item['pubdate'])).')</em><br />'; }
				/* Description of the Video */
				$description = strip_tags($item['description']);
				$description = str_replace(htmlentities($item['title']), "", $description);
				/* Removes End Description */
				$description = str_replace('From:', "", $description);
				$description = str_replace($username, "", $description);
				$description = str_replace('Views:', "", $description);
				$description = str_replace('Time:', "", $description);
				$description = str_replace('ratings', "", $description);
				$description = str_replace('More in', "", $description);
				//$children .= '<span style="font-size:12px;">'.$description.'</span><br />';
				/* Video Length */
				//$findtext = 'Time:';
				//$pos = strpos($findtext, '');
				$videolength = substr("Time:", 5, -5);
				//$uploadedby = str_replace('From: '.$username, "", $uploadedby);
				//$children .= '<span style="font-size:12px;">Time: <em>('.$videolength.')</em></span><br />';
				$children .= "</li>";
			}
		}
		else{
			$children .= "<li>YouTube Feed not found! Please try again later</li>\n";
		}
		$children .= "</ul>";
		$children .= '</div>';

		echo $before_widget;
		if(empty($title)){ $title = _e('My Latest Videos on <span>YouTube<span>', 'oceanthemes_my_yt_vids'); }
		echo $before_title . $title . $after_title;
		echo $children;
		echo $after_widget;
	}

	function update($new_instance, $old_instance){
		$instance = $old_instance;

		/* Strip tags (if needed) and update the widget settings. */
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['username'] = strip_tags($new_instance['username']);
		$instance['myyt_display_many'] = esc_attr($new_instance['myyt_display_many']);
		$instance['myyt_display_thumb'] = esc_attr($new_instance['myyt_display_thumb']);
		$instance['myyt_display_dateadded'] = esc_attr($new_instance['myyt_display_dateadded']);
		$instance['myyt_enable_hd'] = esc_attr($new_instance['myyt_enable_hd']);

		return $instance;
	}

	function form($instance){
		/* Set up some default widget settings. */
		$defaults = array(
						'title' => '',
						'username' => 'sebbyd86',
						'myyt_display_many' => '4',
						'myyt_display_thumb' => 'yes',
						'myyt_display_dateadded' => 'yes',
						'myyt_enable_hd' => 'no'
					);
		$instance = wp_parse_args((array) $instance, $defaults);
	?>
	<p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:', 'oceanthemes'); ?></label>
	<input id="<?php echo $this->get_field_id('title'); ?>" class="widefat" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $instance['title']; ?>" /></p>
	<p><label for="<?php echo $this->get_field_id('username'); ?>"><?php _e('Username:', 'oceanthemes'); ?> <input id="<?php echo $this->get_field_id('username'); ?>" class="widefat" name="<?php echo $this->get_field_name('username'); ?>" type="text" value="<?php echo $instance['username']; ?>" /></label></p>
	<p><label for="<?php echo $this->get_field_id('myyt_display_many'); ?>"><?php _e('Number of Videos:', 'oceanthemes'); ?>
	<select name="<?php echo $this->get_field_name('myyt_display_many'); ?>" class="widefat" id="<?php echo $this->get_field_id('myyt_display_many'); ?>">
	<?php for($i = 1; $i <= 10; $i += 1){ ?>
	<option value="<?php echo $i; ?>"<?php if($instance['myyt_display_many'] == $i){ echo " selected='selected'"; } ?>><?php echo $i; ?></option>
	<?php } ?>
	</select>
	</p>
	<p><label for="<?php echo $this->get_field_id('myyt_display_thumb'); ?>"><?php _e('Display Thumbnail:', 'oceanthemes'); ?></label>
	<select name="<?php echo $this->get_field_name('myyt_display_thumb'); ?>" class="widefat" id="<?php echo $this->get_field_id('myyt_display_thumb'); ?>">
	<option value="yes"<?php if($instance['myyt_display_thumb'] == "yes"){ echo " selected='selected'"; } ?>><?php _e('Yes', 'oceanthemes'); ?></option>
	<option value="no"<?php if($instance['myyt_display_thumb'] == "no"){ echo " selected='selected'"; } ?>><?php _e('No', 'oceanthemes'); ?></option>
	</select></p>
	<p><label for="<?php echo $this->get_field_id('myyt_display_dateadded'); ?>"><?php _e('Display Date Added:', 'oceanthemes'); ?></label>
	<select name="<?php echo $this->get_field_name('myyt_display_dateadded'); ?>" class="widefat" id="<?php echo $this->get_field_id('myyt_display_dateadded'); ?>">
	<option value="yes"<?php if($instance['myyt_display_dateadded'] == "yes"){ echo " selected='selected'"; } ?>><?php _e('Yes', 'oceanthemes'); ?></option>
	<option value="no"<?php if($instance['myyt_display_dateadded'] == "no"){ echo " selected='selected'"; } ?>><?php _e('No', 'oceanthemes'); ?></option>
	</select>
	</p>
	<p><label for="<?php echo $this->get_field_id('myyt_enable_hd'); ?>"><?php _e('HD Videos:','oceanthemes'); ?></label>
	<select name="<?php echo $this->get_field_name('myyt_enable_hd'); ?>" class="widefat" id="<?php echo $this->get_field_id('myyt_enable_hd'); ?>">
	<option value="yes"<?php if($instance['myyt_enable_hd'] == "yes"){ echo " selected='selected'"; } ?>><?php _e('Yes', 'oceanthemes'); ?></option>
	<option value="no"<?php if($instance['myyt_enable_hd'] == "no"){ echo " selected='selected'"; } ?>><?php _e('No', 'oceanthemes'); ?></option>
	</select>
	</p>
<?php
	}
}
/* Add action to initiate the widget. */
add_action('widgets_init', 'ot_load_myytvid_widget');
/* Register Widget. */
function ot_load_myytvid_widget(){
	register_widget('OceanThemes_My_YouTube_Videos_Widget');
}
?>