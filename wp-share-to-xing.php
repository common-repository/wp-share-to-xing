<?php
/**
 * Plugin Name: WP-Share to XING
 * Plugin URI: http://blog.ppfeufer.de/wordpress-plugin-wp-share-to-xing/
 * Description: This plugin will install a XING-Button to share a link to your XING-Contacts.
 * Version: 2.1
 * Author: H.-Peter Pfeufer
 * Author URI: http://ppfeufer.de
 */
if(!class_exists('WP_Share_To_Xing')) {
	/**
	 * WP Shar to Xing
	 *
	 * Add a submenu to your settings for manage the plugins options.
	 * Add the xing button to your frontend.
	 *
	 * @since 2.0
	 *
	 * @author H.-Peter Pfeufer
	 */
	class WP_Share_To_Xing {
		private $var_sPluginVersion = '2.0.1';
		private $var_sPluginName = 'WP Share to Xing';
		private $var_sPluginTextdomain = 'wp-share-to-xing';
		private $var_sPluginSettingsField = 'wp_share_to_xing_settings';
		private $var_sPluginCapability = 'manage_options';
		private $var_sFlattrLink = 'https://flattr.com/thing/92358/WordPress-Plugin-WP-Share-to-XING';
		private $var_sPayPalLink = 'https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=2X3GXM29WW6T4';

		private $array_PluginSettins;

		/**
		 * Constructor / Init
		 *
		 * @since 2.0
		 */
		function WP_Share_To_Xing() {
			$this->array_PluginSettins = get_option($this->var_sPluginSettingsField);

			/**
			 * Sprachdatei wählen
			 */
			if(function_exists('load_plugin_textdomain')) {
				load_plugin_textdomain($this->var_sPluginTextdomain, PLUGINDIR . '/' . dirname(plugin_basename(__FILE__)) . '/languages', dirname(plugin_basename(__FILE__)) . '/languages');
			} // END if(function_exists('load_plugin_textdomain'))

			if(!is_array($this->array_PluginSettins)) {
				$this->_activate();
			} // END if(!is_array($this->array_PluginSettins))

			/**
			 * Filter zum Blog hinzufügen
			 */
			add_filter('the_content', array(
				$this,
				'wp_share_to_xing_button'
			), 8);

// 			add_filter('get_the_excerpt', array(
// 				$this,
// 				'wp_share_to_xing_remove_filter',
// 				9
// 			));
// 			add_filter('plugin_action_links', array(
// 				$this,
// 				'wp_share_to_xing_settings_link',
// 				9,
// 				2
// 			));

			if(is_admin()) {
				add_action('admin_menu', array(
					$this,
					'wp_share_to_xing_options'
				));
				add_action('admin_init', array(
					$this,
					'wp_share_to_xing_init'
				));

				if(ini_get('allow_url_fopen') || function_exists('curl_init')) {
					add_action('in_plugin_update_message-' . plugin_basename(__FILE__), array(
						$this,
						'_update_notice'
					));
				} // END if(ini_get('allow_url_fopen') || function_exists('curl_init'))
			} // END if(is_admin())

			/**
			 * Button aktivieren
			 */
			register_activation_hook(__FILE__, array(
				$this,
				'wp_share_to_xing_activate'
			));

		} // END function WP_Share_To_Xing()

		function _activate() {
			$array_DefaultOptions = array(
				'wp_share_to_xing_where' => 'before',
				'wp_share_to_xing_rss_where' => 'before',
				'wp_share_to_xing_style' => 'float: right; margin-left: 10px;',
				'wp_share_to_xing_display_page' => '0',
				'wp_share_to_xing_display_front' => '0',
				'wp_share_to_xing_display_archive' => '0',
				'wp_share_to_xing_display_category' => '0',
				'wp_share_to_xing_button' => 'counter-top',
				'wp_share_to_xing_language' => (strstr(get_locale(), 'de_')) ? 'de' : 'en'
			);

			add_option($this->var_sPluginSettingsField, $array_DefaultOptions);
		} // END function _activate()

		function _validate_settings($input) {
			if(isset($input['wp_share_to_xing_display_page'])) {
				$output['wp_share_to_xing_display_page'] = 1;
			} else {
				unset($output['wp_share_to_xing_display_page']);
			} // END if(isset($input['wp_share_to_xing_display_page']))

			if(isset($input['wp_share_to_xing_display_front'])) {
				$output['wp_share_to_xing_display_front'] = 1;
			} else {
				unset($output['wp_share_to_xing_display_front']);
			} // END if(isset($input['wp_share_to_xing_display_front']))

			if(isset($input['wp_share_to_xing_display_archive'])) {
				$output['wp_share_to_xing_display_archive'] = 1;
			} else {
				unset($output['wp_share_to_xing_display_archive']);
			} // END if(isset($input['wp_share_to_xing_display_archive']))

			if(isset($input['wp_share_to_xing_display_category'])) {
				$output['wp_share_to_xing_display_category'] = 1;
			} else {
				unset($output['wp_share_to_xing_display_category']);
			} // END if(isset($input['wp_share_to_xing_display_category']))

			if(!empty($input['wp_share_to_xing_where'])) {
				$output['wp_share_to_xing_where'] = $input['wp_share_to_xing_where'];
			} // END if(!empty($input['wp_share_to_xing_where']))

			if(!empty($input['wp_share_to_xing_style'])) {
				$output['wp_share_to_xing_style'] = $input['wp_share_to_xing_style'];
			} else {
				unset($output['wp_share_to_xing_style']);
			} // END if(!empty($input['wp_share_to_xing_style']))

			if(!empty($input['wp_share_to_xing_language'])) {
				$output['wp_share_to_xing_language'] = $input['wp_share_to_xing_language'];
			} // END if(!empty($input['wp_share_to_xing_language']))

			if(!empty($input['wp_share_to_xing_button'])) {
				$output['wp_share_to_xing_button'] = $input['wp_share_to_xing_button'];
			} // END if(!empty($input['wp_share_to_xing_button']))

			return $output;
		} // END function _validate_settings($input)

		function wp_share_to_xing_init() {
			if(function_exists('register_setting')) {
				register_setting('wp-share-to-xing-options', $this->var_sPluginSettingsField, array(
					$this,
					'_validate_settings'
				));
			} // END if(function_exists('register_setting'))
		} // END function wp_share_to_xing_init()

		function wp_share_to_xing_options() {
			add_options_page(
				'Share to XING',
				'<img src="' . $this->_get_url('/img/share_button_16x16_v1.png') . '" id="2-click-icon" alt="2 Click Social Media Buttons Icon" width="10" height="10" /> Share to XING',
				$this->var_sPluginCapability,
				'wp-shar-to-xing-options',
				array(
					$this,
					'wp_share_to_xing_options_page'
				)
			);
		} // END function wp_share_to_xing_options()

		function wp_share_to_xing_options_page() {
			?>
			<div class="wrap">
				<div class="icon32" id="icon-options-general"><br /></div>
				<h2><?php _e('Settings for WP-Share to XING', $this->var_sPluginTextdomain); ?></h2>
				<p><?php _e('This plugin will install the WP-Share to XING widget for each of your blog posts.', $this->var_sPluginTextdomain); ?></p>
				<form id="form-wp-share-to-xing" method="post" action="options.php">
					<?php
					if(function_exists('settings_fields')) {
						settings_fields('wp-share-to-xing-options');
					} // END if(function_exists('settings_fields'))
					?>
					<table class="form-table">
						<tr>
							<th scope="row" valign="top"><?php _e('Display', $this->var_sPluginTextdomain); ?></th>
							<td>
								<!-- Donation -->
								<div style="float:right; text-align:center; width:120px;">
									<?php _e('Like this Plugin? Buy me a coffee.', $this->var_sPluginTextdomain); ?><br />
									<p>
										<a href="<?php echo $this->var_sFlattrLink; ?>" target="_blank"><img src="http://api.flattr.com/button/flattr-badge-large.png" alt="Flattr this" title="Flattr this" border="0" /></a>
									</p>
									<p>
										<a class="PayPalButton" href="<?php echo $this->var_sPayPalLink; ?>" target="_blank"><img src="https://www.paypalobjects.com/WEBSCR-640-20110401-1/en_GB/i/btn/btn_donate_SM.gif" /></a>
									</p>
								</div>
								<div>
									<input type="checkbox" value="1" <?php if($this->array_PluginSettins['wp_share_to_xing_display_page'] == '1') echo 'checked="checked"'; ?> name="wp_share_to_xing_settings[wp_share_to_xing_display_page]" id="wp_share_to_xing_settings[wp_share_to_xing_display_page]" group="wp_share_to_xing_display" />
									<label for="wp_share_to_xing_settings[wp_share_to_xing_display_page]"><?php _e('Display the button on pages', $this->var_sPluginTextdomain); ?></label>
								</div>
								<div>
									<input type="checkbox" value="1" <?php if($this->array_PluginSettins['wp_share_to_xing_display_front'] == '1') echo 'checked="checked"'; ?> name="wp_share_to_xing_settings[wp_share_to_xing_display_front]" id="wp_share_to_xing_settings[wp_share_to_xing_display_front]" group="wp_share_to_xing_display" />
									<label for="wp_share_to_xing_settings[wp_share_to_xing_display_front]"><?php _e('Display the button on front page (home)', $this->var_sPluginTextdomain); ?></label>
								</div>
								<div>
									<input type="checkbox" value="1" <?php if($this->array_PluginSettins['wp_share_to_xing_display_archive'] == '1') echo 'checked="checked"'; ?> name="wp_share_to_xing_settings[wp_share_to_xing_display_archive]" id="wp_share_to_xing_settings[wp_share_to_xing_display_archive]" group="wp_share_to_xing_display" />
									<label for="wp_share_to_xing_settings[wp_share_to_xing_display_archive]"><?php _e('Display the button on archive-view <em>(<strong>Note</strong>: not all themes support this option)</em>', $this->var_sPluginTextdomain); ?></label>
								</div>
								<div>
									<input type="checkbox" value="1" <?php if($this->array_PluginSettins['wp_share_to_xing_display_category'] == '1') echo 'checked="checked"'; ?> name="wp_share_to_xing_settings[wp_share_to_xing_display_category]" id="wp_share_to_xing_settings[wp_share_to_xing_display_category]" group="wp_share_to_xing_display" />
									<label for="wp_share_to_xing_settings[wp_share_to_xing_display_category]"><?php _e('Display the button on category-view <em>(<strong>Note</strong>: not all themes support this option)</em>', $this->var_sPluginTextdomain); ?></label>
								</div>
							</td>
						</tr>
						<tr>
							<th scope="row" valign="top"><?php _e('Position', $this->var_sPluginTextdomain); ?></th>
							<td>
								<select name="wp_share_to_xing_settings[wp_share_to_xing_where]">
									<option <?php if($this->array_PluginSettins['wp_share_to_xing_where'] == 'before') echo 'selected="selected"'; ?> value="before"><?php _e('Before', $this->var_sPluginTextdomain); ?></option>
									<option <?php if($this->array_PluginSettins['wp_share_to_xing_where'] == 'after') echo 'selected="selected"'; ?> value="after"><?php _e('After', $this->var_sPluginTextdomain); ?></option>
									<option <?php if($this->array_PluginSettins['wp_share_to_xing_where'] == 'shortcode') echo 'selected="selected"'; ?> value="shortcode"><?php _e('Shortcode [share_to_xing]', $this->var_sPluginTextdomain); ?></option>
								</select>
							</td>
						</tr>
						<tr>
							<th scope="row" valign="top"><?php _e('Language', $this->var_sPluginTextdomain); ?></th>
							<td>
								<select name="wp_share_to_xing_settings[wp_share_to_xing_language]">
									<option <?php if($this->array_PluginSettins['wp_share_to_xing_language'] == 'de') echo 'selected="selected"'; ?> value="de"><?php _e('Deutsch', $this->var_sPluginTextdomain); ?></option>
									<option <?php if($this->array_PluginSettins['wp_share_to_xing_language'] == 'en') echo 'selected="selected"'; ?> value="en"><?php _e('English', $this->var_sPluginTextdomain); ?></option>
								</select>
							</td>
						</tr>
						<tr>
							<th scope="row" valign="top"><?php _e('Type', $this->var_sPluginTextdomain); ?></th>
							<td>
								<div class="clearfix">
									<?php
									$array_Buttons = $this->_get_button_types();
									foreach((array) $array_Buttons['count'] as $button) {
										echo '<div style="width:20%; float:left;">';
										echo '<div style="float:left; position:relative; top:10px;">';
										echo $button['formfield'];
										echo '</div>';
										echo '<div style="left:13px; position:relative; text-align:center; top:14px; width:110px; float:left;">';
										echo '<img src="' . $button['image'] . '" />';
										echo '</div>';
										echo '</div>';
									} // END foreach((array) $array_Buttons['count'] as $button)

									echo '<div style="float:left;height: 170px; margin-bottom:15px; width:230px;">';
									foreach((array) $array_Buttons['share'] as $button) {
										echo '<div style="margin-bottom:20px;">';
										echo '<div style="float:left; margin-right:15px;">';
										echo $button['formfield'];
										echo '</div>';
										echo '<img src="' . $button['image'] . '" />';
										echo '</div>';
									} // END foreach((array) $array_Buttons['share'] as $button)
									echo '</div>';
									?>
								</div>
							</td>
						</tr>
						<tr>
							<th scope="row" valign="top"><label for="wp_share_to_xing_settings[wp_share_to_xing_style]"><?php _e('Styling', $this->var_sPluginTextdomain); ?></label></th>
							<td>
								<input type="text" value="<?php echo htmlspecialchars($this->array_PluginSettins['wp_share_to_xing_style']); ?>" name="wp_share_to_xing_settings[wp_share_to_xing_style]" id="wp_share_to_xing_settings[wp_share_to_xing_style]" />
								<span class="description"><?php _e('Add style to the div that surrounds the button E.g. <code>float: left; margin-right: 10px;</code>', $this->var_sPluginTextdomain); ?></span>
							</td>
						</tr>
					</table>
					<?php submit_button(__('Save Changes', $this->var_sPluginTextdomain)); ?>
				</form>
			</div>
		<?php
		} // END function wp_share_to_xing_options_page()

		private function _get_button_types() {
			$array_Buttons = array(
				'count' => array (
					'counter-top' => array(
						'image' => $this->_get_url('/img/share_button_top_' . $this->array_PluginSettins['wp_share_to_xing_language'] . '_v1.gif'),
						'formfield' => '<input type="radio" value="counter-top" name="wp_share_to_xing_settings[wp_share_to_xing_button]" ' . checked('counter-top', $this->array_PluginSettins['wp_share_to_xing_button'], false) . 'id="wp_share_to_xing_settings[wp_share_to_xing_button_counter-top]" group="wp_share_to_xing_button" />'
					),
					'counter-right' => array(
						'image' => $this->_get_url('/img/share_button_right_' . $this->array_PluginSettins['wp_share_to_xing_language'] . '_v1.gif'),
						'formfield' => '<input type="radio" value="counter-right" name="wp_share_to_xing_settings[wp_share_to_xing_button]" ' . checked('counter-right', $this->array_PluginSettins['wp_share_to_xing_button'], false) . ' id="wp_share_to_xing_settings[wp_share_to_xing_button_counter-right]" group="wp_share_to_xing_button" />'
					),
					'counter-small' => array(
						'image' => $this->_get_url('/img/share_button_square_right_' . $this->array_PluginSettins['wp_share_to_xing_language'] . '_v1.gif'),
						'formfield' => '<input type="radio" value="counter-small" name="wp_share_to_xing_settings[wp_share_to_xing_button]" ' . checked('counter-small', $this->array_PluginSettins['wp_share_to_xing_button'], false) . ' id="wp_share_to_xing_settings[wp_share_to_xing_button_counter-right]" group="wp_share_to_xing_button" />'
					)
				),
				'share' => array(
					'share-small-square' => array(
						'image' => $this->_get_url('/img/share_button_16x16_v1.png'),
						'formfield' => '<input type="radio" value="share-small-square" name="wp_share_to_xing_settings[wp_share_to_xing_button]"' . checked('share-small-square', $this->array_PluginSettins['wp_share_to_xing_button'], false) . ' id="wp_share_to_xing_settings[wp_share_to_xing_button_share-small-square]" group="wp_share_to_xing_button" />'
					),
					'share-square' => array(
						'image' => $this->_get_url('/img/share_button_32x32_v1.png'),
						'formfield' => '<input type="radio" value="share-square" name="wp_share_to_xing_settings[wp_share_to_xing_button]"' . checked('share-square', $this->array_PluginSettins['wp_share_to_xing_button'], false) . ' id="wp_share_to_xing_settings[wp_share_to_xing_button_share-square]" group="wp_share_to_xing_button" />'
					),
					'share-rectangle' => array(
						'image' => $this->_get_url('/img/share_button_v1.png'),
						'formfield' => '<input type="radio" value="share-rectangle" name="wp_share_to_xing_settings[wp_share_to_xing_button]"' . checked('share-rectangle', $this->array_PluginSettins['wp_share_to_xing_button'], false) . ' id="wp_share_to_xing_settings[wp_share_to_xing_button_share-rectangle]" group="wp_share_to_xing_button" />'
					)
				)
			);

			return $array_Buttons;
		} // END private function _get_button_types()

		private function _get_button_script($var_sType, $var_sLanguage = 'de', $var_sUri) {
			if(!isset($var_sType) || !isset($var_sUri)) {
				return;
			} // END if(!isset($var_sType) || !isset($var_sUri))

			switch($var_sType) {
				case 'counter-top':
					$var_sJS = '<script type="XING/Share" data-counter="top" data-lang="' . $var_sLanguage . '" data-url="' . $var_sUri . '"></script>';
					break;

				case 'counter-right':
					$var_sJS = '<script type="XING/Share" data-counter="right" data-lang="' . $var_sLanguage . '" data-url="' . $var_sUri . '"></script>';
					break;

				case 'counter-small':
					$var_sJS = '<script type="XING/Share" data-button-shape="square" data-counter="right" data-lang="' . $var_sLanguage . '" data-url="' . $var_sUri . '"></script>';
					break;

				case 'share-small-square':
					$var_sJS = '<script type="XING/Share" data-counter="no_count" data-lang="' . $var_sLanguage . '" data-button-shape="small_square" data-url="' . $var_sUri . '"></script>';
					break;

				case 'share-square':
					$var_sJS = '<script type="XING/Share" data-counter="no_count" data-lang="' . $var_sLanguage . '" data-button-shape="square" data-url="' . $var_sUri . '"></script>';
					break;

				case 'share-rectangle':
					$var_sJS = '<script type="XING/Share" data-counter="no_count" data-lang="' . $var_sLanguage . '" data-button-shape="rectangle" data-url="' . $var_sUri . '"></script>';
					break;
			} // END switch($var_sType)

			return $var_sJS . '<script>;(function(d, s) {var x = d.createElement(s),s = d.getElementsByTagName(s)[0];x.src =\'https://www.xing-share.com/js/external/share.js\';s.parentNode.insertBefore(x, s);})(document, \'script\');</script>';
		} // END private function _get_button_script($var_sType, $var_sLanguage = 'de', $var_sUri)

		private function _get_url( $path = '' ) {
			return plugins_url( ltrim( $path, '/' ), __FILE__ );
		} // END private function _get_url( $path = '' )

		function wp_share_to_xing_button($content) {
			global $post;

			/**
			 * Soll der Button im Feed ausgeblendet werden?
			 */
			if(is_feed()) {
				return $content;
			} // END if(is_feed())

			/**
			 * Sind wir auf einer CMS-Seite?
			 */
			if($this->array_PluginSettins['wp_share_to_xing_display_page'] == null && is_page()) {
				return $content;
			} // END if($this->array_PluginSettins['wp_share_to_xing_display_page'] == null && is_page())

			/**
			 * Sind wir auf der Startseite?
			 */
			if($this->array_PluginSettins['wp_share_to_xing_display_front'] == null && is_home()) {
				return $content;
			} // END if($this->array_PluginSettins['wp_share_to_xing_display_front'] == null && is_home())

			/**
			 * Sind wir in der Achiveanzeige?
			 */
			if($this->array_PluginSettins['wp_share_to_xing_display_archive'] == null && is_archive()) {
				return $content;
			} // END if($this->array_PluginSettins['wp_share_to_xing_display_archive'] == null && is_archive())

			/**
			 * Sind wir in der Kategorieanzeige?
			 */
			if($this->array_PluginSettins['wp_share_to_xing_display_category'] == null && is_category()) {
				return $content;
			} // END if($this->array_PluginSettins['wp_share_to_xing_display_category'] == null && is_category())

			$button = '<div style="' . $this->array_PluginSettins['wp_share_to_xing_style'] . '">' . $this->_get_button_script($this->array_PluginSettins['wp_share_to_xing_button'], $this->array_PluginSettins['wp_share_to_xing_language'], get_permalink($post->ID)) . '</div>';
			$where = 'wp_share_to_xing_where';

			/**
			 * Wurde der Shortcode genutzt
			 */
			if($this->array_PluginSettins[$where] == 'shortcode') {
				return str_replace('[share_to_xing]', $button, $content);
			} else {
				/**
				 * Wenn wir den Button abgeschalten haben
				 */
				if(get_post_meta($post->ID, $this->var_sPluginTextdomain) == null) {
					if($this->array_PluginSettins[$where] == 'before') {
						/**
						 * Vor dem Beitrag einfügen
						 */
						return $button . $content;
					} else {
						/**
						 * Nach dem Beitrag einfügen
						 */
						return $content . $button;
					} // END if($this->array_PluginSettins[$where] == 'before')
				} else {
					/**
					 * Keinen Button einfügen
					 */
					return $content;
				} // END if(get_post_meta($post->ID, $this->var_sPluginTextdomain) == null)
			} // END if($this->array_PluginSettins[$where] == 'shortcode')
		} // END private function wp_share_to_xing_button($content)

		function _update_notice() {
			$url = 'http://plugins.trac.wordpress.org/browser/wp-share-to-xing/trunk/readme.txt?format=txt';
			$data = '';

			if(ini_get('allow_url_fopen')) {
				$data = file_get_contents($url);
			} else {
				if(function_exists('curl_init')) {
					$ch = curl_init();
					curl_setopt($ch, CURLOPT_URL, $url);
					curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
					$data = curl_exec($ch);
					curl_close($ch);
				} // END if(function_exists('curl_init'))
			} // END if(ini_get('allow_url_fopen'))

			if($data) {
				$matches = null;
				$regexp = '~==\s*Changelog\s*==\s*=\s*[0-9.]+\s*=(.*)(=\s*' . preg_quote($this->var_sPluginVersion) . '\s*=|$)~Uis';

				if(preg_match($regexp, $data, $matches)) {
					$changelog = (array) preg_split('~[\r\n]+~', trim($matches[1]));

					echo '</div><div class="update-message" style="font-weight: normal;"><strong>What\'s new:</strong>';
					$ul = false;
					$version = 99;

					foreach($changelog as $index => $line) {
						if(version_compare($version, $this->var_sPluginVersion, ">")) {
							if(preg_match('~^\s*\*\s*~', $line)) {
								if(!$ul) {
									echo '<ul style="list-style: disc; margin-left: 20px;">';
									$ul = true;
								} // END if(!$ul)

								$line = preg_replace('~^\s*\*\s*~', '', $line);
								echo '<li>' . $line . '</li>';
							} else {
								if($ul) {
									echo '</ul>';
									$ul = false;
								} // END if($ul)

								$version = trim($line, " =");
								echo '<p style="margin: 5px 0;">' . htmlspecialchars($line) . '</p>';
							} // END if(preg_match('~^\s*\*\s*~', $line))
						} // END if(version_compare($version, TWOCLICK_SOCIALMEDIA_BUTTONS_VERSION,">"))
					} // END foreach($changelog as $index => $line)

					if($ul) {
						echo '</ul><div style="clear: left;"></div>';
					} // END if($ul)


					echo '</div>';
				} // END if(preg_match($regexp, $data, $matches))
			} // END if($data)
		} // END function _update_notice()
	} // class WP_Share_To_Xing

	new WP_Share_To_Xing();
} // if(!class_exists('WP_Share_To_Xing'))