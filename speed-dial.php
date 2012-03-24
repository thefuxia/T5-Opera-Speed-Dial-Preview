<?php # -*- coding: utf-8 -*-
declare ( encoding = 'UTF-8' );
/**
 * This is the default template for the speed dial plugin.
 * You may copy it to your theme – keep the name! – and customize it.
 * The plugin will use the file from the theme then.
 * Do not just change this file! It will be overridden with the next update.
 *
 * Keep the code short and clean. Make the page load as fast as possible.
 * Your canvas is 256 pixels wide and 160 pixels high.
 */
?>
<!Doctype html>
<html>
	<head>
		<meta charset='utf-8'>
		<title><?php
		/*
		 * The title will be the visible name below the speed dial image.
		 * Make sure it is short enough to fit. Use the static function
		 * T5_Opera_Speed_Dial::utf8_truncate() to shorten the title.
		 */
		print T5_Opera_Speed_Dial::utf8_truncate( get_bloginfo( 'name' ), 50 );
		?></title>
		<?php
		/*
		 * To be honest: I couldn’t see a difference using this tag. It may be
		 * useful if the implementation changes or if you visit the page in a
		 * mobile browser.
		 */
		?>
		<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
		<?php
		/*
		 * Set a refresh rate in seconds. Default here: 2 hours.
		 * If you don’t want any refresh just leave the element out.
		 */
		?>
		<meta http-equiv='preview-refresh' content='7200'>
		<?php
		/*
		 * Note that <style media='screen and (view-mode: minimized)'> will not
		 * work. You may use an external stylesheet. It should not contain any
		 * other media (or non-media) rules, or the sky will fall onto your head.
		 * For now, the following method works best.
		 *
		 * I use a very basic style sheet here.
		 * Single links will not be clickable in preview mode, that’s why I
		 * removed the text-decoration.
		 * screen and (max-width: 500px) will catch cases when visitors with a
		 * small screen land on /speeddial/.
		 */
		?>
		<style>
@media screen and (view-mode: minimized), screen and (max-width: 500px) {
	* {
		margin:				0;
		padding:			0;
		font:				13px/1.5 sans-serif;
		color:				#333;
		background:			#fff;
		text-decoration:	none;
	}
	h1 {
		font:				bold 16px/1 sans-serif;
		color:				#eee;
		background:			#333;
		text-align:			center;
		padding:			5px;
	}
	li {
		list-style:			none;
		padding:			3px 10px;
		border-top:			1px solid #ddd;
	}
	li:first-child {
		border-top:			none;
	}
}
		</style>
	</head>
	<body>
	<?php
	/**
	 * Three posts only. Remember: We don’t have much space.
	 * If you don’t show comments you may set the number to 6.
	 */
	if ( ! $posts = get_posts( array ( 'numberposts' => 3 ) ) )
	{
		/*
		 * There are no posts. No comments probably too.
		 */
		?><h1><?php bloginfo( 'name' ); ?></h1><?php
	}
	else
	{
		?><h1><?php _e( 'Posts' ); ?></h1>
		<ul>
		<?php
		foreach ( $posts as $post )
		{
			?>
			<li>
				<?php
				/*
				 * Just day and month.
				 */
				print mysql2date( 'd.m', $post->post_date );
				?>:
				<a href="<?php
					/*
					 * Links are not clickable in speed dial. But if someone
					 * reads the page in a regular browser window we want to
					 * offer something useful. Hence the links.
					 */
					print get_permalink( $post->ID );
					?>"><?php
					print T5_Opera_Speed_Dial::utf8_truncate(
						get_the_title( $post->ID ),
						50
					);
				?></a>
			</li>
			<?php
		}
		?></ul>
		<?php
	}

	/*
	 * Again just three comments. And no pingbacks.
	 * If you don’t use the comment list, you may show more posts.
	 * We shorten the commenter’s name and the comment excerpt.
	 */
	$comment_args = array ( 'number' => 3, 'type' => 'comment' );
	if ( $comments = get_comments( $comment_args ) )
	{
		?><h1><?php _e( 'Comments' ); ?></h1>
		<ul>
		<?php
		foreach ( $comments as $comment )
		{
			?><li>
				<?php
				/*
				 * Usually, there are more comments than posts. So we
				 * include the time too.
				 */
				print mysql2date( 'd.m · H:i', $comment->comment_date )
				. ' '
				. T5_Opera_Speed_Dial::utf8_truncate(
					get_comment_author( $comment->comment_ID )
				);
				?>: <a href="<?php
					/*
					 * Links again. See the explanation above to understand why.
					 */
					print esc_url( get_comment_link( $comment->comment_ID ) );
					?>"><?php
					print T5_Opera_Speed_Dial::utf8_truncate(
						get_comment_text( $comment->comment_ID )
					);
				?></a>
			</li>
			<?php
		}
		?>
		</ul>
		<?php
	}
	?>
	</body>
</html>