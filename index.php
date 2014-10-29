<?php
wp_enqueue_style('pingeroo');
wp_enqueue_style('kit-kat-clock');

wp_enqueue_script('pingeroo');
//wp_enqueue_script('kit-kat-clock');

show_admin_bar( false );
remove_action('wp_head', '_admin_bar_bump_cb');
$options = get_pingeroo_options();

get_header();
?>
<div id="content" role="main">
	<form method="post" action="<?php echo get_site_url(); ?>">
		
		<fieldset id="the-services">
			<legend><i class="icon-flow-tree"></i> Pingeroo to</legend>
			<select>
				<?php echo get_pingeroo_group_options(); ?>
				<option>-----</option>
				<option value="all">All</option>
				<option value="+1">+ Create a new group</option>	
			</select>
			
			<?php list_pingeroo_services(); ?>
			
			<?php wp_nonce_field( 'pingeroo-create-group', 'pingeroo-create-group-nonce' ); ?>
		</fieldset>
		
		<div class="main">
			<fieldset id="the-message">
				<label for="message" class="hidden">Message</label>
				<textarea id="message" name="message" rows="1"><?php echo $options['pre-fill']; ?></textarea>
			</fieldset>
			
			<div class="added-media"></div>
			
			<p class="buttons">
				<button title="Media" class="media" id="media-upload"><i class="icon-pictures"></i></button>
				<button title="Schedule" class="schedule"><i class="icon-clock"></i></button>
				<button title="Geotag" class="geotag"><i class="icon-location"></i></button>
			</p>
			
			<div class="options">
				<fieldset id="the-time" class="hide">
					<legend><i class="icon-clock"></i> Schedule</legend>
					<input type="date" id="date" name="date">
					<input type="time" id="time" name="time">
				</fieldset>
				
				<fieldset id="the-location" class="hide">
					<legend><i class="icon-location"></i> Location</legend>
					
					<div id="map"></div>
					
					<input type="hidden" name="lat" id="lat" value="">
					<input type="hidden" name="long" id="long" value="">
				</fieldset>
			</div>
			
		</div>
		
		<input type="hidden" name="pingeroo-nonce" value="<?php echo wp_create_nonce( 'do-pingeroo' );?>">
		<input type="hidden" name="action" value="save_pingeroo_post">
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

<p class="settings"><a href="<?php echo admin_url('options-general.php?page=pingeroo'); ?>"><i class="icon-cog" aria-hidden="true"></i>Settings</a></p>

<div id="drop-target">
	<h2><i class="icon-upload" aria-hidden="true"></i>Drop files here to upload</h2>
</div>

<?php get_footer(); ?>