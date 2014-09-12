<?php
wp_enqueue_style('pingeroo');
wp_enqueue_script('pingeroo');
get_header();
?>

<div id="content" role="main">
	<form method="post" action="<?php echo get_site_url(); ?>">
		<fieldset id="the-message">
			<label for="message" class="hidden">Message</label>
			<textarea id="message" name="message"></textarea>
			<p id="character-count"></p>
		</fieldset>
		
		<fieldset id="the-services">
			<legend>Pingeroo to</legend>
			<select>
				<?php echo get_pingeroo_group_options(); ?>
				<option value="all">All</option>
				<option value="+1">+ Create a new group</option>	
			</select>
			
			<?php list_pingeroo_services(); ?>
			
			<?php wp_nonce_field( 'pingeroo-create-group', 'pingeroo-create-group-nonce' ); ?>
		</fieldset>
		
		<fieldset id="the-time">
			<legend><i class="dashicons dashicons-clock"></i>When?</legend>
			<input type="date">
			<input type="time">
		</fieldset>
		
		<input type="hidden" name="pingeroo-nonce" value="<?php echo wp_create_nonce( 'do-pingeroo' );?>">
		<ul>
			<li></li>
		</ul>
		<input type="submit" class="submit" value="Post it!">
	</form>
</div>
<?php get_footer(); ?>