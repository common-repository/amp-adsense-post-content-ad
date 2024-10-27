<?php
/*
Plugin Name: AMP AdSense Post Content Ad
Plugin URI: https://kivabe.com/en/adsense-amp-post-content-ad-wordpress-plugin/
Description: This plugin is used to add AdSense ad inside AMP post content.
Author: Shariar
Version: 1.0.0
Author URI: http://kivabe.com/
*/

if(! defined('ABSPATH')){ exit; }

/**
 * Class for AMP_Adsense_Post_Content_Ad
 */
class AMP_Adsense_Post_Content_Ad {
	public $option;

	public function __construct() {

		add_action('admin_menu', array( $this, 'ad_amp_in_content_Admin'),1);
		add_action( 'pre_amp_render_post', array( $this, 'is_amp_content_filter' ));
		add_filter( 'plugin_action_links_' . plugin_basename(__FILE__), array( $this, 'ad_amp_in_content_actions' ));
		add_action('init', array( $this, 'ad_amp_post_content_data_update'));

		add_action('admin_init', array( $this, 'ad_amp_admin_notice_ignore'));
		add_action('admin_notices', array( $this, 'ad_amp_admin_notice'));


	}

	public function ad_options( $option ){
		return get_option( $option );
	}

	public function is_amp_content_filter() { 
		add_filter( 'the_content', array($this, 'is_amp_adsense_in_content' ) );
	}

	public function is_amp_adsense_in_content( $content ) {  

 		// Ad code. This is responsive as per Google guidelines for AdSense for AMP. 
		$ad_code = '<amp-ad width="100vw" height=320 type="adsense" data-ad-client="' . $this->ad_options('_amp_ad_pub_id') . '" data-ad-slot="' .  $this->ad_options('_amp_ad_ad_slot') . '" data-auto-format="rspv" data-full-width><div overflow></div></amp-ad>';

		$wp_amp_ad_para_pos = $this->ad_options('_amp_ad_para_pos');

 		// Insert Adsense ad between the content, after paragraph $wp_amp_ad_para_pos
		$new_content = $this->ad_insert_after_paragraph( $ad_code, $wp_amp_ad_para_pos, $content );

		return $new_content;

	}

	public function ad_insert_after_paragraph( $insertion, $paragraph_id, $content ) {
		$closing_p = '</p>';
		$counters = 0;
		$p_index = $paragraph_id;

		if ( (int)$p_index === 0 ) {
			$paragraphs = $insertion.$content; 
			return $paragraphs;
		} else { 
			$paragraphs = explode( $closing_p, $content );
			foreach ( $paragraphs as $index => $paragraph ) {
				if ( trim( $paragraph ) ) {
					$paragraphs[$index] .= $closing_p;
				}
				if ( (int)$p_index - 1 === $index ) {
					$paragraphs[$index] .= $insertion;
				}  
			}

			return implode( '', $paragraphs ); 
		} //  End of (int)$p_index === 0
	} //  End of ad_insert_after_paragraph

	public function ad_amp_in_content_Admin()	{
		if( current_user_can( 'manage_options' ) ) {
			add_options_page('AMP AdSense Content Ad', 'AMP AdSense Content Ad', 'manage_options', basename(__FILE__), array($this, 'ad_amp_in_content_options'));
		}
	
	}
	
	public function ad_amp_in_content_actions( $links ) { 
			$links[] = '<a href="'. esc_url( get_admin_url(null, 'options-general.php?page=amp-adsense-post-content-ad.php') ) .'">' . esc_html__('Settings', 'amp-adsense-post-content-ad'). '</a>'; 
		return $links;
	}


	public function ad_amp_post_content_data_update(){

		if(isset( $_POST['_ad_amp_nonce'] )){
			if(wp_verify_nonce($_POST['_ad_amp_nonce'], '_ad_amp_nonce' )){
				//echo "Nonce has been create successfully"; exit;

				if ( isset( $_POST['amp_content_adSense_ad'] )) {

					if( isset( $_POST['_amp_ad_pub_id'])){
						update_option('_amp_ad_pub_id', sanitize_text_field( $_POST['_amp_ad_pub_id']), true );
					}
					if( isset( $_POST['_amp_ad_ad_slot'])){
						update_option('_amp_ad_ad_slot', sanitize_text_field( $_POST['_amp_ad_ad_slot']), true);
					}
					
					if( isset( $_POST['_amp_ad_para_pos'])){
						update_option('_amp_ad_para_pos', sanitize_text_field( $_POST['_amp_ad_para_pos']), true );	
					} 
					
				} 
			}
		}

	}


	// Admin Options
	public function ad_amp_in_content_options(){
		
		if( ! current_user_can( 'manage_options' ) ) {
			exit();
		}

		if(isset( $_POST['_ad_amp_nonce'] )){
			if(wp_verify_nonce($_POST['_ad_amp_nonce'], '_ad_amp_nonce' )){
				if ( isset( $_POST['amp_content_adSense_ad'] )) {
					echo '<h3 style="color:green;">Data Updated.</h3>';
				}
			}
		}

		?>

		<div class="wrap">
			<form method="post" id="amp_adSense_in_post">
				<?php wp_nonce_field( '_ad_amp_nonce', '_ad_amp_nonce' ); ?>
				<fieldset class="options">
					<table class="form-table wp-list-table widefat plugins">
						<tr valign="top">
							<td>
								<h2><?php echo esc_html__('AMP AdSense Content Ad Settings', 'amp-adsense-post-content-ad');?></h2>
								<h4><?php echo esc_html__('AdSense Publisher ID', 'amp-adsense-post-content-ad');?></h4>

								<input 
									name="_amp_ad_pub_id" 
									type="text" 
									id="_amp_ad_pub_id" 
									value="<?php echo sanitize_text_field( $this->ad_options('_amp_ad_pub_id'));?>" 
									size="60" 
									placeholder="ca-pub-xxxxxxxxxxxxxx"
								>
								<br>
								<p><?php echo esc_html__('If Empty, Ad will not show.  Put your AdSense Publisher ID here.', 'amp-adsense-post-content-ad');?></p>

								<h4><?php echo esc_html__('AdSense Ad Slot id', 'amp-adsense-post-content-ad');?></h4>
								<input 
									name="_amp_ad_ad_slot" 
									type="number" 
									id="_amp_ad_ad_slot" 
									value="<?php echo sanitize_text_field( $this->ad_options('_amp_ad_ad_slot'));?>" 
									size="60" 
									placeholder="xxxxxxxxxxx" 
								>
								<br><p><?php echo esc_html__('If Empty, Ad will not show. Put your AdSense Ad Slot id here', 'amp-adsense-post-content-ad');?> </p>

								<h4><?php echo esc_html__('Choose Ad Paragraph position', 'amp-adsense-post-content-ad');?> </h4> 
								<input 
									type="number" 
									size="60" 
									id="_amp_ad_para_pos" 
									name="_amp_ad_para_pos" 
									value="<?php echo sanitize_text_field( $this->ad_options('_amp_ad_para_pos'));?>" 
									placeholder="3"
								> 
								<br>
								<p>
									<?php echo esc_html__('Default value 0,  i.e at the very bigging, it will appear. if you want to show after 1st paragraph, put 1. 2 for after second paragraph. 3 for 3rd paragraph ...', 'amp-adsense-post-content-ad');?>  
				            	</p> 
				            	<p>
				            		<?php echo wp_kses_post('** Please make sure that you have any of those plugin. "<a href="https://wordpress.org/plugins/amp/" target="_blank">AMP for WordPress</a>" plugin by Automattic or "<a target="_blank" href="https://wordpress.org/plugins/accelerated-mobile-pages/">AMP for WP – Accelerated Mobile Pages</a>" by Ahmed Kaludi, Mohammed Kaludi.', 'amp-adsense-post-content-ad');?>
				            	</p>
				            </td>
				        </tr>
				        <tr>
				        	<td>
				        		<input class="button button-primary" type="submit" name="amp_content_adSense_ad" value="Update" /> 
				        	</td>
				        </tr>
				    </table>
				</fieldset>
			</form>  
		</div>
	<?php
	}	





	public function ad_amp_admin_notice() {
	    global $current_user ;
	        $user_id = $current_user->ID;
	    if ( ! get_user_meta($user_id, 'ad_amp_notice_ignore') ) {
	        echo '<div class="updated"><p>';
	        printf(__('
	                    <h4 style="font-size: 20px; color: #5FA52A; font-weight: normal; margin-bottom: 10px; margin-top: 5px;">
	                    AMP AdSense Post Content Ad</h4>

	                    <p> This Plugin have dependency. Please make sure you have installed any of the plugin. "<a href="https://wordpress.org/plugins/amp/" target="_blank">AMP for WordPress</a>" by Automattic or "<a target="_blank" href="https://wordpress.org/plugins/accelerated-mobile-pages/">AMP for WP – Accelerated Mobile Pages</a>" by Ahmed Kaludi, Mohammed Kaludi.
	                   <a style="float: right;" href="%1$s">X</a> </p>'), '?ignore_notice=0');
	        echo "</p></div>";
	    }
	}

	public function ad_amp_admin_notice_ignore() {
	    global $current_user;
	        $user_id = $current_user->ID;
	        if ( isset($_GET['ignore_notice']) && '0' == $_GET['ignore_notice'] ) {
	             add_user_meta($user_id, 'ad_amp_notice_ignore', 'true', true);
	    }
	}

 
}

new AMP_Adsense_Post_Content_Ad();







