<?php
/**
 * @package Admin
 */

/**
 * This class handles the pointers for the GA for WP plugin.
 */
class GA_Pointer {

	/**
	 * Class constructor.
	 */
	function __construct() {
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue' ) );
	}

	/**
	 * Enqueue styles and scripts needed for the pointers.
	 */
	function enqueue() {
		$options = get_option( 'Yoast_Google_Analytics' );
		if ( !isset( $options['tracking_popup'] ) && !isset( $_GET['allow_tracking'] ) ) {
			wp_enqueue_style( 'wp-pointer' );
			wp_enqueue_script( 'jquery-ui' );
			wp_enqueue_script( 'wp-pointer' );
			wp_enqueue_script( 'utils' );
			add_action( 'admin_print_footer_scripts', array( $this, 'tracking_request' ) );
		}
	}

	/**
	 * Shows a popup that asks for permission to allow tracking.
	 */
	function tracking_request() {
		$id      = '#wpadminbar';
		$content = '<h3>' . __( 'Help improve Google Analytics for WordPress', 'gawp' ) . '</h3>';
		$content .= '<p>' . __( 'You\'ve just installed Google Analytics for WordPress by Yoast. Please helps us improve it by allowing us to gather anonymous usage stats so we know which configurations, plugins and themes to test with.', 'gawp' ) . '</p>';
		$opt_arr = array(
			'content'  => $content,
			'position' => array( 'edge' => 'top', 'align' => 'center' )
		);
		$button2 = __( 'Allow tracking', 'gawp' );
		$nonce   = wp_create_nonce( 'wpga_activate_tracking' );

		$function2 = 'wpga_store_answer("yes","'.$nonce.'");';
		$function1 = 'wpga_store_answer("no","'.$nonce.'");';

		$this->print_scripts( $id, $opt_arr, __( 'Do not allow tracking', 'gawp' ), $button2, $function2, $function1 );
	}

	/**
	 * Load a tiny bit of CSS in the head
	 */
	function admin_head() {
		?>
	<style type="text/css" media="screen">
		#pointer-primary {
			margin: 0 5px 0 0;
		}
	</style>
	<?php
	}

	/**
	 * Prints the pointer script
	 *
	 * @param string      $selector         The CSS selector the pointer is attached to.
	 * @param array       $options          The options for the pointer.
	 * @param string      $button1          Text for button 1
	 * @param string|bool $button2          Text for button 2 (or false to not show it, defaults to false)
	 * @param string      $button2_function The JavaScript function to attach to button 2
	 * @param string      $button1_function The JavaScript function to attach to button 1
	 */
	function print_scripts( $selector, $options, $button1, $button2 = false, $button2_function = '', $button1_function = '' ) {
		?>
	<script type="text/javascript">
		//<![CDATA[
        function wpga_store_answer( input, nonce ) {
            var wpga_tracking_data = {
                action : 'wpga_tracking_data',
                allow_tracking : input,
                nonce: nonce
            }
            jQuery.post( ajaxurl, wpga_tracking_data, function( response ) {
                jQuery('#wp-pointer-0').remove();
            } );
        }

        (function ($) {
			var gawp_pointer_options = <?php echo json_encode( $options ); ?>, setup;

			gawp_pointer_options = $.extend(gawp_pointer_options, {
				buttons:function (event, t) {
					button = jQuery('<a id="pointer-close" style="margin-left:5px" class="button-secondary">' + '<?php echo $button1; ?>' + '</a>');
					button.bind('click.pointer', function () {
						t.element.pointer('close');
					});
					return button;
				},
				close:function () {
				}
			});

			setup = function () {
				$('<?php echo $selector; ?>').pointer(gawp_pointer_options).pointer('open');
				<?php if ( $button2 ) { ?>
					jQuery('#pointer-close').after('<a id="pointer-primary" class="button-primary">' + '<?php echo $button2; ?>' + '</a>');
					jQuery('#pointer-primary').click(function () {
						<?php echo $button2_function; ?>
					});
					jQuery('#pointer-close').click(function () {
							<?php echo $button1_function; ?>
					});
					<?php } ?>
			};

			if (gawp_pointer_options.position && gawp_pointer_options.position.defer_loading)
				$(window).bind('load.wp-pointers', setup);
			else
				$(document).ready(setup);
		})(jQuery);
		//]]>
	</script>
	<?php
	}
}

$ga_pointer = new GA_Pointer;
