<?php
/*-----------------------------------------------------------------------------------

	Here we have all the custom functions for the theme
	Please be extremely cautious editing this file,
	When things go wrong, they tend to go wrong in a big way.
	You have been warned!

-------------------------------------------------------------------------------------*/

// Number of product on shop page before pagination

add_filter( 'loop_shop_per_page', create_function( '$cols', 'return 20;' ), 20 );

// Adding text under add to cart button 

add_action( 'woocommerce_after_add_to_cart_form', 'sls_per_yard_message');

function sls_per_yard_message() {
  echo '<p class="qty_in_yards">Quantity in yards</p>';
}

// Adding text above the Price on the single product page 

// add_action( 'woocommerce_single_product_summary', 'sls_retail_discount');

// function sls_retail_discount() {
//   echo '<p class="retail_discount">40% off the retail price</p>';
// }

// Adding Text next to the Price on the single product Page

add_filter( 'woocommerce_get_price_html', 'sls_custom_price_text' );
function sls_custom_price_text( $price ) {
	$message = ' (40% off)';
	return $price . $message;
}

// Add text before regular price and sale price
function sls_rrp_sale_price_html( $price, $product ) {
 
    $has_sale_text = array(
      '<del>' => '<del>Retail Price: ',
      '<ins>' => 'Your Price: <ins>'
    );
    $return_string = str_replace(array_keys( $has_sale_text ), array_values( $has_sale_text ), $price);
    
  
  return $return_string;
}
add_filter( 'woocommerce_get_price_html', 'sls_rrp_sale_price_html', 100, 2 );


// Adding Specific Attribute links above the Add to Cart button on single products

add_action( 'woocommerce_single_product_summary', 'sls_attribute_bullets' );

function sls_attribute_bullets() {
	global $post;
	$attribute_names = array( 'pa_durability', 'pa_repeat', 'pa_finish', 'pa_width' ); // Insert attribute names here

	echo '<ul id="attribut_bullet_list">';

	foreach ( $attribute_names as $attribute_name ) {
		$taxonomy = get_taxonomy( $attribute_name );

		if ( $taxonomy && ! is_wp_error( $taxonomy ) ) {
			$terms = wp_get_post_terms( $post->ID, $attribute_name );
			$terms_array = array();

	        if ( ! empty( $terms ) ) {
		        foreach ( $terms as $term ) {
			       $archive_link = get_term_link( $term->slug, $attribute_name );
			       // $full_line = '<a href="' . $archive_link . '">'. $term->name . '</a>';
			       $full_line = $term->name;
			       array_push( $terms_array, $full_line );
		        }
		        
		        echo '<li>' . $taxonomy->labels->name . ': ' . implode( $terms_array, ', ' . '</li>' );
		        
	        }

    	}
    }
    echo '</ul>';

}

// Why DDS Message and button on single product page

add_action ('woocommerce_product_meta_end', 'sls_ddf_message');

function sls_ddf_message() {
	echo '<hr>';
	echo '<div id="why_ddf_message">';
	echo '<h3>Why Discounted Designer Fabrics?</h3>';
	echo '<ul>';
	echo '<li>40% Off Retail Price</li>';
	echo '<li>Same Day Fast Shipping</li>';
	echo '<li>Free Samples</li>';
	echo '<li>No Hassle Returns</li>';
	echo '</ul>';
	echo '<a href="/yardage-chart/" target="_blank" class="btn btn-medium btn-primary pull-left button-rs" style="">How much do I need?</a>';
	echo '</div>';
}


// Search WP code for indexing

function my_searchwp_basic_auth_creds() {
	$credentials = array( 
		'username' => 'benheath', 
		'password' => 'SparkWeb5987%(*&' 
	);
	
	return $credentials;
}

add_filter( 'searchwp_basic_auth_creds', 'my_searchwp_basic_auth_creds' );

// Change "You may also like" into "Correlated Products"
 
add_filter('gettext', 'sls_translate_like');
add_filter('ngettext', 'sls_translate_like');
function sls_translate_like($translated) {
$translated = str_ireplace('You may also like', 'Correlated Products', $translated);
return $translated;
}

// Remove related products from single product pages

function wc_remove_related_products( $args ) {
	return array();
}
add_filter('woocommerce_related_products_args','wc_remove_related_products', 10); 

//Change the number of Upsells showing (Correlated Products)

remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_upsell_display', 15 );
add_action( 'woocommerce_after_single_product_summary', 'woocommerce_output_upsells', 15 );

if ( ! function_exists( 'woocommerce_output_upsells' ) ) {
	function woocommerce_output_upsells() {
	    woocommerce_upsell_display( 16,4 ); // Display 3 products in rows of 3
	}
}
add_filter( 'woocommerce_cart_shipping_packages', 'spark_sample_woocommerce_cart_shipping_packages' );
function spark_sample_woocommerce_cart_shipping_packages( $packages ) {
    // Reset the packages
    // Bulky items
	//echo "<pre>";
	//print_r($packages);
    $packages   = array();
    $regular_items = array();
    
    // Sort bulky from regular
	$shipping_items = array();
    foreach ( WC()->cart->get_cart() as $item ) {
        if ($item['sample']){
			$sample_shipping_mode = get_post_meta($item['product_id'], 'sample_shipping_mode', true);
			if ( $item['data']->needs_shipping() && $sample_shipping_mode !== "free" ) {
				$regular_items[] = $item;
			}
		}
		else
		{
			$regular_items[] = $item;
		}
    }
	if ( $regular_items ) {
        $packages[] = array(
            'contents'        => $regular_items,
            'contents_cost'   => array_sum( wp_list_pluck( $regular_items, 'line_total' ) ),
            'applied_coupons' => WC()->cart->applied_coupons,
            'destination'     => array(
                'country'   => WC()->customer->get_shipping_country(),
                'state'     => WC()->customer->get_shipping_state(),
                'postcode'  => WC()->customer->get_shipping_postcode(),
                'city'      => WC()->customer->get_shipping_city(),
                'address'   => WC()->customer->get_shipping_address(),
                'address_2' => WC()->customer->get_shipping_address_2()
            )
        );
    }    
	//echo "<pre>";
	//print_r($packages);
    return $packages;
}

// Register TOP Sidebar Widget Area
register_sidebar( array(
		'name' => __( 'Shop Page Top Widget Area', 'Respondo' ),
		'id' => 'always-on-top',
		'description' => __( 'The widgets in this area will always be above the Shop page content on mobile devices, regardless of the options selected in Appearance > Theme Options.', 'Respondo'),
		'before_widget' => '<aside id="%1$s" class="widget %2$s">',
		'after_widget' => '</aside>',
		'before_title' => '<h3 class="widget-title">',
		'after_title' => '</h3>',
) );

// Change attribute rewrite rules
add_action('woocommerce_register_taxonomy', 'razorfrog_woo_register_taxonomy');
function razorfrog_woo_register_taxonomy() {
	global $razorfrog_woo_attribute_labels;
	$razorfrog_woo_attributes_labels = array();
 
	if ( $attribute_taxonomies = wc_get_attribute_taxonomies() ) {
		foreach ( $attribute_taxonomies as $tax ) {
			if ( $name = wc_attribute_taxonomy_name( $tax->attribute_name ) ) {
				$razorfrog_woo_attribute_labels[ $tax->attribute_label ] = $tax->attribute_name;
				add_filter('woocommerce_taxonomy_args_'.$name, 'razorfrog_woo_taxonomy_args');
			}
		}
	}
}
 
function razorfrog_woo_taxonomy_args($taxonomy_data) {
	global $razorfrog_woo_attribute_labels;
	
	if (isset($taxonomy_data['rewrite']) && is_array($taxonomy_data['rewrite']) && empty($taxonomy_data['rewrite']['slug'])) {
		$taxonomy_data['rewrite']['slug'] = $razorfrog_woo_attribute_labels[ $taxonomy_data['labels']['name'] ];
	}	
	return $taxonomy_data;
}

add_action('woocommerce_after_add_to_cart_form' , 'sls_single_product_share', 100, 1 );

function sls_single_product_share() {
	do_action( 'woocommerce_share' );
}

// Add button on single product page to the Request a sample form

// add_action( 'woocommerce_after_add_to_cart_form', 'sls_request_sample_btn');

// function sls_request_sample_btn() {
//   global $product;
//   $prodSku = $product->get_sku();
//   echo '<a href="/request-a-sample/?productSku=' . $prodSku . '" target="_blank" class="btn btn-medium btn-primary pull-left button-rs">Request Sample</a>';
//   echo '<div style="margin-top: 40px;"></div>';
// }

add_action( 'woocommerce_after_add_to_cart_form', 'sls_sample_product_btn');  
  
function sls_sample_product_btn() {  
echo '<button type="button" class="btn btn-primary" data-toggle="modal" data-target=".bs-example-modal-lg">Request Sample</button>'; 
echo '<div class="modal fade bs-example-modal-lg" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" style="display: none;">';
echo '<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>';
echo '<div class="modal-dialog modal-lg">';
echo '<div class="modal-content">';
echo gravity_form(2, false, false, false, '', true, 12);
echo '</div>';
echo '</div>';
echo '</div>';
}

// Adding Product Sku merge tag for Gravity Form

add_action( 'gform_admin_pre_render', 'sls_add_merge_tags' );
function sls_add_merge_tags( $form ) {
    ?>
    <script type="text/javascript">
        gform.addFilter('gform_merge_tags', 'add_merge_tags');
        function add_merge_tags(mergeTags, elementId, hideAllFields, excludeFieldTypes, isPrepop, option){
            mergeTags["custom"].tags.push({ tag: '{productSku}', label: 'Product SKU' });
            mergeTags["custom"].tags.push({ tag: '{productImage}', label: 'Product Image' });
             
            return mergeTags;
        }
    </script>
    <?php
    //return the form object from the php hook  
    return $form;
}

add_filter( 'gform_replace_merge_tags', 'sls_replace_prod_sku_tag', 10, 7 );
function sls_replace_prod_sku_tag( $text, $form, $entry, $url_encode, $esc_html, $nl2br, $format ) {
    
    $custom_merge_tag = '{productSku}';
    
    if ( strpos( $text, $custom_merge_tag ) === false ) {
        return $text;
    }
    global $product;
    $prodSku = $product->get_sku();
    $text = str_replace( $custom_merge_tag, $prodSku, $text );
    
    return $text;
}

// Merge tag replacement with Product Image URL

add_filter( 'gform_replace_merge_tags', 'sls_replace_prod_image', 10, 7 );
function sls_replace_prod_image( $text, $form, $entry, $url_encode, $esc_html, $nl2br, $format ) {
    
    $custom_merge_tag = '{productImage}';
    
    if ( strpos( $text, $custom_merge_tag ) === false ) {
        return $text;
    }
    global $product;
    	$prodimage = wp_get_attachment_image_src( get_post_thumbnail_id( $product->ID ), 'single-post-thumbnail' );


    $text = str_replace( $custom_merge_tag, $prodimage, $text );
    
    return $text;
}



// Order Thankyou page conversion code

add_action( 'woocommerce_thankyou', 'my_custom_tracking' );

function my_custom_tracking( $order_id ) {

// Lets grab the order
$order = new WC_Order( $order_id );

?>
<!-- Google Code for Orders Conversion Page -->
<script type="text/javascript">
/* <![CDATA[ */
var google_conversion_id = 943276548;
var google_conversion_language = "en";
var google_conversion_format = "3";
var google_conversion_color = "ffffff";
var google_conversion_label = "pNiRCK-d4F4QhITlwQM";
var google_conversion_value = 0.00;
var google_conversion_currency = "USD";
var google_remarketing_only = false;
/* ]]> */
</script>
<script type="text/javascript" src="//www.googleadservices.com/pagead/conversion.js">
</script>
<noscript>
<div style="display:inline;">
<img height="1" width="1" style="border-style:none;" alt="" src="//www.googleadservices.com/pagead/conversion/943276548/?value=0.00&amp;currency_code=USD&amp;label=pNiRCK-d4F4QhITlwQM&amp;guid=ON&amp;script=0"/>
</div>
</noscript>

<?php }



?>