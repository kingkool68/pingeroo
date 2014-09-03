<?php
wp_enqueue_style('pingeroo');
wp_enqueue_script('pingeroo');
get_header();
?>

<div id="content" role="main">
	<p id="character-count"></p>
	<form method="post">
		<fieldset id="the-message" class="effect4">
			<label for="message">Message</label>
			<textarea id="message" name="message"></textarea>
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
			<legend>When?</legend>
			<input type="date">
			<input type="time">
		</fieldset>
		
		<input type="hidden" name="pingeroo-nonce" value="<?php echo wp_create_nonce( 'pingeroo-update' );?>">
		<input type="submit" class="submit" value="Post it!">
	</form>
</div>
<?php get_footer(); ?>