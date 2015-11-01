<?php

/*
Plugin Name: Hatena Connect
Plugin URI: GitHub URL
Description: Connect Hatena with your wordpress blog to get notifications on Hatena notify.
Version: 1.1.0
Author: wanakijiji
Author URI: http://blog.aroundit.net
Text Domain: hatena-connect
License: GPLv2 or later
*/


class hatena_connect {

	var $options = array();

	function __construct() {
		/* Load Plugin Translation */
		load_plugin_textdomain( 'hatena-connect', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );

		/* Activation & Uninstall */
		register_activation_hook( __FILE__, array( $this, 'hc_activation_hook' ) );
		register_uninstall_hook( __FILE__, array( $this, 'hc_uninstall_hook' ) );

		/* Get Option */
		$this->options = get_option( 'hatena_connect' );

		/* Settings Menu & Settings Page */
		add_action( 'admin_menu', array( $this, 'hc_add_admin_menu' ) );

		/* Main Functions */
		if( $this->options['hatena_id'] ) {
			/* Add RDF Tags to Head */
			add_action( 'wp_head', array( $this, 'hc_add_head_tags' ) );
		}
		if( $this->options['remove_more'] ) {
			/* Remove More Anchor link */
			add_filter( 'the_content_more_link', array( $this, 'hc_remove_more_link' ) );
		}
	}
	
	
	/**
	 * Activation
	 */
	function hc_activation_hook() {
		$hc_options = array(
			'hatena_id' => '',
			'remove_more' => false
		);
		add_option( 'hatena_connect', $hc_options );
	}
	
	
	/**
	 * Uninstall
	 */
	function hc_uninstall_hook() {
		delete_option( 'hatena_connect' );
	}
	
	
	/**
	 * Settings Menu & Settings Page
	 */
	function hc_add_admin_menu() {
		add_options_page( 'Hatena Connect', 'Hatena Connect', 'manage_options', 'hatena-connect', array( $this, 'hc_options_page' ) );
	}
	function hc_options_page() {
		if( $_POST['sended'] == 'Y' ) {
			check_admin_referer('hct_options');
			$this->options['hatena_id'] = $_POST['hct_hatena_id'];
			$this->options['remove_more'] = ( $_POST['hct_remove_more'] == 1 ) ? true : false;
			update_option( 'hatena_connect', $this->options );
		?>
			<div id="message" class="updated notice is-dismissible">
				<p><?php _e( 'Settings saved.', 'hatena-connect' ); ?></p>
				<button type="button" class="notice-dismiss"><span class="screen-reader-text"><?php _e( 'Dismiss this notice.', 'hatena-connect' ); ?></span></button>
			</div>
		<?php
		}
		?>
		<div class="wrap">
			<h1><?php _e( 'Hatena Connect Settings', 'hatena-connect' ); ?></h1>
			<p><?php _e( 'Hatena Connect is a plugin to connect Hatena Bookmark with your wordpress blog.', 'hatena-connect' ); ?></p>
			<ul style="margin-left:2em;list-style-type: disc">
				<li><?php _e( '"Notifications for you" on Hatena Website', 'hatena-connect' ); ?></li>
				<li><?php _e( '"Notifications" on Hatena App', 'hatena-connect' ); ?></li>
				<li><?php _e( '"Hatena Notify" Chrome Extension', 'hatena-connect' ); ?></li>
			</ul>
			<form name="form" action="" method="post">
				<?php wp_nonce_field( 'hct_options' ); ?>
				<input type="hidden" name="sended" value="Y">
				<table class="form-table">
					<tr>
						<th><?php _e( 'Hatena ID', 'hatena-connect' ); ?></th>
						<td><input id="hct_hatena_id" name="hct_hatena_id" class="regular-text code" type="text" value="<?php echo $this->options['hatena_id']; ?>" pattern="^[0-9A-Za-z-_]+$" title="<?php _e( 'alphanumeric, hyphen, underscore', 'hatena-connect' ); ?>"></td>
					</tr>
					<tr>
						<th><?php _e( 'Remove #more anchor', 'hatena-connect' ); ?></th>
						<td><label for="hct_remove_more"><input type="checkbox" name="hct_remove_more" id="hct_remove_more" value="1"<?php if( $this->options['remove_more'] ): ?> checked<?php endif; ?>> <?php _e( 'Remove more anchor from "Read More" links to prevent bookmarking duplicate content', 'hatena-connect' ); ?></label></td>
					</tr>
				</table>
				<p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary" value="<?php _e( 'Save Changes', 'hatena-connect'); ?>"></p>
			</form>
		</div>
		<?php
	}


	/**
	 * Add RDF Tags to Head
	 */
	function hc_add_head_tags() {
		$url = ( is_single() || is_page() ) ? get_the_permalink() : home_url();
		?>
		<!-- Hatena Connect Tags -->
		<rdf:RDF xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:foaf="http://xmlns.com/foaf/0.1/">
		<rdf:Description rdf:about="<?php echo $url ?>">
		<foaf:maker rdf:parseType="Resource">
		<foaf:holdsAccount>
		<foaf:OnlineAccount foaf:accountName="<?php echo $this->options['hatena_id']; ?>">
		<foaf:accountServiceHomepage rdf:resource="http://www.hatena.ne.jp/" />
		</foaf:OnlineAccount>
		</foaf:holdsAccount>
		</foaf:maker>
		</rdf:Description>
		</rdf:RDF>
		<?php
	}
	
	
	/**
	 * Remove More Anchor Link
	 */
	function hc_remove_more_link( $link ) { 
		$link = preg_replace( '|#more-[0-9]+|', '', $link );
		return $link;
	}

}

new hatena_connect;
