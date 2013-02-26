<?php
wp_enqueue_style('pingeroo');
wp_enqueue_script('pingeroo');
get_header(); ?>
<div id="content" role="main">
	<p id="character-count"></p>
	<form>
		<fieldset>
			<label for="message">Message</label>
			<textarea id="message" name="message"></textarea>
		</fieldset>
		
		<fieldset id="services">
			<legend>Pingeroo to</legend>
			<select>
				<option>Select a group</option>
				<option>Default</option>
				<option>Custom</option>	
			</select>
			<label><input type="checkbox" name="services[twitter]" value="twitter"> Twitter</label>
			<label><input type="checkbox" name="services[facebook]" value="facebook"> Facebook</label>
		</fieldset>
		
		<fieldset>
			<legend>When?</legend>
			<input type="date">
			<input type="time">
		</fieldset>
		
		<input type="submit" class="submit" value="Post it!">
	</form>
</div>
<?php get_footer(); ?>