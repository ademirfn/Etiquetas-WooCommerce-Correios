<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
/**
 * build shipping labels PDF
*/



function get_etiquetas_pdf(){

/**
 *
 * WooCommerce
 *
 * Biblioteca para PDF
 *
 */
require_once("dompdf/dompdf_config.inc.php");

$html .= '<!DOCTYPE html>';
$html .= ' <html>';
$html .= ' <head>';
$html .= ' 	<title>Etiquetas Correios</title>';
$html .= ' <style type="text/css">
*{font-size:15px;}
div.one{width:368px; position: absolute; top: 0; height: 142px; border: 1px dotted #fff; background: transparent; }

div.one div{padding:13px 18px 13px 25px;line-height:19px;}

.right {
	left: 379px;
}

html,
@page,
page,
body { padding: 0 !important; margin: 0px !important; position: relative; }

body {
	margin-top: 57px !important;
	margin-left: 17.7px !important;
	margin-right: 17.7px !important;
	font-family: helvetica !important;
}

@page .single-page {
  size: A4 portrait;
}

.single-page {
   page: teacher;
   page-break-after: always;
}

</style>';
$html .= ' </head>';
$html .= ' <body>';
$html .= ' <page><div class="single-page">';

$orders = $_GET['ids'];
$orders = explode(",", $orders);

$i=0; $a=0;
foreach ($orders as $key => $value) {

	$pedido = $value;
	$order = new WC_Order( $pedido );
	// $order = wc_get_order( $value );

	//altura
	$height = 142; // 144 tamanho real
	$top = ( $height + 3 ) * $a;

	//esquerda//direita
	if($i%2){ $alinha = "right"; $a++; }else{ $alinha = "left";  }

	$nome 			= $order->shipping_first_name;
	$sobrenome 		= $order->shipping_last_name;
	$endereco 		= $order->shipping_address_1;
	$endereco2 		= $order->shipping_address_2;
	$cidade 		= $order->shipping_city;
	$uf 			= $order->shipping_state;
	$cep 			= $order->shipping_postcode;

$rates = $order->get_shipping_methods();
foreach ( $rates as $key => $rate ) {
        $tipoEnvio = $rate['method_id'];
            break;
}

$html .= '<div class="one ';
$html .= $alinha;
$html .= '" style="top: ' . $top . 'px;"><div>';
#$html .= '#';
#$html .= str_pad($order->id, 6, "0", STR_PAD_LEFT);
$html .= '<b>DESTINAT&Aacute;RIO: </b>';

if ( $tipoEnvio == 'advanced_free_shipping' ) {
	#$html .= 'Carta Registrada';
} else {
	#$html .= $tipoEnvio;
}
$html .= '</b><br /><p style="font-size: 10px;">';
$html .= $nome ." ". $sobrenome;
$html .= '<br />';
$html .= $endereco;
#$html .= ' - ';


	// campos do Extra Checkout Field
	$numero = $order->shipping_number;
	$bairro = $order->shipping_neighborhood;
  	$html .= $numero;
  	$html .= '<br/>';
	/**
	  * verifica se o campo foi preenchido, evitando que fique espaços em branco na etiqueta
	  */
	if($bairro != ""){
	  	$html .= $bairro;
		$html .= '<br />';
	}
#  	$html .= ' - ';
	// fim dos campos do Extra Checkout Field
/**
  * verifica se o campo foi preenchido, evitando que fique espaços em branco na etiqueta
  */
if($endereco2 != ""){
	$html .= $endereco2;
	$html .= '<br />';
}
$html .= $cidade;
$html .= ' - ';
$html .= $uf;
$html .= '<br />';
$html .= 'CEP: ';
$html .= $cep;
$html .= '<br />';
$html .= '<br />';

/**
  * Verifica se o post tipo página responsável pelo endereço do remente já foi inserido no banco
  * Se foi, exibe o remetente, se não, insere um remetente padrão e exibe
  * O remetente padrão deve ser alterado no painel, nas páginas
  */
$buscaRemetente = mysql_query("SELECT SQL_CACHE * FROM wp_posts WHERE post_password = '123Mudar#$'") or die(mysql_error());
if(mysql_num_rows($buscaRemetente) == 0){
	$insere = mysql_query("INSERT INTO wp_posts (post_author, post_date, post_date_gmt, post_content, post_title, post_excerpt, post_status,  comment_status, ping_status, post_password, post_name, to_ping, pinged, post_modified, post_modified_gmt, post_content_filtered, post_parent, guid, menu_order, post_type, post_mime_type, comment_count) VALUES ('1', '2016-01-27 12:45:09', '2016-01-27 14:45:09', 'Nome da empresa<br>R. Nome da rua, 123 - Centro<br>Cidade - UF<br>CEP: 99888-777', 'Nosso endereço', '', 'publish', 'closed', 'closed', '123Mudar#$', 'nosso-endereco', '', '', '2016-01-27 14:53:51', '2016-01-27 14:53:51', '', '0', 'http://localhost/wordpress/?page_id=', '0', 'page', '', '0')") or die(mysql_error());
	$buscaDeNovo = mysql_query("SELECT * FROM wp_posts WHERE post_password = '123Mudar#$'") or die(mysql_error());
	$r = mysql_fetch_array($buscaDeNovo);
	$html .= '<b>REMETENTE</b><br>';
	$html .= '<p style="font-size: 10px;">'.$r['post_content'].'</p>';
}else{
	$r = mysql_fetch_array($buscaRemetente);
	$html .= '<b>REMETENTE</b><br>';
	$html .= '<p style="font-size: 10px;">'.$r['post_content'].'</p>';
}

$html .= '</p></div></div>';

if( $i == 13 ){
	$html .= '</div></page><page><div class="single-page">';
	$a = 0;
}

$i++;
	
}
 
$html .= ' </body>';
$html .= '</html>';

// wp_mail( 'contato@fernandoacosta.net', 'Etiqueta', $html );

$dompdf = new DOMPDF();
$dompdf->load_html( $html );
$dompdf->render();
$dompdf->set_paper( 'a4', 'landscape' );
$dompdf->stream( "etiqueta.pdf" , array( 'Attachment'=>0 ) );

	exit;
}
