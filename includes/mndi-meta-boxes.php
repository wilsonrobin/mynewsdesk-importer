<?php

add_action('add_meta_boxes', 'create_mndi_meta_box');


function create_mndi_meta_box() {
    add_meta_box(
		'mndi_meta_box',
		'MyNewsDesk Data',
		'display_mndi_meta_data',
		'mndi_news',
		'side'
	);
}

function display_mndi_meta_data($post) {
	$links = get_post_meta($post->ID, 'mndi_links', true); ?>
	<div class="cf-container">		
		<p><label class="cf-field__label" for="mndi_id">MyNewsDesk id</label>
		<input id="mndi_id"  type="text" disabled value="<?php echo get_post_meta($post->ID, 'mndi_id', true); ?>" class="cf-text__input"></p>

		<p><label class="cf-field__label" for="mndi_updated">MyNewsDesk post last updated</label>
		<input id="mndi_updated"  type="text" disabled value="<?php echo get_post_meta($post->ID, 'mndi_updated', true); ?>" class="cf-text__input"></p>

		<p><b class="cf-field__label" for="mndi_url">MyNewsDesk url</b>
		<a href="<?php echo get_post_meta($post->ID, 'mndi_url', true); ?>" target="_blank">
			Go to article on mynewsdesk.com
		</a></p>

		<p><b class="cf-field__label" for="mndi_url">MyNewsDesk full data</b>
		Full post data can be fetched with <br><pre>$mndi->get_post_data();</pre></p>
	</div>
	<?php
}