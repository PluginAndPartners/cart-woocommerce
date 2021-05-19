<?php
/**
 * Part of Woo Mercado Pago Module
 * Author - Mercado Pago
 * Developer
 * Copyright - Copyright(c) MercadoPago [https://www.mercadopago.com]
 * License - https://www.gnu.org/licenses/gpl.html GPL version 2 or higher
 *
 * @package MercadoPago
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
						
<div style="text-align: center;">
	
	<div>
	<img style="border: none; 
				display: inline-block; 
				font-size: 14px; 
				font-weight: bold; 
				outline: none; 
				text-decoration: none; 
				text-transform: capitalize; 
				vertical-align: middle; 
				max-width: 100%; 
				width: 168px; 
				height: 168px;
				margin: 0 0 10px;" 
				src="<?php echo $src ?>, <?php echo $qr_image ?>" alt="pix">	
	</div>

	<div style="margin: 0 0 16px;
				border: none; 
				display: inline-block; 
				font-size: 14px; 
				font-weight: bold; 
				outline: none; 
				text-decoration: none; 
				text-transform: capitalize; 
				vertical-align: middle;
				max-width: 100%;">
		<small><?php echo $text_expiration_date.$expiration_date ?></small>
	</div>

	<div>		
		<p style="font-size: 10px;  
					width: 320px; height: 80px; 
					margin-left: auto; 
					margin-right: auto;">
					<small><?php echo $qr_code ?></small>
		</p>
	</div>
	
</div>

