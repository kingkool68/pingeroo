<?php
wp_enqueue_style('pingeroo');
wp_enqueue_script('pingeroo');
get_header();
?>

<div id="content" role="main">
	<form method="post" action="<?php echo get_site_url(); ?>">
		<fieldset id="the-message">
			<label for="message" class="hidden">Message</label>
			<textarea id="message" name="message" rows="1"></textarea>
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

		<div class="controls">
			<input type="submit" class="submit" value="Post it!">
			<p id="character-count">0</p>
		</div>
		
		<input type="hidden" name="pingeroo-nonce" value="<?php echo wp_create_nonce( 'do-pingeroo' );?>">
	</form>
	
	<section id="recent" style="display:none;">
		<h2>Recent Pingeroos</h2>
		<ul>
			<li><a href="#">Recent Pingeroo update for all to see.</a></li>
			<li><a href="#">Recent Pingeroo update for all to see.</a></li>
			<li><a href="#">Recent Pingeroo update for all to see.</a></li>
		</ul>
	</section>
</div>
<?php get_footer(); ?>