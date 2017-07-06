<?php
/**
 * Provides helper functions.
 *
 * @since	  1.0.0
 *
 * @package	CPT_Documentation_Plugin
 * @subpackage CPT_Documentation_Plugin/core
 */
if ( ! defined( 'ABSPATH' ) ) {
	die;
}

/**
 * Returns the main plugin object
 *
 * @since		1.0.0
 *
 * @return		CPT_Documentation_Plugin
 */
function RBMDOCUMENTATIONCPT() {
	return CPT_Documentation_Plugin::instance();
}

/**
 * Adds a list of Documentation Links for each Download in the Purchase
 * 
 * @param		integer $payment_id Payment ID
 *                                     
 * @since		1.0.1
 * @return		HTML
 */
function edd_rbp_documentation_links( $payment_id ) {

	$payment = new EDD_Payment( $payment_id );
	$cart_items = $payment->cart_details;
	
	ob_start();

	if ( $cart_items ) : ?>
		
		<ul>

			<?php foreach ( $cart_items as $item ) :

				$download_id = $item['id'];
	
				if ( ! $documentations = rbm_cpts_get_p2p_children( 'documentation', $download_id ) ) continue;
	
				$documentation = array_shift( $documentations );
	
				$price_id = edd_get_cart_item_price_id( $item );

				$title = '<strong>' . get_the_title( $download_id ) . '</strong>';

				?>

				<li>
					<a href="<?php echo get_permalink( $documentation ); ?>" target="_blank"><?php printf( __( 'Find Documentation for %s Here', 'rbp-documentation-cpt' ), apply_filters( 'edd_email_receipt_download_title', $title, $item, $price_id, $payment_id ) ); ?></a>
				</li>

			<?php endforeach; ?>
			
		</ul>

	<?php endif;
	
	$documentation_list = ob_get_clean();
	
	return $documentation_list;
	
}