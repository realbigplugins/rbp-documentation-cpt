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
			
			<?php 
	
			// Bundles are their own Downloads, therefore are not linked to the Documentation like we need
			// Here we loop through each item, find Bundles, grab their Downloads, and remove the Bundle Item from our version of teh Cart before adding back in the individual Bundled Items in a format similar enough to the Cart for our old code to work
			$bundled_items = array();
			foreach ( $cart_items as $index => $item ) : 
	
				$download_id = $item['id'];
	
				if ( ! edd_is_bundled_product( $download_id ) ) continue;
	
				foreach( edd_get_bundled_products( $download_id ) as $bundled_item ) {
					
					$bundled_download_id = edd_get_bundle_item_id( $bundled_item );
					$bundled_price_id = edd_get_bundle_item_price_id( $bundled_item );
					
					$bundled_items[] = array(
						'id' => $bundled_download_id,
						'item_number' => array(
							'options' => array(
								'price_id' => $bundled_price_id,
							),
						),
					);
					
				}
	
				// Remove Bundle from the List, as we are going to add the individual items after this loop
				unset( $cart_items[ $index ] );
	
			endforeach;
	
			$cart_items = $cart_items + $bundled_items;

			foreach ( $cart_items as $item ) :

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