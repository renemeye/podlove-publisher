<?php
namespace Podlove\Settings;
use \Podlove\Model;

class Dashboard {

	static $pagehook;

	public function __construct() {

		// use \Podlove\Podcast_Post_Type::SETTINGS_PAGE_HANDLE to replace
		// default first item name
		Dashboard::$pagehook = add_submenu_page(
			/* $parent_slug*/ \Podlove\Podcast_Post_Type::SETTINGS_PAGE_HANDLE,
			/* $page_title */ __( 'Dashboard', 'podlove' ),
			/* $menu_title */ __( 'Dashboard', 'podlove' ),
			/* $capability */ 'administrator',
			/* $menu_slug  */ \Podlove\Podcast_Post_Type::SETTINGS_PAGE_HANDLE,
			/* $function   */ array( $this, 'settings_page' )
		);

		add_action( Dashboard::$pagehook, function () {

			wp_enqueue_script( 'postbox' );
			add_screen_option( 'layout_columns', array(
				'max' => 2, 'default' => 2
			) );

			wp_register_script(
				'cornify-js',
				\Podlove\PLUGIN_URL . '/js/admin/cornify.js'
			);
			wp_enqueue_script( 'cornify-js' );
		} );
	}

	public static function about_meta() {
		?>
		<ul>
			<li>
				<a href="<?php echo admin_url( 'admin.php?page=podlove_Support_settings_handle' ) ?>">Report Bugs</a>
			</li>
			<li>
				<a target="_blank" href="https://trello.com/board/podlove-publisher/508293f65573fa3f62004e0a">See what we're working on</a>
			</li>
			<li>
				<script type="text/javascript">
				/* <![CDATA[ */
				    (function() {
				        var s = document.createElement('script'), t = document.getElementsByTagName('script')[0];
				        s.type = 'text/javascript';
				        s.async = true;
				        s.src = 'http://api.flattr.com/js/0.6/load.js?mode=auto';
				        t.parentNode.insertBefore(s, t);
				    })();
				/* ]]> */</script>
				<a class="FlattrButton" style="display:none;" rev="flattr;button:compact;" href="http://wordpress.org/extend/plugins/podlove-podcasting-plugin-for-wordpress/"></a>
				<a href="http://www.cornify.com" onclick="cornify_add();return false;" style="text-decoration: none; color: #A7A7A7; float: right; font-size: 20px; line-height: 20px;"><i class="podlove-icon-heart"></i></a>
				<noscript><a href="http://flattr.com/thing/728463/Podlove-Podcasting-Plugin-for-WordPress" target="_blank">
				<img src="http://api.flattr.com/button/flattr-badge-large.png" alt="Flattr this" title="Flattr this" border="0" /></a></noscript>
			</li>
		</ul>
		<?php
	}

	public static function settings_page() {
		add_meta_box( Dashboard::$pagehook . '_about', __( 'About', 'podlove' ), '\Podlove\Settings\Dashboard::about_meta', Dashboard::$pagehook, 'side' );		
		add_meta_box( Dashboard::$pagehook . '_statistics', __( 'At a glance', 'podlove' ), '\Podlove\Settings\Dashboard::statistics', Dashboard::$pagehook, 'normal' );
		add_meta_box( Dashboard::$pagehook . '_feeds', __( 'Podcast feeds', 'podlove' ), '\Podlove\Settings\Dashboard::feeds', Dashboard::$pagehook, 'normal' );
		add_meta_box( Dashboard::$pagehook . '_validation', __( 'Validate Podcast Files', 'podlove' ), '\Podlove\Settings\Dashboard::validate_podcast_files', Dashboard::$pagehook, 'normal' );

		do_action( 'podlove_dashboard_meta_boxes' );

		?>
		<div class="wrap">
			<?php screen_icon( 'podlove-podcast' ); ?>
			<h2><?php echo __( 'Podlove Dashboard', 'podlove' ); ?></h2>

			<div id="poststuff" class="metabox-holder has-right-sidebar">
				
				<!-- sidebar -->
				<div id="side-info-column" class="inner-sidebar">
					<?php do_action( 'podlove_settings_before_sidebar_boxes' ); ?>
					<?php do_meta_boxes( Dashboard::$pagehook, 'side', NULL ); ?>
					<?php do_action( 'podlove_settings_after_sidebar_boxes' ); ?>
				</div>

				<!-- main -->
				<div id="post-body" class="has-sidebar">
					<div id="post-body-content" class="has-sidebar-content">
						<?php do_action( 'podlove_settings_before_main_boxes' ); ?>
						<?php do_meta_boxes( Dashboard::$pagehook, 'normal', NULL ); ?>
						<?php do_meta_boxes( Dashboard::$pagehook, 'additional', NULL ); ?>
						<?php do_action( 'podlove_settings_after_main_boxes' ); ?>						
					</div>
				</div>

				<br class="clear"/>

			</div>

			<!-- Stuff for opening / closing metaboxes -->
			<script type="text/javascript">
			jQuery( document ).ready( function( $ ){
				// close postboxes that should be closed
				$( '.if-js-closed' ).removeClass( 'if-js-closed' ).addClass( 'closed' );
				// postboxes setup
				postboxes.add_postbox_toggles( '<?php echo \Podlove\Podcast_Post_Type::SETTINGS_PAGE_HANDLE; ?>' );
			} );
			</script>

			<form style='display: none' method='get' action=''>
				<?php
				wp_nonce_field( 'closedpostboxes', 'closedpostboxesnonce', false );
				wp_nonce_field( 'meta-box-order', 'meta-box-order-nonce', false );
				?>
			</form>

		</div>
		<?php
	}

	/**
	 * Look for errors in podcast settings.
	 * 
	 * @return array list of error messages
	 */
	public static function get_podcast_setting_warnings() {
		
		$warnings = array();
		$podcast = Model\Podcast::get_instance();

		$required_attributes = array(
			'title'               => __( 'Your podcast needs a title.', 'podlove' ),
			'media_file_base_uri' => __( 'Your podcast needs a base URL for file storage.', 'podlove' ),
		);
		$required_attributes = apply_filters( 'podlove_podcast_required_attributes', $required_attributes );

		foreach ( $required_attributes as $attribute => $error_text ) {
			if ( ! $podcast->$attribute )
				$warnings[] = $error_text;
		}

		return $warnings;
	}

	public static function duration_to_seconds( $timestring ) {
		$time 		= strtotime($timestring);
		$seconds    = date( "s", $time);
		$minutes    = date( "i", $time);
		$hours	    = date( "H", $time);

		return $seconds + $minutes * 60 + $hours * 3600;
	}

	public static function statistics() {

		$episodes     = Model\Episode::allByTime();
		$media_files  = Model\MediaFile::all();

		$episode_edit_url = site_url() . '/wp-admin/edit.php?post_type=podcast';

		// For Media Files the total and average file size will be calculated
		$mediafile_total_size = 0;
		$mediafile_counted = 0;

		/*
         *	Episode Statistics
		 */
		$prev_post = null;
		$counted_episodes = 0;
		$time_stamp_differences = array();
		$episode_durations = array();
		$episode_status_count = array(
			'publish' => 0,
			'private' => 0,
			'future' => 0,
			'draft' => 0,
		);

		foreach ( $episodes as $episode_key => $episode ) {

			if ( !$episode->is_valid() )
				continue;

			$post = get_post( $episode->post_id );
			$counted_episodes++;

			// duration in seconds
			if ( self::duration_to_seconds( $episode->duration ) > 0 )
				$episode_durations[$post->ID] = self::duration_to_seconds( $episode->duration );

			// count by post status
			if (!isset($episode_status_count[$post->post_status])) {
				$episode_status_count[$post->post_status] = 1;
			} else {
				$episode_status_count[$post->post_status]++;
			}

			// determine time in days since last publication
			if ($prev_post) {
				$timestamp_current_episode = new \DateTime( $post->post_date );
				$timestamp_next_episode = new \DateTime( $prev_post->post_date );
				$time_stamp_differences[$post->ID] = $timestamp_current_episode->diff($timestamp_next_episode)->days;
			}

			$prev_post = $post;
		}

		$episodes_total_length = array_sum($episode_durations);
		// Calculating average episode in seconds
		$episodes_average_episode_length = ( $counted_episodes > 0 ? round(array_sum($episode_durations) / count($episode_durations)) : 0 );
		// Calculate average tim until next release in days
		$average_days_between_releases = ( $counted_episodes > 0 ? round(array_sum($time_stamp_differences) / count($time_stamp_differences)) : 0 );

		/*
         *	Media Files
		 */
		foreach ( $media_files as $media_file_key => $media_file) {
			if ( $media_file->size <= 0 ) // Neglect empty files
				continue;

			$mediafile_total_size = $mediafile_total_size + $media_file->size;
			$mediafile_counted++;
		}

		$mediafile_average_size = ( $mediafile_counted > 0 ? $mediafile_total_size / $mediafile_counted : 0 );
		?>
		<div class="podlove-dashboard-statistics-wrapper">
			<h4>Episodes</h4>
			<table cellspacing="0" cellpadding="0" class="podlove-dashboard-statistics">
				<tr>
					<td class="podlove-dashboard-number-column">
						<a href="<?php echo $episode_edit_url; ?>&amp;post_status=publish"><?php echo $episode_status_count['publish']; ?></a>
					</td>
					<td>
						<span style="color: #2c6e36;"><?php echo __( 'Published', 'podlove' ); ?></span>
					</td>
				</tr>
				<tr>
					<td class="podlove-dashboard-number-column">
						<a href="<?php echo $episode_edit_url; ?>&amp;post_status=private"><?php echo $episode_status_count['private']; ?></a>
					</td>
					<td>
						<span style="color: #b43f56;"><?php echo __( 'Private', 'podlove' ); ?></span>
					</td>
				</tr>
				<tr>
					<td class="podlove-dashboard-number-column">
						<a href="<?php echo $episode_edit_url; ?>&amp;post_status=future"><?php echo $episode_status_count['future']; ?></a>
					</td>
					<td>
						<span style="color: #a8a8a8;"><?php echo __( 'To be published', 'podlove' ); ?></span>
					</td>
				</tr>
				<tr>
					<td class="podlove-dashboard-number-column">
						<a href="<?php echo $episode_edit_url; ?>&amp;post_status=draft"><?php echo $episode_status_count['draft']; ?></a>
					</td>
					<td>
						<span style="color: #c0844c;"><?php echo __( 'Drafts', 'podlove' ); ?></span>
					</td>
				</tr>
				<tr>
					<td class="podlove-dashboard-number-column podlove-dashboard-total-number">
						<a href="<?php echo $episode_edit_url; ?>"><?php echo $counted_episodes; ?></a>
					</td>
					<td class="podlove-dashboard-total-number">
						<?php echo __( 'Total', 'podlove' ); ?>
					</td>
				</tr>
			</table>
		</div>
		<div class="podlove-dashboard-statistics-wrapper">
			<h4><?php echo __('Statistics', 'podlove') ?></h4>
			<table cellspacing="0" cellpadding="0" class="podlove-dashboard-statistics">
				<tr>
					<td class="podlove-dashboard-number-column">
						<?php echo gmdate("H:i:s", $episodes_average_episode_length ); ?>
					</td>
					<td>
						<?php echo __( 'is the average length of an episode', 'podlove' ); ?>.
					</td>
				</tr>
				<tr>
					<td class="podlove-dashboard-number-column">
						<?php
							$days = round($episodes_total_length / 3600 / 24, 1);
							echo sprintf(_n('%s day', '%s days', $days, 'podlove'), $days);
						?>
					</td>
					<td>
						<?php echo __( 'is the total playback time of all episodes', 'podlove' ); ?>.
					</td>
				</tr>
				<tr>
					<td class="podlove-dashboard-number-column">
						<?php echo \Podlove\format_bytes($mediafile_average_size, 1); ?>
					</td>
					<td>
						<?php echo __( 'is the average media file size', 'podlove' ); ?>.
					</td>
				</tr>
				<tr>
					<td class="podlove-dashboard-number-column">
						<?php echo \Podlove\format_bytes($mediafile_total_size, 1); ?>
					</td>
					<td>
						<?php echo __( 'is the total media file size', 'podlove' ); ?>.
					</td>
				</tr>
				<tr>
					<td class="podlove-dashboard-number-column">
						<?php echo sprintf(_n('%s day', '%s days', $average_days_between_releases, 'podlove'), $average_days_between_releases); ?>
					</td>
					<td>
						<?php echo __( 'is the average interval until a new episode is released', 'podlove' ); ?>.
					</td>
				</tr>
				<?php do_action('podlove_dashboard_statistics'); ?>
			</table>
		</div>
		<p>
			<?php echo sprintf( __('You are using %s', 'podlove'), '<strong>Podlove Publisher ' . \Podlove\get_plugin_header( 'Version' ) . '</strong>'); ?>.
		</p>
		<?php
	}

	public static function feeds() {
		$feeds = \Podlove\Model\Feed::all();
		?>

			<input id="revalidate_feeds" type="button" class="button button-primary" value="<?php echo __( 'Revalidate Feeds', 'podlove' ); ?>">

			<table id="dashboard_feed_info">
				<thead>
					<tr>
						<th>Name</th>
						<th>Slug</th>
						<th>Last Modification</th>
						<th>Entries</th>
						<th>Size of Feed (gzip / uncompressed)</th>
						<th>Validation</th>
					</tr>
				</thead>
				<tbody>
					<?php
						foreach ($feeds as $feed_key => $feed) {
							$number_of_items = count( $feed->post_ids() );

							$feed_request = get_transient( 'podlove_dashboard_feed_info_' . $feed->id );
							if ( false === $feed_request ) {
								$feed_request = self::request_feed( $feed->get_subscribe_url() );
								set_transient( 'podlove_dashboard_feed_info_' . $feed->id, 
											  $feed_request,
											  3600*24 );
							}

							$feed_validation = get_transient( 'podlove_dashboard_feed_validation_' . $feed->id );
							if ( false === $feed_validation ) {
								$feed_validation = self::validate_feed( $feed->get_subscribe_url() );
								set_transient( 'podlove_dashboard_feed_validation_' . $feed->id, 
											  $feed_validation,
											  3600*24 );
							}							 

							$feed_header = $feed_request['headers'];
							$feed_body = $feed_request['body'];

							$source  = "<tr>\n";
							$source .= "<td><a href='" . $feed->get_subscribe_url() . "'>" . $feed->name ."</a></td>";
							$source .= "<td class='center'>" . $feed->slug . "</td>";
							$source .= "<td class='center'>" . $feed_header['last-modified'] ."</td>";
							$source .= "<td class='center'>" . $number_of_items ."</td>";
							$source .= "<td class='center'>" .  strlen( gzdeflate( $feed_body , 9 ) ) . " / " .  strlen( $feed_body ) . "</td>";
							$source .= "<td class='center' data-feed-id='" . $feed->id . "'>" . $feed_validation . "</td>";
							$source .= "</tr>\n";
							echo $source;
						}
					?>
				</tbody>
			</table>
		<?php
	}

	public static function request_feed( $url ) {
		$curl = new \Podlove\Http\Curl();
		$curl->request( $url, array(
			'headers' => array( 'Content-type'  => 'application/json' ),
			'timeout' => 10,
			'compress' => true,
			'decompress' => false,
			'sslcertificates' => '', // Set both options to '' to avoid errors
			'_redirection' => ''
		) );
		return $curl->get_response();
	}

	public static function get_feed_validation( $url ) {

		$curl = new \Podlove\Http\Curl();
		$curl->request( "http://validator.w3.org/feed/check.cgi?output=soap12&url=http://freakshow.fm/feed/m4a/", array(
			'headers' => array( 'Content-type'  => 'application/soap+xml' ),
			'timeout' => 15,
			'compress' => true,
			'decompress' => false,
			'sslcertificates' => '', // Set both options to '' to avoid errors
			'_redirection' => ''
		) );
		$response = $curl->get_response();

		if( strpos( $response['body'], 'faultcode' ) )
			return FEED_VALIDATION_INACTIVE;

		$xml = simplexml_load_string( $response['body'] );
		$namespaces = $xml->getNamespaces( true );
		$soap = $xml->children( $namespaces['env'] );
		$warning_and_error_list = $soap->Body->children( $namespaces['m'] )->children( $namespaces['m'] );

		$warning_list = array();
		$error_list = array();

		// Getting Warnings
		foreach ( $warning_and_error_list->warnings->warninglist->children()  as $warning_key => $warning  ) {
			$warning_list[] = get_object_vars( $warning ); // Converting object to array here to have a consitent data structure
		}

		foreach ( $warning_and_error_list->errors->errorlist->children()  as $error_key => $error  ) {
			$error_list[] = get_object_vars( $error ); // Converting object to array here to have a consitent data structure
		}

		return array(	
						'validity'				=> $warning_and_error_list->validity->__toString(),
						'number_of_errors' 		=> $warning_and_error_list->errors->errorcount->__toString(),
						'number_of_warnings'	=> $warning_and_error_list->warnings->warningcount->__toString(),
						'errors'				=> $error_list,
						'warnings'				=> $warning_list
					);

	}

	public static function validate_feed( $url ) {

		define( 'FEED_VALIDATION_OK', '<i class="clickable podlove-icon-ok"></i>' );
		define( 'FEED_VALIDATION_INACTIVE', '<i class="podlove-icon-minus"></i>' );
		define( 'FEED_VALIDATION_ERROR', '<i class="clickable podlove-icon-remove"></i>' );

		$validation = self::get_feed_validation( $url );

		print_r($validation);

		return ( $validation['validity'] == 'true' ? FEED_VALIDATION_OK : FEED_VALIDATION_ERROR );
	}

	public static function validate_podcast_files() {
		
		$podcast = Model\Podcast::get_instance();
		?>
		<div id="asset_validation">
			<?php
			$episodes = Model\Episode::all( 'ORDER BY slug DESC' );
			$assets   = Model\EpisodeAsset::all();

			$header = array( __( 'Episode', 'podlove' ) );
			foreach ( $assets as $asset ) {
				$header[] = $asset->title;
			}
			$header[] = __( 'Status', 'podlove' );

			define( 'ASSET_STATUS_OK', '<i class="clickable podlove-icon-ok"></i>' );
			define( 'ASSET_STATUS_INACTIVE', '<i class="podlove-icon-minus"></i>' );
			define( 'ASSET_STATUS_ERROR', '<i class="clickable podlove-icon-remove"></i>' );
			?>

			<h4><?php echo $podcast->title ?></h4>

			<input id="revalidate_assets" type="button" class="button button-primary" value="<?php echo __( 'Revalidate Assets', 'podlove' ); ?>">

			<table id="asset_status_dashboard">
				<thead>
					<tr>
						<?php foreach ( $header as $column_head ): ?>
							<th><?php echo $column_head ?></th>
						<?php endforeach; ?>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $episodes as $episode ): ?>
						<?php 
						$post_id = $episode->post_id;
						$post = get_post( $post_id );

						if ( ! $episode || ! $episode->is_valid() )
							continue;
						?>
						<tr>
							<td>
								<a href="<?php echo get_edit_post_link( $episode->post_id ) ?>"><?php echo $episode->slug ?></a>
							</td>
							<?php $media_files = $episode->media_files(); ?>
							<?php foreach ( $assets as $asset ): ?>
								<?php 
								$files = array_filter( $media_files, function ( $file ) use ( $asset ) {
									return $file->episode_asset_id == $asset->id;
								} );
								$file = array_pop( $files );
								?>
								<td style="text-align: center; font-weight: bold; font-size: 20px" data-media-file-id="<?php echo $file ? $file->id : '' ?>">
									<?php
									if ( ! $file ) {
										echo ASSET_STATUS_INACTIVE;
									} elseif ( $file->size > 0 ) {
										echo ASSET_STATUS_OK;
									} else {
										echo ASSET_STATUS_ERROR;
									}
									?>
								</td>
							<?php endforeach; ?>
							<td>
								<?php echo $post->post_status ?>
							</td>
							<!-- <td>buttons</td> -->
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		</div>

		<style type="text/css">
		#validation h4 {
			font-size: 20px;
		}

		#validation .episode {
			margin: 0 0 15px 0;
		}

		#validation .slug {
			font-size: 18px;
			margin: 0 0 5px 0;
		}

		#validation .warning {
			color: maroon;
		}

		#validation .error {
			color: red;
		}
		</style>
		<?php
	}

}
