<?php
/*
  Plugin Name: Gerador de Etiquetas WooCommerce
  Plugin URI: http://www.fernandoacosta.net
  Description: Um plugin simples para impressão de etiquetas do WooCommerce para envio por Correios.
  Version: 1.0
  Author: Fernando Acosta
  Author URI: http://fernandoacosta.net
  License: GPL v3

	Plugin Simples.
*/
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}


// include generate
include_once( 'generator.php' );


	/**
	 * abb option to bulk actions
	*/
	function bulk_admin_etiqueta_footer() {
		global $post_type;

		if ( 'shop_order' == $post_type ) {
			?>
			<script type="text/javascript">
			jQuery(function() {
				jQuery('<option>').val('gerar_etiqueta').text('<?php _e( 'Gerar etiquetas', 'woocommerce' )?>').appendTo("select[name='action']");
				jQuery('<option>').val('gerar_etiqueta').text('<?php _e( 'Gerar etiquetas', 'woocommerce' )?>').appendTo("select[name='action2']");
			});
			</script>
			<?php
		}
	}


/**
 * Process the new bulk actions for get order IDs
 */
function bulk_action_etiqueta() {

	$wp_list_table = _get_list_table( 'WP_Posts_List_Table' );
	$action = $wp_list_table->current_action();

	// Bail out if this is not a status-changing action
	if ( strpos( $action, 'gerar_' ) === false ) {
		return;
	}

	$new_status    = substr( $action, 5 ); // get the status name from action
	$report_action = 'gerada' . $new_status;

	$changed = 0;

	$post_ids = array_map( 'absint', (array) $_REQUEST['post'] );

	$sendback = add_query_arg( array( 'post_type' => 'shop_order', $report_action => true, 'changed' => $changed, 'ids' => join( ',', $post_ids ) ), '' );
	wp_redirect( $sendback ); // esse é o padrão

	exit();
}


/**
 * add notice when shipping label has been created
*/
function bulk_action_etiqueta_notices() {
		global $post_type, $pagenow;

		// Bail out if not on shop order list page
		if ( 'edit.php' !== $pagenow || 'shop_order' !== $post_type ) {
			return;
		}
			if ( isset( $_REQUEST[ 'gerada_etiqueta' ] ) ) {

				$number = isset( $_REQUEST['changed'] ) ? absint( $_REQUEST['changed'] ) : 0;
				$message = 'Etiquetas geradas em uma nova aba. Se não abrir, <a href="'. get_admin_url() .'admin-ajax.php/?action=get_etiquetas_pdf&ids='.$_GET['ids'].'" target="_blank">clique aqui</a>.';
				echo '<div class="updated"><p>' . $message . '</p></div>';
			}
}

/**
 * open labels PDF window
*/
function custom_admin_etiqueta_js() {

	if ( $_GET['gerada_etiqueta'] == "1" ) {
    echo '<script type="text/javascript" language="Javascript">window.open("'. get_admin_url() .'admin-ajax.php/?action=get_etiquetas_pdf&ids='.$_GET['ids'].'")</script>';
	}
}


add_action('wp_ajax_get_etiquetas_pdf', 'get_etiquetas_pdf');
add_action('wp_ajax_nopriv_get_etiquetas_pdf', 'get_etiquetas_pdf');
add_action( 'admin_footer', 'bulk_admin_etiqueta_footer', 1000 );
add_action( 'load-edit.php', 'bulk_action_etiqueta' );
add_action( 'admin_notices', 'bulk_action_etiqueta_notices' );
add_action('admin_head', 'custom_admin_etiqueta_js');