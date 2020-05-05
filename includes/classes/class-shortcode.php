<?php
if (!class_exists('ATBDP_Shortcode')):

    class ATBDP_Shortcode
    {

        public function __construct()
        {
            add_shortcode('directorist_search_listing', array($this, 'search_listing'));
            add_shortcode('directorist_search_result', array($this, 'search_result'));
            add_shortcode('directorist_author_profile', array($this, 'author_profile'));
            add_shortcode('directorist_add_listing', array($this, 'add_listing'));
            add_shortcode('directorist_custom_registration', array($this, 'user_registration'));
            add_shortcode('directorist_user_login', array($this, 'custom_user_login'));
            add_shortcode('directorist_user_dashboard', array($this, 'user_dashboard'));
            add_shortcode('directorist_all_listing', array($this, 'all_listing'));
            add_shortcode('directorist_all_categories', array($this, 'all_categories'));
            add_shortcode('directorist_category', array($this, 'atbdp_category'));
            add_shortcode('directorist_all_locations', array($this, 'all_locations'));
            add_shortcode('directorist_location', array($this, 'atbdp_location'));
            add_shortcode('directorist_tag', array($this, 'atbdp_tag'));
            $checkout = new ATBDP_Checkout;
            add_shortcode('directorist_checkout', array($checkout, 'display_checkout_content'));
            add_shortcode('directorist_payment_receipt', array($checkout, 'payment_receipt'));
            add_shortcode('directorist_transaction_failure', array($checkout, 'transaction_failure'));
            
            add_shortcode('directorist_listing_top_area', array( $this, 'directorist_listing_header' ));
            add_shortcode('directorist_listing_custom_fields',array($this,'directorist_custom_field'));
            add_shortcode('directorist_listing_video',array($this,'directorist_listing_video'));
            add_shortcode('directorist_listing_map',array($this,'directorist_listing_map'));
            add_shortcode('directorist_listing_contact_information',array($this,'directorist_listing_contact_information'));
            add_shortcode('directorist_listing_author_info', array($this,'directorist_listing_author_details'));
            add_shortcode('directorist_listing_contact_owner',array($this,'directorist_listing_contact_owner'));
            add_shortcode('directorist_listing_review',array($this,'directorist_listing_review'));
            add_shortcode('directorist_related_listings',array($this,'directorist_related_listings'));

            add_action('wp_ajax_atbdp_custom_fields_listings_front', array($this, 'ajax_callback_custom_fields'), 10, 2);
            add_action('wp_ajax_nopriv_atbdp_custom_fields_listings_front', array($this, 'ajax_callback_custom_fields'), 10, 2);
            add_action('wp_ajax_atbdp_custom_fields_listings_front_selected', array($this, 'ajax_callback_custom_fields'), 10, 2);
            add_action('wp_ajax_nopriv_atbdp_custom_fields_listings_front_selected', array($this, 'ajax_callback_custom_fields'), 10, 2);
        }

        // listing header area
        public function directorist_listing_header() {
            ob_start();
            if ( is_singular( ATBDP_POST_TYPE ) ) {
                global $post;
                $listing_id = $post->ID;
                $fm_plan = get_post_meta($listing_id, '_fm_plans', true);
                /*store all data in an array so that we can pass it to filters for extension to get this value*/
                $listing_info['never_expire'] = get_post_meta($post->ID, '_never_expire', true);
                $listing_info['featured'] = get_post_meta($post->ID, '_featured', true);
                $listing_info['price'] = get_post_meta($post->ID, '_price', true);
                $listing_info['price_range'] = get_post_meta($post->ID, '_price_range', true);
                $listing_info['atbd_listing_pricing'] = get_post_meta($post->ID, '_atbd_listing_pricing', true);
                $listing_info['videourl'] = get_post_meta($post->ID, '_videourl', true);
                $listing_info['listing_status'] = get_post_meta($post->ID, '_listing_status', true);
                $listing_info['tagline'] = get_post_meta($post->ID, '_tagline', true);
                $listing_info['excerpt'] = get_post_meta($post->ID, '_excerpt', true);
                $listing_info['address'] = get_post_meta($post->ID, '_address', true);
                $listing_info['phone'] = get_post_meta($post->ID, '_phone', true);
                $listing_info['email'] = get_post_meta($post->ID, '_email', true);
                $listing_info['website'] = get_post_meta($post->ID, '_website', true);
                $listing_info['zip'] = get_post_meta($post->ID, '_zip', true);
                $listing_info['social'] = get_post_meta($post->ID, '_social', true);
                $listing_info['faqs'] = get_post_meta($post->ID, '_faqs', true);
                $listing_info['manual_lat'] = get_post_meta($post->ID, '_manual_lat', true);
                $listing_info['manual_lng'] = get_post_meta($post->ID, '_manual_lng', true);
                $listing_info['hide_map'] = get_post_meta($post->ID, '_hide_map', true);
                $listing_info['listing_img'] = get_post_meta($post->ID, '_listing_img', true);
                $listing_info['listing_prv_img'] = get_post_meta($post->ID, '_listing_prv_img', true);
                $listing_info['hide_contact_info'] = get_post_meta($post->ID, '_hide_contact_info', true);
                $listing_info['hide_contact_owner'] = get_post_meta($post->ID, '_hide_contact_owner', true);
                $listing_info['expiry_date'] = get_post_meta($post->ID, '_expiry_date', true);
                $display_prv_image = get_directorist_option('dsiplay_prv_single_page', 1);
                $display_slider_image = get_directorist_option('dsiplay_slider_single_page', 1);
                $custom_gl_width = get_directorist_option('gallery_crop_width', 670);
                $custom_gl_height = get_directorist_option('gallery_crop_height', 750);
                $select_listing_map = get_directorist_option('select_listing_map', 'google');
                extract($listing_info);
                /*Prepare Listing Image links*/
                $listing_imgs = (!empty($listing_img) && !empty($display_slider_image)) ? $listing_img : array();
                $image_links = array(); // define a link placeholder variable
                $full_image_links = array(); // define a link placeholder variable
                foreach ($listing_imgs as $id) {
                    $full_image_links[$id] = atbdp_get_image_source($id, 'large');
                    $image_links_thumbnails[$id] = atbdp_get_image_source($id, 'thumbnail'); // store the attachment id and url
                    //@todo; instead of getting a full size image, define a an image size and then fetch that size and let the user change that image size via a hook.
                }
                /*Code for Business Hour Extensions*/
                /*@todo; Make business hour settings compatible to our new settings panel. It is good to prefix all settings of extensions with their prefix*/
                $text247 = get_directorist_option('text247', __('Open 24/7', 'directorist')); // text for 24/7 type listing
                $business_hour_title = get_directorist_option('business_hour_title', __('Business Hour', 'directorist')); // text Business Hour Title
                $bdbh = get_post_meta($listing_id, '_bdbh', true);
                $enable247hour = get_post_meta($listing_id, '_enable247hour', true);
                $disable_bz_hour_listing = get_post_meta($listing_id, '_disable_bz_hour_listing', true);
                $business_hours = !empty($bdbh) ? atbdp_sanitize_array($bdbh) : array(); // arrays of days and times if exist
                /*Code for Business Hour Extensions*/
                $manual_lat = (!empty($manual_lat)) ? floatval($manual_lat) : false;
                $manual_lng = (!empty($manual_lng)) ? floatval($manual_lng) : false;
                $hide_contact_info = !empty($hide_contact_info) ? $hide_contact_info : false;
                $hide_contact_owner = !empty($hide_contact_owner) ? $hide_contact_owner : false;
                /*INFO WINDOW CONTENT*/
                $t = get_the_title();
                $t = !empty($t) ? $t : __('No Title', 'directorist');
                $tg = !empty($tagline) ? esc_html($tagline) : '';
                $ad = !empty($address) ? esc_html($address) : '';
                $image = (!empty($attachment_id[0])) ? "<img src='" . esc_url(wp_get_attachment_image_url($attachment_id[0], 'thumbnail')) . "'>" : '';
                $info_content = "<div class='map_info_window'> <h3>{$t}</h3>";
                $info_content .= "<p> {$tg} </p>";
                $info_content .= $image; // add the image if available
                $info_content .= "<address>{$ad}</address>";
                $info_content .= "<a href='http://www.google.com/maps/place/{$manual_lat},{$manual_lng}' target='_blank'> " . __('View On Google Maps', 'directorist') . "</a></div>";
                /*END INFO WINDOW CONTENT*/
                $map_zoom_level = get_directorist_option('map_zoom_level', 16);
                $disable_map = get_directorist_option('disable_map', 0);
                $disable_sharing = get_directorist_option('disable_sharing', 0);
                $disable_contact_info = get_directorist_option('disable_contact_info', 0);
                $disable_contact_owner = get_directorist_option('disable_contact_owner', 1);
                $is_disable_price = get_directorist_option('disable_list_price');
                $enable_social_share = get_directorist_option('enable_social_share', 1);
                $enable_favourite = get_directorist_option('enable_favourite', 1);
                $enable_report_abuse = get_directorist_option('enable_report_abuse', 1);
                $enable_video_url = get_directorist_option('atbd_video_url', 1);
                $video_label = get_directorist_option('atbd_video_title', __('Video', 'directorist'));
                $p_lnk = get_the_permalink();
                $p_title = get_the_title();
                $featured = get_post_meta(get_the_ID(), '_featured', true);
                $cats = get_the_terms($post->ID, ATBDP_CATEGORY);
                $reviews_count = ATBDP()->review->db->count(array('post_id' => $listing_id)); // get total review count for this post
                $listing_author_id = get_post_field('post_author', $listing_id);
                $display_feature_badge_single = get_directorist_option('display_feature_badge_cart', 1);
                $display_popular_badge_single = get_directorist_option('display_popular_badge_cart', 1);
                $popular_badge_text = get_directorist_option('popular_badge_text', 'Popular');
                $feature_badge_text = get_directorist_option('feature_badge_text', 'Feature');
                $new_badge_text = get_directorist_option('new_badge_text', 'New');
                $enable_new_listing = get_directorist_option('display_new_badge_cart', 1);
                $use_nofollow = get_directorist_option('use_nofollow');
                $enable_review = get_directorist_option('enable_review', 'yes');
                $custom_section_lable = get_directorist_option('custom_section_lable', __('Details', 'directorist'));
                $listing_details_text = get_directorist_option('listing_details_text', __('Listing Details', 'directorist'));
                $listing_details_text = apply_filters('atbdp_single_listing_details_section_text', $listing_details_text);
                $display_tagline_field = get_directorist_option('display_tagline_field', 0);
                $display_pricing_field = get_directorist_option('display_pricing_field', 1);
                $display_thumbnail_img = get_directorist_option('dsiplay_thumbnail_img', 1);
                // make main column size 12 when sidebar or submit widget is active @todo; later make the listing submit widget as real widget instead of hard code
                $main_col_size = is_active_sidebar('right-sidebar-listing') ? 'col-lg-8' : 'col-lg-12';

                $template_file = 'single-listing/listing-header.php';
                $theme_template_file =  ATBDP_SHORTCODE_TEMPLATES_THEME_DIR . $template_file;
                $default_template_file = ATBDP_SHORTCODE_TEMPLATES_DEFAULT_DIR . $template_file;

                // Load theme template if exist
                $theme_template = atbdp_get_theme_file( $theme_template_file );
                if ( $theme_template ) {
                    include $theme_template;
                    return ob_get_clean();
                }

                // Load default template
                include $default_template_file;
                return ob_get_clean();
            }
            return ob_get_clean();
        }

        // listing custom fields area
        public function directorist_custom_field() {
            ob_start();
            if( is_singular(ATBDP_POST_TYPE ) ) {
                global $post;
                $listing_id = $post->ID;
                $fm_plan = get_post_meta($listing_id, '_fm_plans', true);
                $cats = get_the_terms($post->ID, ATBDP_CATEGORY);
                $custom_section_lable = get_directorist_option('custom_section_lable', __('Details', 'directorist'));
                // make main column size 12 when sidebar or submit widget is active @todo; later make the listing submit widget as real widget instead of hard code
                $main_col_size = is_active_sidebar('right-sidebar-listing') ? 'col-lg-8' : 'col-lg-12';
                $category_ids = array();
                if (!empty($cats)) {
                    foreach ($cats as $single_val) {
                        $category_ids[] = $single_val->term_id;
                    }
                }
                $c_args = array(
                    'post_type' => ATBDP_CUSTOM_FIELD_POST_TYPE,
                    'posts_per_page' => -1,
                    'post_status' => 'publish',

                );
                $custom_fields = new WP_Query($c_args);
                $custom_fields_posts = $custom_fields->posts;
                $has_field_value = array();
                $has_field_ids = array();
                foreach ($custom_fields_posts as $custom_fields_post) {
                    $id = $custom_fields_post->ID;
                    $fields = get_post_meta($id, 'associate', true);
                    //lets match if the field is associated with a category and the category is selected
                    if ('form' != $fields){
                        $fields_id_with_cat = get_post_meta($id, 'category_pass', true);
                        if (in_array($fields_id_with_cat, $category_ids)){
                            $has_field_details = get_post_meta($listing_id, $custom_fields_post->ID, true);
                            if (!empty($has_field_details)){
                                $has_field_ids[] = $id;
                            }
                            $has_field_value[] = $has_field_details;
                        }

                    }else{
                        $has_field_details = get_post_meta($listing_id, $custom_fields_post->ID, true);
                        if (!empty($has_field_details)){
                            $has_field_ids[] = $id;
                        }
                        $has_field_value[] = $has_field_details;
                    }

                }
                wp_reset_postdata();
                $has_field = join($has_field_value);
                $has_field = apply_filters('atbdp_single_listing_custom_field', $has_field);
                $plan_custom_field = true;
                if (is_fee_manager_active()) {
                    $plan_custom_field = is_plan_allowed_custom_fields($fm_plan);
                }

                $template_file = 'single-listing/custom-field.php';
                $theme_template_file =  ATBDP_SHORTCODE_TEMPLATES_THEME_DIR . $template_file;
                $default_template_file = ATBDP_SHORTCODE_TEMPLATES_DEFAULT_DIR . $template_file;

                // Load theme template if exist
                $theme_template = atbdp_get_theme_file( $theme_template_file );
                if ( $theme_template ) {
                    include $theme_template;
                    return ob_get_clean();
                } 

                // Load default template
                include $default_template_file;
            }
            return ob_get_clean();
        }

        //listing video area
        public function directorist_listing_video() {
            ob_start();
            if(is_singular(ATBDP_POST_TYPE)) {
                global $post;
                $listing_id               = $post->ID;
                $fm_plan                  = get_post_meta($listing_id, '_fm_plans', true);
                $listing_info['videourl'] = get_post_meta($post->ID, '_videourl', true);
                extract($listing_info);
                $enable_video_url         = get_directorist_option('atbd_video_url', 1);
                $video_label              = get_directorist_option('atbd_video_title', __('Video', 'directorist'));
                $main_col_size            = is_active_sidebar('right-sidebar-listing') ? 'col-lg-8' : 'col-lg-12';
                $display_video_for        = get_directorist_option('display_video_for', 'admin_users');

                $plan_video = true;
                if (is_fee_manager_active()) {
                    $plan_video = is_plan_allowed_listing_video($fm_plan);
                }

                $template_file = 'single-listing/listing-video.php';
                $theme_template_file =  ATBDP_SHORTCODE_TEMPLATES_THEME_DIR . $template_file;
                $default_template_file = ATBDP_SHORTCODE_TEMPLATES_DEFAULT_DIR . $template_file;

                // Load theme template if exist
                $theme_template = atbdp_get_theme_file( $theme_template_file );
                if ( $theme_template ) {
                    include $theme_template;
                    return ob_get_clean();
                } 

                // Load default template
                if ( file_exists( $default_template_file ) ) {
                    include $default_template_file;
                }
                
            }
            return ob_get_clean();
        }

        //listing map area
        public function directorist_listing_map() {
            ob_start();
            if(is_singular(ATBDP_POST_TYPE)) {

                global $post;
                $listing_id = $post->ID;
                $fm_plan = get_post_meta($listing_id, '_fm_plans', true);
                $listing_info['address'] = get_post_meta($post->ID, '_address', true);
                $listing_info['manual_lat'] = get_post_meta($post->ID, '_manual_lat', true);
                $listing_info['manual_lng'] = get_post_meta($post->ID, '_manual_lng', true);
                $listing_info['listing_prv_img'] = get_post_meta($post->ID, '_listing_prv_img', true);
                $listing_info['hide_map'] = get_post_meta($post->ID, '_hide_map', true);
                $select_listing_map = get_directorist_option('select_listing_map', 'google');
                $display_map_field  = get_directorist_option('display_map_field', 1);
                $display_map_field  = apply_filters('atbdp_show_single_listing_map', $display_map_field);
                $cats               = get_the_terms(get_the_ID(), ATBDP_CATEGORY);
                if(!empty($cats)){
                    $cat_icon                       = get_cat_icon($cats[0]->term_id);
                }
                $cat_icon = !empty($cat_icon) ? $cat_icon : 'fa-map-marker';
                $icon_type = substr($cat_icon, 0,2);
                $fa_or_la = ('la' == $icon_type) ? "la " : "fa ";
                $cat_icon = ('none' == $cat_icon) ? 'fa fa-map-marker' : $fa_or_la . $cat_icon ;

                extract($listing_info);
                /*INFO WINDOW CONTENT*/
                $t = get_the_title();
                $t = !empty($t) ? $t : __('No Title', 'directorist');
                $average = ATBDP()->review->get_average($listing_id);
                $reviews_count = ATBDP()->review->db->count(array('post_id' => $post->ID)); // get total review count for this post
                $reviews = (($reviews_count > 1) || ($reviews_count === 0)) ? __(' Reviews', 'directorist') : __(' Review', 'directorist');
                $review_info = '';
                $review_info = '';
                if (!empty($enable_review)) {
                    $review_info = "<div class='miwl-rating'><span class='atbd_meta atbd_listing_rating'>$average<i class='".atbdp_icon_type()."-star'></i></span>";
                    $review_info .= "<div class='atbd_rating_count'>";
                    $review_info .= "<p>" . $reviews_count . $reviews . "</p>";
                    $review_info .= "</div></div>";
                }
                $tg = !empty($tagline) ? esc_html($tagline) : '';
                $ad = !empty($address) ? esc_html($address) : '';
                $default_image = get_directorist_option('default_preview_image', ATBDP_PUBLIC_ASSETS . 'images/grid.jpg');
                $listing_prv_imgurl = !empty($listing_prv_img) ? atbdp_get_image_source($listing_prv_img, 'small') : '';
                $listing_prv_imgurl = atbdp_image_cropping($listing_prv_img, 150, 150, true, 100)['url'];
                $img_url = !empty($listing_prv_imgurl)?$listing_prv_imgurl:$default_image;
                $image = "<img src=". $img_url.">";
                $display_map_info               = apply_filters('atbdp_listing_map_info_window', get_directorist_option('display_map_info', 1));
                $display_image_map              = get_directorist_option('display_image_map', 1);
                $display_title_map              = get_directorist_option('display_title_map', 1);
                $display_address_map            = get_directorist_option('display_address_map', 1);
                $display_direction_map          = get_directorist_option('display_direction_map', 1);
                if(empty($display_image_map)) {
                    $image = '';
                }
                if(empty($display_title_map)) {
                    $t = '';
                }
                $info_content = "";
                if(!empty($display_image_map) || !empty($display_title_map)) {
                    $info_content .= "<div class='map-info-wrapper'><div class='map-info-img'>$image</div><div class='map-info-details'><div class='atbdp-listings-title-block'><h3>$t</h3></div>";
                }
                if(!empty($display_address_map) && !empty($ad)) {
                    $info_content .= apply_filters("atbdp_address_in_map_info_window", "<address>{$ad}</address>");
                }
                if(!empty($display_direction_map)) {
                    $info_content .= "<div class='map_get_dir'><a href='http://www.google.com/maps?daddr={$manual_lat},{$manual_lng}' target='_blank'> " . __('Get Direction', 'directorist') . "</a></div><span id='iw-close-btn'><i class='la la-times'></i></span></div></div>";
                }
                /*END INFO WINDOW CONTENT*/
                $map_zoom_level = get_directorist_option('map_zoom_level', 16);
                $disable_map = get_directorist_option('disable_map', 0);
                $listing_location_text = get_directorist_option('listing_location_text', __('Location', 'directorist'));
                $listing_location_text = apply_filters('atbdp_single_listing_map_section_text', $listing_location_text);
                $main_col_size = is_active_sidebar('right-sidebar-listing') ? 'col-lg-8' : 'col-lg-12';


                $template_file = 'single-listing/listing-map.php';
                $theme_template_file =  ATBDP_SHORTCODE_TEMPLATES_THEME_DIR . $template_file;
                $default_template_file = ATBDP_SHORTCODE_TEMPLATES_DEFAULT_DIR . $template_file;

                // Load theme template if exist
                $theme_template = atbdp_get_theme_file( $theme_template_file );
                if ( $theme_template ) {
                    include $theme_template;
                    return ob_get_clean();
                } 

                // Load default template
                include $default_template_file;
            }
            return ob_get_clean();
        }

        //listing contact information area
        public function directorist_listing_contact_information() {
            ob_start();
            if(is_singular(ATBDP_POST_TYPE)) {
                global $post;
                $listing_id = $post->ID;
                $fm_plan = get_post_meta($listing_id, '_fm_plans', true);
                /*store all data in an array so that we can pass it to filters for extension to get this value*/
                $listing_info['address'] = get_post_meta($post->ID, '_address', true);
                $listing_info['phone'] = get_post_meta($post->ID, '_phone', true);
                $listing_info['phone2'] = get_post_meta($post->ID, '_phone2', true);
                $listing_info['fax'] = get_post_meta($post->ID, '_fax', true);
                $listing_info['email'] = get_post_meta($post->ID, '_email', true);
                $listing_info['website'] = get_post_meta($post->ID, '_website', true);
                $listing_info['zip'] = get_post_meta($post->ID, '_zip', true);
                $listing_info['social'] = get_post_meta($post->ID, '_social', true);
                $listing_info['hide_contact_info'] = get_post_meta($post->ID, '_hide_contact_info', true);
                extract($listing_info);
                /*Prepare Listing Image links*/
                $listing_imgs = (!empty($listing_img) && !empty($display_slider_image)) ? $listing_img : array();


                /*END INFO WINDOW CONTENT*/
                $contact_info_text = get_directorist_option('contact_info_text', __('Contact Information', 'directorist'));
                $display_address_field = get_directorist_option('display_address_field', 1);
                $address_label = get_directorist_option('address_label', __('Address', 'directorist'));
                $display_phone_field = get_directorist_option('display_phone_field', 1);
                $phone_label = get_directorist_option('phone_label', __('Phone', 'directorist'));
                $display_phone2_field = get_directorist_option('display_phone_field2', 1);
                $phone_label2 = get_directorist_option('phone_label2', __('Phone Number 2', 'directorist'));
                $display_fax_field = get_directorist_option('display_fax', 1);
                $fax_label = get_directorist_option('fax_label', __('Fax', 'directorist'));
                $display_email_field = get_directorist_option('display_email_field', 1);
                $email_label = get_directorist_option('email_label', __('Email', 'directorist'));
                $display_website_field = get_directorist_option('display_website_field', 1);
                $website_label = get_directorist_option('website_label', __('Website', 'directorist'));
                $display_zip_field = get_directorist_option('display_zip_field', 1);
                $zip_label = get_directorist_option('zip_label', __('Zip/Post Code', 'directorist'));
                $display_social_info_field = get_directorist_option('display_social_info_field', 1);
                $display_social_info_for = get_directorist_option('display_social_info_for', 'admin_users');
                $is_info = get_directorist_option('disable_contact_info', 0);
                $use_nofollow = get_directorist_option('use_nofollow');
                $address_map_link = get_directorist_option('address_map_link', 0);
                $disable_contact_info = apply_filters('atbdp_single_listing_contact_info', $is_info);
                // make main column size 12 when sidebar or submit widget is active @todo; later make the listing submit widget as real widget instead of hard code
                $main_col_size = is_active_sidebar('right-sidebar-listing') ? 'col-lg-8' : 'col-lg-12';

                $template_file = 'single-listing/contact-information.php';
                $theme_template_file =  ATBDP_SHORTCODE_TEMPLATES_THEME_DIR . $template_file;
                $default_template_file = ATBDP_SHORTCODE_TEMPLATES_DEFAULT_DIR . $template_file;

                // Load theme template if exist
                $theme_template = atbdp_get_theme_file( $theme_template_file );
                if ( $theme_template ) {
                    include $theme_template;
                    return ob_get_clean();
                } 
                
                // Load default template
                include $default_template_file;
            }
            return ob_get_clean();
        }

        //listing author details
        public function directorist_listing_author_details() {
            ob_start();
            if(is_singular(ATBDP_POST_TYPE)) {
                $template_file = 'single-listing/author-details.php';
                $theme_template_file =  ATBDP_SHORTCODE_TEMPLATES_THEME_DIR . $template_file;
                $default_template_file = ATBDP_SHORTCODE_TEMPLATES_DEFAULT_DIR . $template_file;

                // Load theme template if exist
                $theme_template = atbdp_get_theme_file( $theme_template_file );
                if ( $theme_template ) {
                    include $theme_template;
                    return ob_get_clean();
                } 

                // Load default template
                include $default_template_file;
            }
            return ob_get_clean();
        }

        //listing contact owner area
        public function directorist_listing_contact_owner() {
            ob_start();
            if(is_singular(ATBDP_POST_TYPE)) {
                global $post;
                $listing_id = $post->ID;
                $fm_plan = get_post_meta($listing_id, '_fm_plans', true);
                $email = get_post_meta($post->ID, '_email', true);
                $plan_permission = true;
                $listing_info['hide_contact_owner'] = get_post_meta($post->ID, '_hide_contact_owner', true);
                $disable_contact_owner = get_directorist_option('disable_contact_owner', 1);
                $contact_listing_owner = get_directorist_option('contact_listing_owner', __('Contact Listing Owner', 'directorist'));
                $main_col_size          = is_active_sidebar('right-sidebar-listing') ? 'col-lg-8' : 'col-lg-12';
                extract($listing_info);
                if (is_fee_manager_active()) {
                    $plan_permission = is_plan_allowed_owner_contact_widget($fm_plan);
                }

                //do_action('atbdp_after_contact_listing_owner_section', $listing_id);
                $template_file = 'single-listing/contact-owner.php';
                $theme_template_file =  ATBDP_SHORTCODE_TEMPLATES_THEME_DIR . $template_file;
                $default_template_file = ATBDP_SHORTCODE_TEMPLATES_DEFAULT_DIR . $template_file;

                // Load theme template if exist
                $theme_template = atbdp_get_theme_file( $theme_template_file );
                if ( $theme_template ) {
                    include $theme_template;
                    return ob_get_clean();
                } 

                // Load default template
                include $default_template_file;
            }
            return ob_get_clean();
        }

        //listing review area
        public function directorist_listing_review() {
            ob_start();
            if (is_singular(ATBDP_POST_TYPE)) {
                $template_file = 'single-listing/custom-field.php';
                $theme_template_file =  ATBDP_SHORTCODE_TEMPLATES_THEME_DIR . $template_file;
                $default_template_file = ATBDP_SHORTCODE_TEMPLATES_DEFAULT_DIR . $template_file;

                // Load theme template if exist
                $theme_template = atbdp_get_theme_file( $theme_template_file );
                if ( $theme_template ) {
                    include $theme_template;
                    return ob_get_clean();
                } 

                // Load default template
                include $default_template_file;
            }
            return ob_get_clean();
        }

        //related listing area
        public function directorist_related_listings() {
            ob_start();
            if(is_singular(ATBDP_POST_TYPE)) {
                global $post;
                $listing_id    = $post->ID;
                $fm_plan       = get_post_meta($listing_id, '_fm_plans', true);
                $listing_info['never_expire'] = get_post_meta($post->ID, '_never_expire', true);
                $listing_info['featured'] = get_post_meta($post->ID, '_featured', true);
                $listing_info['price'] = get_post_meta($post->ID, '_price', true);
                $listing_info['price_range'] = get_post_meta($post->ID, '_price_range', true);
                $listing_info['atbd_listing_pricing'] = get_post_meta($post->ID, '_atbd_listing_pricing', true);
                $listing_info['videourl'] = get_post_meta($post->ID, '_videourl', true);
                $listing_info['listing_status'] = get_post_meta($post->ID, '_listing_status', true);
                $listing_info['tagline'] = get_post_meta($post->ID, '_tagline', true);
                $listing_info['excerpt'] = get_post_meta($post->ID, '_excerpt', true);
                $listing_info['address'] = get_post_meta($post->ID, '_address', true);
                $listing_info['phone'] = get_post_meta($post->ID, '_phone', true);
                $listing_info['email'] = get_post_meta($post->ID, '_email', true);
                $listing_info['website'] = get_post_meta($post->ID, '_website', true);
                $listing_info['zip'] = get_post_meta($post->ID, '_zip', true);
                $listing_info['social'] = get_post_meta($post->ID, '_social', true);
                $listing_info['faqs'] = get_post_meta($post->ID, '_faqs', true);
                $listing_info['manual_lat'] = get_post_meta($post->ID, '_manual_lat', true);
                $listing_info['manual_lng'] = get_post_meta($post->ID, '_manual_lng', true);
                $listing_info['hide_map'] = get_post_meta($post->ID, '_hide_map', true);
                $listing_info['listing_img'] = get_post_meta($post->ID, '_listing_img', true);
                $listing_info['listing_prv_img'] = get_post_meta($post->ID, '_listing_prv_img', true);
                $listing_info['hide_contact_info'] = get_post_meta($post->ID, '_hide_contact_info', true);
                $listing_info['hide_contact_owner'] = get_post_meta($post->ID, '_hide_contact_owner', true);
                $listing_info['expiry_date'] = get_post_meta($post->ID, '_expiry_date', true);
                extract($listing_info);
                $main_col_size = is_active_sidebar('right-sidebar-listing') ? 'col-lg-8' : 'col-lg-12';

                $enable_rel_listing = get_directorist_option('enable_rel_listing', 1);
                if (empty($enable_rel_listing)) return; // vail if related listing is not enabled
                $related_listings = $this->get_related_listings($post);
                $is_disable_price = get_directorist_option('disable_list_price');
                $rel_listing_column = get_directorist_option('rel_listing_column', 3);

                $template_file = 'single-listing/related_listings.php';
                $theme_template_file =  ATBDP_SHORTCODE_TEMPLATES_THEME_DIR . $template_file;
                $default_template_file = ATBDP_SHORTCODE_TEMPLATES_DEFAULT_DIR . $template_file;

                // Load theme template if exist
                $theme_template = atbdp_get_theme_file( $theme_template_file );
                if ( $theme_template ) {
                    include $theme_template;
                    return ob_get_clean();
                } 

                // Load default template
                include $default_template_file;
            }
            return ob_get_clean();
        }

        /**
         * It gets the related listings of the given listing/post
         * @param object|WP_Post $post The WP Post Object of whose related listing we would like to show
         * @return object|WP_Query It returns the related listings if found.
         */
        public function get_related_listings($post)
        {
            $rel_listing_num = get_directorist_option('rel_listing_num', 2);
            $atbd_cats = get_the_terms($post, ATBDP_CATEGORY);
            $atbd_tags = get_the_terms($post, ATBDP_TAGS);
            // get the tag ids of the listing post type
            $atbd_cats_ids = array();
            $atbd_tags_ids = array();

            if (!empty($atbd_cats)) {
                foreach ($atbd_cats as $atbd_cat) {
                    $atbd_cats_ids[] = $atbd_cat->term_id;
                }
            }
            if (!empty($atbd_tags)) {
                foreach ($atbd_tags as $atbd_tag) {
                    $atbd_tags_ids[] = $atbd_tag->term_id;
                }
            }
            $relationship = get_directorist_option('rel_listings_logic','OR');
            $args = array(
                'post_type' => ATBDP_POST_TYPE,
                'tax_query' => array(
                    'relation' => $relationship,
                    array(
                        'taxonomy' => ATBDP_CATEGORY,
                        'field' => 'term_id',
                        'terms' => $atbd_cats_ids,
                    ),
                    array(
                        'taxonomy' => ATBDP_TAGS,
                        'field' => 'term_id',
                        'terms' => $atbd_tags_ids,
                    ),
                ),
                'posts_per_page' => (int)$rel_listing_num,
                'post__not_in' => array($post->ID),
            );

            $meta_queries = array();
            $meta_queries[] = array(
                'relation' => 'OR',
                array(
                    'key' => '_expiry_date',
                    'value' => current_time('mysql'),
                    'compare' => '>', // eg. expire date 6 <= current date 7 will return the post
                    'type' => 'DATETIME'
                ),
                array(
                    'key' => '_never_expire',
                    'value' => 1,
                )
            );

            $meta_queries = apply_filters('atbdp_related_listings_meta_queries', $meta_queries);
            $count_meta_queries = count($meta_queries);
            if ($count_meta_queries) {
                $args['meta_query'] = ($count_meta_queries > 1) ? array_merge(array('relation' => 'AND'), $meta_queries) : $meta_queries;
            }

            return new WP_Query(apply_filters('atbdp_related_listing_args', $args));

        }

        /**
         * Display custom fields.Columns
         *
         * @param int $post_id Post ID.
         * @param array $term_id Category ID.
         * @since     3.2
         * @access   public
         */
        public function ajax_callback_custom_fields($post_id = 0, $term_id = array())
        {

            $ajax = false;
            if (isset($_POST['term_id'])) {
                $ajax = true;
                $post_ID = !empty($_POST['post_id']) ? (int)$_POST['post_id'] : '';
                $term_id = $_POST['term_id'];
            }
            // Get custom fields
            $categories = !empty($term_id) ? $term_id : array();
            $args = array(
                'post_type' => ATBDP_CUSTOM_FIELD_POST_TYPE,
                'posts_per_page' => -1,
                'status' => 'published'
            );
            $meta_queries = array();
            if(!empty($categories)){
                if ($categories > 1) {
                    $sub_meta_queries = array();
                    foreach ($categories as $value) {
                        $sub_meta_queries[] = array(
                            'key' => 'category_pass',
                            'value' => $value,
                            'compare' => '='
                        );
                    }

                    $meta_queries[] = array_merge(array('relation' => 'OR'), $sub_meta_queries);
                } else {
                    $meta_queries[] = array(
                        'key' => 'category_pass',
                        'value' => $categories[0],
                        'compare' => '='
                    );
                }
            }
            $meta_queries[] = array(
                array(
                    'relation' => 'OR',
                    array(
                        'key' => 'admin_use',
                        'compare' => 'NOT EXISTS'
                    ),
                    array(
                        'key' => 'admin_use',
                        'value' => 1,
                        'compare' => '!='
                    ),
                )
            );
            $meta_queries[] = array(
                array(
                    'key' => 'associate',
                    'value' => 'categories',
                    'compare' => 'LIKE',
                ),
            );


            $count_meta_queries = count($meta_queries);
            if ($count_meta_queries) {
                $args['meta_query'] = ($count_meta_queries > 1) ? array_merge(array('relation' => 'AND'), $meta_queries) : $meta_queries;
            }

            $atbdp_query = new WP_Query($args);

            if ($atbdp_query->have_posts()) {
                // Start the Loop
                global $post;
                // Process output
                ob_start();
                $include = apply_filters('include_style_settings', true);
                if ($include) {
                    include ATBDP_DIR . 'public/assets/css/style.php';
                }
                include ATBDP_TEMPLATES_DIR . 'add-listing-custom-field.php';
                wp_reset_postdata(); // Restore global post data stomped by the_post()
                $output = ob_get_clean();

                print $output;

                if ($ajax) {
                    wp_die();
                }
            } else {
                echo '<div class="custom_field_empty_area"></div>';
                if ($ajax) {
                    wp_die();
                }
            }

        }

        public function search_result($atts)
        {
            wp_enqueue_script('adminmainassets');
            wp_enqueue_script('atbdp-search-listing', ATBDP_PUBLIC_ASSETS . 'js/search-form-listing.js');
            wp_localize_script('atbdp-search-listing', 'atbdp_search', array(
                'ajaxnonce' => wp_create_nonce('bdas_ajax_nonce'),
                'ajax_url' => admin_url('admin-ajax.php'),
                'added_favourite' => __('Added to favorite', 'directorist'),
                'please_login' => __('Please login first', 'directorist')
            ));
            wp_enqueue_script('atbdp-range-slider');
            $radius_search_unit            = get_directorist_option('radius_search_unit', 'miles');
            if(!empty($radius_search_unit) && 'kilometers' == $radius_search_unit) {
                $miles = __(' Kilometers', 'directorist');
            }else{
                $miles = __(' Miles', 'directorist');
            }
            $default_radius_distance = get_directorist_option('sresult_default_radius_distance', 0);

            $listing_orderby = get_directorist_option('search_order_listing_by');
            $search_sort_listing_by = get_directorist_option('search_sort_listing_by');
            $listing_view = get_directorist_option('search_view_as');
            $listing_order = get_directorist_option('search_sort_by');
            $listing_grid_columns = get_directorist_option('search_listing_columns', 3);
            $display_listings_header = get_directorist_option('search_header', 1);
            $filters_display = get_directorist_option('search_result_display_filter', 'sliding');
            $paginate = get_directorist_option('paginate_search_results');
            $listings_map_height = get_directorist_option('listings_map_height', 350);
            $params = apply_filters('atbdp_search_results_param', array(
                'view' => !empty($listing_view) ? $listing_view : 'grid',
                '_featured' => 1,
                'filterby' => '',
                'orderby' => !empty($listing_orderby) ? $listing_orderby : 'date',
                'order' => !empty($search_sort_listing_by) ? $search_sort_listing_by : 'asc',
                'listings_per_page' => (int)get_directorist_option('search_posts_num', 6),
                'show_pagination' => !empty($paginate) ? 'yes' : '',
                'header' => !empty($display_listings_header) ? 'yes' : '',
                'columns' => !empty($listing_grid_columns) ? $listing_grid_columns : 3,
                'featured_only' => '',
                'popular_only' => '',
                'logged_in_user_only' => '',
                'redirect_page_url' => '',
                'map_height' => !empty($listings_map_height) ? $listings_map_height : 350,
            ));
            $atts = shortcode_atts($params, $atts);
            $columns = !empty($atts['columns']) ? $atts['columns'] : 3;
            $display_header = !empty($atts['header']) ? $atts['header'] : '';
            $show_pagination = !empty($atts['show_pagination']) ? $atts['show_pagination'] : '';
            $feature_only = !empty($atts['featured_only']) ? $atts['featured_only'] : '';
            $popular_only = !empty($atts['popular_only']) ? $atts['popular_only'] : '';
            $logged_in_user_only = !empty($atts['logged_in_user_only']) ? $atts['logged_in_user_only'] : '';
            $redirect_page_url = !empty($atts['redirect_page_url']) ? $atts['redirect_page_url'] : '';
            $map_height = !empty($atts['map_height']) ? $atts['map_height'] : '';
            //for pagination
            $paged = atbdp_get_paged_num();

            if ( 'yes' == $logged_in_user_only && ! atbdp_logged_in_user() ) {
                ob_start();
                // user not logged in;
                $error_message = sprintf(__('You need to be logged in to view the content of this page. You can login %s. Don\'t have an account? %s', 'directorist'), apply_filters('atbdp_listing_form_login_link', "<a href='" . ATBDP_Permalink::get_login_page_link() . "'> " . __('Here', 'directorist') . "</a>"), apply_filters('atbdp_listing_form_signup_link', "<a href='" . ATBDP_Permalink::get_registration_page_link() . "'> " . __('Sign Up', 'directorist') . "</a>")); 
                ?>
                <section class="directory_wrapper single_area">
                    <?php ATBDP()->helper->show_login_message($error_message); ?>
                </section>
                <?php

                return ob_get_clean();
            }

            $has_featured = get_directorist_option('enable_featured_listing');
            if ($has_featured || is_fee_manager_active()) {
                $has_featured = $atts['_featured'];
            }
            if ('rand' == $atts['orderby']) {
                $current_order = atbdp_get_listings_current_order($atts['orderby']);
            } else {
                $current_order = atbdp_get_listings_current_order($atts['orderby'] . '-' . $atts['order']);
            }
            $view = atbdp_get_listings_current_view_name($atts['view']);
            $s_string = !empty($_GET['q']) ? sanitize_text_field($_GET['q']) : '';// get the searched query
            $args = array(
                'post_type' => ATBDP_POST_TYPE,
                'post_status' => 'publish',
            );

            if (!empty($s_string)) {
                $args['s'] = $s_string;
            }

            if ('yes' == $show_pagination) {
                $args['posts_per_page'] = (int)$atts['listings_per_page'];
                $args['paged'] = $paged;
            }

            if ($has_featured) {
                $args['meta_key'] = '_featured';
                $args['orderby'] = array(
                    'meta_value_num' => 'DESC',
                    'title' => 'ASC',
                );
            } else {
                $args['orderby'] = 'title';
                $args['order'] = 'ASC';
            };

            // Define tax queries( only if applicable )
            $tax_queries = array();
            if (isset($_GET['in_cat']) && (int)$_GET['in_cat'] > 0) {
                $tax_queries[] = array(
                    'taxonomy' => ATBDP_CATEGORY,
                    'field' => 'term_id',
                    'terms' => (int)$_GET['in_cat'],
                    'include_children' => true,
                );
            }

            if (isset($_GET['in_loc']) && (int)$_GET['in_loc'] > 0) {
                $tax_queries[] = array(
                    'taxonomy' => ATBDP_LOCATION,
                    'field' => 'term_id',
                    'terms' => (int)$_GET['in_loc'],
                    'include_children' => true,
                );
            }

            if (isset($_GET['in_tag']) && (int)$_GET['in_tag'] > 0) {
                $tag_value = $_GET['in_tag'];
                $tax_queries[] = array(
                    'taxonomy' => ATBDP_TAGS,
                    'field' => 'term_id',
                    'terms' => $tag_value,
                );

            }
            $count_tax_queries = count($tax_queries);
            if ($count_tax_queries) {
                $args['tax_query'] = ($count_tax_queries > 1) ? array_merge(array('relation' => 'AND'), $tax_queries) : $tax_queries;
            }

            $meta_queries = array();

            if (isset($_GET['custom_field'])) {
                $cf = array_filter($_GET['custom_field']);

                foreach ($cf as $key => $values) {
                    if (is_array($values)) {

                        if (count($values) > 1) {

                            $sub_meta_queries = array();

                            foreach ($values as $value) {
                                $sub_meta_queries[] = array(
                                    'key' => $key,
                                    'value' => sanitize_text_field($value),
                                    'compare' => 'LIKE'
                                );
                            }

                            $meta_queries[] = array_merge(array('relation' => 'OR'), $sub_meta_queries);

                        } else {

                            $meta_queries[] = array(
                                'key' => $key,
                                'value' => sanitize_text_field($values[0]),
                                'compare' => 'LIKE'
                            );
                        }

                    } else {

                        $field_type = get_post_meta($key, 'type', true);
                        $operator = ('text' == $field_type || 'textarea' == $field_type || 'url' == $field_type) ? 'LIKE' : '=';
                        $meta_queries[] = array(
                            'key' => $key,
                            'value' => sanitize_text_field($values),
                            'compare' => $operator
                        );

                    }

                }

            } // end get['cf']

            if (isset($_GET['price'])) {
                $price = array_filter($_GET['price']);

                if ($n = count($price)) {

                    if (2 == $n) {
                        $meta_queries[] = array(
                            'key' => '_price',
                            'value' => array_map('intval', $price),
                            'type' => 'NUMERIC',
                            'compare' => 'BETWEEN'
                        );
                    } else {
                        if (empty($price[0])) {
                            $meta_queries[] = array(
                                'key' => '_price',
                                'value' => (int)$price[1],
                                'type' => 'NUMERIC',
                                'compare' => '<='
                            );
                        } else {
                            $meta_queries[] = array(
                                'key' => '_price',
                                'value' => (int)$price[0],
                                'type' => 'NUMERIC',
                                'compare' => '>='
                            );
                        }
                    }

                }

            }// end price
            if (isset($_GET['price_range']) && 'none' != $_GET['price_range']) {
                $price_range = $_GET['price_range'];
                $meta_queries[] = array(
                    'key' => '_price_range',
                    'value' => $price_range,
                    'compare' => 'LIKE'
                );
            }

            // search by rating
            if (isset($_GET['search_by_rating'])) {
                $q_rating = $_GET['search_by_rating'];
                $listings = get_atbdp_listings_ids();
                $rated = array();
                if ($listings->have_posts()) {
                    while ($listings->have_posts()) {
                        $listings->the_post();
                        $listing_id = get_the_ID();
                        $average = ATBDP()->review->get_average($listing_id);
                        if ($q_rating === '5') {
                            if (($average == '5')) {
                                $rated[] = get_the_ID();
                            } else {
                                $rated[] = array();
                            }
                        } elseif ($q_rating === '4') {
                            if ($average >= '4') {
                                $rated[] = get_the_ID();
                            } else {
                                $rated[] = array();
                            }
                        } elseif ($q_rating === '3') {
                            if ($average >= '3') {
                                $rated[] = get_the_ID();
                            } else {
                                $rated[] = array();
                            }
                        } elseif ($q_rating === '2') {
                            if ($average >= '2') {
                                $rated[] = get_the_ID();
                            } else {
                                $rated[] = array();
                            }
                        } elseif ($q_rating === '1') {
                            if ($average >= '1') {
                                $rated[] = get_the_ID();
                            } else {
                                $rated[] = array();
                            }
                        } elseif ('' === $q_rating) {
                            if ($average === '') {
                                $rated[] = get_the_ID();
                            }
                        }
                    }
                    $rating_id = array(
                        'post__in' => !empty($rated) ? $rated : array()
                    );
                    $args = array_merge($args, $rating_id);
                }


            }

            if (isset($_GET['website'])) {
                $website = $_GET['website'];
                $meta_queries[] = array(
                    'key' => '_website',
                    'value' => $website,
                    'compare' => 'LIKE'
                );
            }

            if (isset($_GET['email'])) {
                $email = $_GET['email'];
                $meta_queries[] = array(
                    'key' => '_email',
                    'value' => $email,
                    'compare' => 'LIKE'
                );
            }


            if (isset($_GET['phone'])) {
                $phone = $_GET['phone'];
                $meta_queries[] = array(
                    'relation' => 'OR',
                    array(
                        'key' => '_phone2',
                        'value' => $phone,
                        'compare' => 'LIKE'
                    ),
                    array(
                        'key' => '_phone',
                        'value' => $phone,
                        'compare' => 'LIKE'
                    )
                );
            }

            if (!empty($_GET['fax'])) {
                $fax = $_GET['fax'];
                $meta_queries[] = array(
                    'key' => '_fax',
                    'value' => $fax,
                    'compare' => 'LIKE'
                );
            }
            if (!empty($_GET['miles']) && $_GET['miles'] > 0 && !empty($_GET['cityLat']) && !empty($_GET['cityLng'])) {
                $radius_search_unit = get_directorist_option('radius_search_unit', 'miles');
                $args['atbdp_geo_query'] = array(
                    'lat_field' => '_manual_lat',  // this is the name of the meta field storing latitude
                    'lng_field' => '_manual_lng', // this is the name of the meta field storing longitude
                    'latitude' => $_GET['cityLat'],    // this is the latitude of the point we are getting distance from
                    'longitude' => $_GET['cityLng'],   // this is the longitude of the point we are getting distance from
                    'distance' => $_GET['miles'],           // this is the maximum distance to search
                    'units' => $radius_search_unit       // this supports options: miles, mi, kilometers, km
                );
            } elseif (isset($_GET['address'])) {
                $address = $_GET['address'];
                $meta_queries[] = array(
                    'key' => '_address',
                    'value' => $address,
                    'compare' => 'LIKE'
                );
            }

            if (!empty($_GET['zip_code'])) {
                $zip_code = $_GET['zip_code'];
                $meta_queries[] = array(
                    'key' => '_zip',
                    'value' => $zip_code,
                    'compare' => 'LIKE'
                );
            }

            $meta_queries['expired'] = array(
                'relation' => 'OR',
                array(
                    'key' => '_expiry_date',
                    'value' => current_time('mysql'),
                    'compare' => '>', // eg. expire date 6 <= current date 7 will return the post
                    'type' => 'DATETIME'
                ),
                array(
                    'key' => '_never_expire',
                    'value' => 1,
                )

            );
            $args['expired'] = $meta_queries;
            if ($has_featured) {

                if ('_featured' == $atts['filterby']) {
                    $meta_queries['_featured'] = array(
                        'key' => '_featured',
                        'value' => 1,
                        'compare' => '='
                    );

                } else {
                    $meta_queries['_featured'] = array(
                        'key' => '_featured',
                        'type' => 'NUMERIC',
                        'compare' => 'EXISTS',
                    );
                }

            }

            switch ($current_order) {
                case 'title-asc' :
                if ($has_featured) {
                    $args['meta_key'] = '_featured';
                    $args['orderby'] = array(
                        'meta_value_num' => 'DESC',
                        'title' => 'ASC',
                    );
                } else {
                    $args['orderby'] = 'title';
                    $args['order'] = 'ASC';
                };
                break;
                case 'title-desc' :
                if ($has_featured) {
                    $args['meta_key'] = '_featured';
                    $args['orderby'] = array(
                        'meta_value_num' => 'DESC',
                        'title' => 'DESC',
                    );
                } else {
                    $args['orderby'] = 'title';
                    $args['order'] = 'DESC';
                };
                break;
                case 'date-asc' :
                if ($has_featured) {
                    $args['meta_key'] = '_featured';
                    $args['orderby'] = array(
                        'meta_value_num' => 'DESC',
                        'date' => 'ASC',
                    );
                } else {
                    $args['orderby'] = 'date';
                    $args['order'] = 'ASC';
                };
                break;
                case 'date-desc' :
                if ($has_featured) {
                    $args['meta_key'] = '_featured';
                    $args['orderby'] = array(
                        'meta_value_num' => 'DESC',
                        'date' => 'DESC',
                    );
                } else {
                    $args['orderby'] = 'date';
                    $args['order'] = 'DESC';
                };
                break;
                case 'price-asc' :
                if ($has_featured) {
                    $meta_queries['price'] = array(
                        'key' => '_price',
                        'type' => 'NUMERIC',
                        'compare' => 'EXISTS',
                    );

                    $args['orderby'] = array(
                        '_featured' => 'DESC',
                        'price' => 'ASC',
                    );
                } else {
                    $args['meta_key'] = '_price';
                    $args['orderby'] = 'meta_value_num';
                    $args['order'] = 'ASC';
                };
                break;
                case 'price-desc' :
                if ($has_featured) {
                    $meta_queries['price'] = array(
                        'key' => '_price',
                        'type' => 'NUMERIC',
                        'compare' => 'EXISTS',
                    );

                    $args['orderby'] = array(
                        '_featured' => 'DESC',
                        'price' => 'DESC',
                    );
                } else {
                    $args['meta_key'] = '_price';
                    $args['orderby'] = 'meta_value_num';
                    $args['order'] = 'DESC';
                };
                break;
                case 'views-desc' :
                $listings = get_atbdp_listings_ids();
                $rated = array();
                $listing_popular_by = get_directorist_option('listing_popular_by');
                $average_review_for_popular = get_directorist_option('average_review_for_popular', 4);
                $view_to_popular = get_directorist_option('views_for_popular');
                if ($has_featured) {
                    if ('average_rating' === $listing_popular_by) {
                        if ($listings->have_posts()) {
                            while ($listings->have_posts()) {
                                $listings->the_post();
                                $listing_id = get_the_ID();
                                $average = ATBDP()->review->get_average($listing_id);
                                if ($average_review_for_popular <= $average) {
                                    $rated[] = get_the_ID();
                                }

                            }
                            $rating_id = array(
                                'post__in' => !empty($rated) ? $rated : array()
                            );
                            $args = array_merge($args, $rating_id);
                        }
                    } elseif ('view_count' === $listing_popular_by) {
                        $meta_queries['views'] = array(
                            'key' => '_atbdp_post_views_count',
                            'value' => $view_to_popular,
                            'type' => 'NUMERIC',
                            'compare' => '>=',
                        );
                        $args['orderby'] = array(
                            '_featured' => 'DESC',
                            'views' => 'DESC',
                        );
                    } else {
                        $meta_queries['views'] = array(
                            'key' => '_atbdp_post_views_count',
                            'value' => $view_to_popular,
                            'type' => 'NUMERIC',
                            'compare' => '>=',
                        );
                        $args['orderby'] = array(
                            '_featured' => 'DESC',
                            'views' => 'DESC',
                        );
                        if ($listings->have_posts()) {
                            while ($listings->have_posts()) {
                                $listings->the_post();
                                $listing_id = get_the_ID();
                                $average = ATBDP()->review->get_average($listing_id);
                                if ($average_review_for_popular <= $average) {
                                    $rated[] = get_the_ID();
                                }

                            }
                            $rating_id = array(
                                'post__in' => !empty($rated) ? $rated : array()
                            );
                            $args = array_merge($args, $rating_id);
                        }
                    }
                } else {
                    if ('average_rating' === $listing_popular_by) {
                        if ($listings->have_posts()) {
                            while ($listings->have_posts()) {
                                $listings->the_post();
                                $listing_id = get_the_ID();
                                $average = ATBDP()->review->get_average($listing_id);
                                if ($average_review_for_popular <= $average) {
                                    $rated[] = get_the_ID();
                                }

                            }
                            $rating_id = array(
                                'post__in' => !empty($rated) ? $rated : array()
                            );
                            $args = array_merge($args, $rating_id);
                        }
                    } elseif ('view_count' === $listing_popular_by) {
                        $meta_queries['views'] = array(
                            'key' => '_atbdp_post_views_count',
                            'value' => $view_to_popular,
                            'type' => 'NUMERIC',
                            'compare' => '>=',
                        );
                        $args['orderby'] = array(
                            'views' => 'DESC',
                        );
                    } else {
                        $meta_queries['views'] = array(
                            'key' => '_atbdp_post_views_count',
                            'value' => (int)$view_to_popular,
                            'type' => 'NUMERIC',
                            'compare' => '>=',
                        );
                        $args['orderby'] = array(
                            'views' => 'DESC',
                        );
                        if ($listings->have_posts()) {
                            while ($listings->have_posts()) {
                                $listings->the_post();
                                $listing_id = get_the_ID();
                                $average = ATBDP()->review->get_average($listing_id);
                                if ($average_review_for_popular <= $average) {
                                    $rated[] = get_the_ID();
                                }

                            }
                            $rating_id = array(
                                'post__in' => !empty($rated) ? $rated : array()
                            );
                            $args = array_merge($args, $rating_id);
                        }
                    }
                }
                break;
                case 'rand' :
                if ($has_featured) {
                    $args['meta_key'] = '_featured';
                    $args['orderby'] = 'meta_value_num rand';
                } else {
                    $args['orderby'] = 'rand';
                };
                break;
            }

            $meta_queries = apply_filters('atbdp_search_listings_meta_queries', $meta_queries);

            $count_meta_queries = count($meta_queries);
            if ($count_meta_queries) {
                $args['meta_query'] = ($count_meta_queries > 1) ? array_merge(array('relation' => 'AND'), $meta_queries) : $meta_queries;
            }

            $all_listings = new WP_Query(apply_filters('atbdp_listing_search_query_argument', $args));
            $default_radius_distance = !empty($_GET['miles']) ? $_GET['miles'] : $default_radius_distance;
            wp_localize_script( 'atbdp-range-slider', 'atbdp_range_slider', array(
                'Miles'     =>  $miles,
                'default_val'   =>  $default_radius_distance
            ) );
            $cat_id = !empty($_GET['in_cat']) ? $_GET['in_cat'] : '';
            $loc_id = !empty($_GET['in_loc']) ? $_GET['in_loc'] : '';
            $cat_name = get_term_by('id', $cat_id, ATBDP_CATEGORY);
            $loc_name = get_term_by('id', $loc_id, ATBDP_LOCATION);
            $for_cat = !empty($cat_name) ? sprintf(__('for %s', 'directorist'), $cat_name->name) : '';
            if (isset($_GET['in_loc']) && (int)$_GET['in_loc'] > 0) {
                $in_loc = !empty($loc_name) ? sprintf(__('in %s', 'directorist'), $loc_name->name) : '';
            } else {
                $in_loc = !empty($_GET['address']) ? sprintf(__('in %s', 'directorist'), $_GET['address']) : '';
            }
            $_s = (1 < count($all_listings->posts)) ? 's' : '';

            $header_title = sprintf(__('%d result%s %s %s', 'directorist'), $all_listings->found_posts, $_s, $for_cat, $in_loc);
            $listing_filters_button = get_directorist_option('search_result_filters_button_display', 1);
            $filters = get_directorist_option('search_result_filter_button_text', __('Filters', 'directorist'));
            $text_placeholder = get_directorist_option('search_result_search_text_placeholder', __('What are you looking for?', 'directorist'));
            $category_placeholder = get_directorist_option('search_result_category_placeholder', __('Select a category', 'directorist'));
            $location_placeholder = get_directorist_option('search_result_location_placeholder', __('Select a location', 'directorist'));
            $sort_by_text = get_directorist_option('search_sortby_text', __('Sort By', 'directorist'));
            $view_as_text = get_directorist_option('search_viewas_text', __('View As', 'directorist'));
            $all_listing_title = !empty($all_listing_title) ? $all_listing_title : '';
            $data_for_template = compact('all_listings', 'all_listing_title', 'paged', 'paginate');
            $search_more_filters_fields = get_directorist_option('search_result_filters_fields', array('search_text', 'search_category', 'search_location', 'search_price', 'search_price_range', 'search_rating', 'search_tag', 'search_custom_fields', 'radius_search'));
            $filters_button = get_directorist_option('search_result_filters_button', array('reset_button', 'apply_button'));
            $reset_filters_text = get_directorist_option('sresult_reset_text', __('Reset Filters', 'directorist'));
            $apply_filters_text = get_directorist_option('sresult_apply_text', __('Apply Filters', 'directorist'));
            $data_for_template = compact('all_listings', 'all_listing_title', 'paged', 'paginate');
            $view_as_items = get_directorist_option('search_view_as_items', array('listings_grid', 'listings_list', 'listings_map'));
            $sort_by_items = get_directorist_option('search_sort_by_items', array('a_z', 'z_a', 'latest', 'oldest', 'popular', 'price_low_high', 'price_high_low', 'random'));
            $listing_header_container_fluid = is_directoria_active() ? 'container' : 'container-fluid';
            $header_container_fluid = apply_filters('atbdp_search_result_header_container_fluid', $listing_header_container_fluid);
            $listing_grid_container_fluid = is_directoria_active() ? 'container' : 'container-fluid';
            $grid_container_fluid = apply_filters('atbdp_search_result_grid_container_fluid', $listing_grid_container_fluid);
            $listing_location_address = get_directorist_option('sresult_location_address', 'map_api');
            ob_start();
            $include = apply_filters('include_style_settings', true);
            if ($include) {
                include ATBDP_DIR . 'public/assets/css/style.php';
            }
            if (!empty($redirect_page_url)) {
                $redirect = '<script>window.location="' . esc_url($redirect_page_url) . '"</script>';
                return $redirect;
            }
            $listing_type = isset($_GET['listing_type']) ? sanitize_text_field($_GET['listing_type']) : '';
            

            // Base Template Data
            !empty($args['data']) ? extract($args['data']) : array(); // data array contains all required var.
            $all_listings = !empty($all_listings) ? $all_listings : new WP_Query;
            $display_sortby_dropdown = get_directorist_option('display_sort_by', 1);
            $display_viewas_dropdown = get_directorist_option('display_view_as', 1);
            $display_image = !empty($display_image) ? $display_image : '';
            $show_pagination = !empty($show_pagination) ? $show_pagination : '';
            $paged = !empty($paged) ? $paged : '';

            $is_disable_price = get_directorist_option('disable_list_price');
            $view_as = get_directorist_option('grid_view_as', 'normal_grid');
            $column_width = 100 / (int)$columns . '%';

            if (is_rtl()) {
                wp_enqueue_style('atbdp-search-style-rtl', ATBDP_PUBLIC_ASSETS . 'css/search-style-rtl.css');
            } else {
                wp_enqueue_style('atbdp-search-style', ATBDP_PUBLIC_ASSETS . 'css/search-style.css');
            }

            // Header Template Data
            $address_label               = get_directorist_option('address_label',__('Address','directorist'));
            $fax_label                   = get_directorist_option('fax_label',__('Fax','directorist'));
            $email_label                 = get_directorist_option('email_label',__('Email','directorist'));
            $website_label               = get_directorist_option('website_label',__('Website','directorist'));
            $tag_label                   = get_directorist_option('tag_label',__('Tag','directorist'));
            $zip_label                   = get_directorist_option('zip_label',__('Zip','directorist'));
            $listing_filters_icon        = get_directorist_option('listing_filters_icon',1);
            $query_args = array(
                'parent'             => 0,
                'term_id'            => 0,
                'hide_empty'         => 0,
                'orderby'            => 'name',
                'order'              => 'asc',
                'show_count'         => 0,
                'single_only'        => 0,
                'pad_counts'         => true,
                'immediate_category' => 0,
                'active_term_id'     => 0,
                'ancestors'          => array()
            );
            $categories_fields = search_category_location_filter( $query_args, ATBDP_CATEGORY );
            $locations_fields  = search_category_location_filter( $query_args, ATBDP_LOCATION );
            $currency = get_directorist_option('g_currency', 'USD');
            $c_symbol = atbdp_currency_symbol($currency);


            // If Post_Your_Need is active
            if ( class_exists('Post_Your_Need') && ($listing_type === 'need') ) {
                $path = atbdp_get_theme_file("/directorist/shortcodes/listings/extension/post-your-need/need-card.php");
                
                if ( $path ) {
                    include $path;
                } else {
                    include PYN_TEMPLATES_DIR . "/need-card.php";
                }

                return ob_get_clean();
            }

            // If listings_with_map is active
            if ( 'listings_with_map' == $view ) {
                $path = atbdp_get_theme_file("/directorist/shortcodes/listings/extension/listing-with-map/map-view.php");
                
                if ( $path ) {
                    include $path;
                } else {
                    include BDM_TEMPLATES_DIR . '/map-view.php';
                }

                return ob_get_clean();
            }

            // Default Template
            $path = atbdp_get_theme_file("/directorist/shortcodes/listings-archive/listings-{$view}.php");
            if ( $path ) {
                include $path;
            } else {
                include ATBDP_TEMPLATES_DIR . "public-templates/shortcodes/listings-archive/listings-$view.php";
            }
            
            return ob_get_clean();
        }

        public function all_listing( $atts )
        {
            $listings_model = new All_Listins_Model( [ 'atts' => $atts ] );

            wp_enqueue_script('adminmainassets');
            wp_enqueue_script('atbdp-search-listing', ATBDP_PUBLIC_ASSETS . 'js/search-form-listing.js');
            wp_localize_script('atbdp-search-listing', 'atbdp_search', array(
                'ajaxnonce' => wp_create_nonce('bdas_ajax_nonce'),
                'ajax_url' => admin_url('admin-ajax.php'),
                'added_favourite' => __('Added to favorite', 'directorist'),
                'please_login' => __('Please login first', 'directorist')
            ));
            wp_enqueue_script('atbdp-range-slider');

            wp_localize_script( 'atbdp-range-slider', 'atbdp_range_slider', array(
                'Miles'     =>  $listings_model->miles,
                'default_val'   =>  $listings_model->default_radius_distance
            ));

            ob_start();
            if ( $listings_model->include ) {
                include ATBDP_DIR . 'public/assets/css/style.php';
            }
            if (!empty($listings_model->redirect_page_url)) {
                $redirect = '<script>window.location="' . esc_url($listings_model->redirect_page_url) . '"</script>';
                return $redirect;
            }

            if ( is_rtl() ){
                wp_enqueue_style('atbdp-search-style-rtl', ATBDP_PUBLIC_ASSETS . 'css/search-style-rtl.css');
            } else{
                wp_enqueue_style('atbdp-search-style', ATBDP_PUBLIC_ASSETS . 'css/search-style.css');
            }

            if ( 'listings_with_map' == $listings_model->view ) {
                $template_file = "listing-with-map/map-view";
                $extension_file = BDM_TEMPLATES_DIR . '/map-view';

                atbdp_get_shortcode_ext_template( $template_file, $extension_file, null, $listings_model, true );
                return ob_get_clean();
            }
            
            $template_file = "listings-archive/listings-{$listings_model->view}";
            atbdp_get_shortcode_template( $template_file, null, $listings_model, true );

            return ob_get_clean();
        }
        
        public function all_listing_draft($atts)
        {
            $logged_in_user_only = !empty($atts['logged_in_user_only']) ? $atts['logged_in_user_only'] : '';

            if ( 'yes' == $logged_in_user_only && ! atbdp_logged_in_user() ) {
                ob_start();
                // user not logged in;
                $error_message = sprintf(__('You need to be logged in to view the content of this page. You can login %s. Don\'t have an account? %s', 'directorist'), apply_filters('atbdp_listing_form_login_link', "<a href='" . ATBDP_Permalink::get_login_page_link() . "'> " . __('Here', 'directorist') . "</a>"), apply_filters('atbdp_listing_form_signup_link', "<a href='" . ATBDP_Permalink::get_registration_page_link() . "'> " . __('Sign Up', 'directorist') . "</a>")); 
                ?>
                <section class="directory_wrapper single_area">
                    <?php ATBDP()->helper->show_login_message($error_message); ?>
                </section>
                <?php

                return ob_get_clean();
            }

            wp_enqueue_script('adminmainassets');
            wp_enqueue_script('atbdp-search-listing');
            wp_localize_script('atbdp-search-listing', 'atbdp_search', array(
                'ajaxnonce' => wp_create_nonce('bdas_ajax_nonce'),
                'ajax_url' => admin_url('admin-ajax.php'),
                'added_favourite' => __('Added to favorite', 'directorist'),
                'please_login' => __('Please login first', 'directorist')
            ));
            wp_enqueue_script('atbdp-range-slider');
            $radius_search_unit = get_directorist_option('radius_search_unit', 'miles');
            if(!empty($radius_search_unit) && 'kilometers' == $radius_search_unit) {
                $miles = __(' Kilometers', 'directorist');
            }else{
                $miles = __(' Miles', 'directorist');
            }
            $default_radius_distance = get_directorist_option('listing_default_radius_distance', 0);
            wp_localize_script( 'atbdp-range-slider', 'atbdp_range_slider', array(
                'Miles'     =>  $miles,
                'default_val'   =>  $default_radius_distance
            ) );
            $listing_orderby = get_directorist_option('order_listing_by');
            $listing_view = get_directorist_option('default_listing_view');
            $filters_display = get_directorist_option('listings_display_filter', 'sliding');
            $listing_filters_button = get_directorist_option('listing_filters_button');
            $listing_order = get_directorist_option('sort_listing_by');
            $listing_grid_columns = get_directorist_option('all_listing_columns', 3);
            $display_listings_header = get_directorist_option('display_listings_header', 1);
            $listings_header_title = get_directorist_option('all_listing_header_title', __('Items Found', 'directorist'));
            $pagination = get_directorist_option('paginate_all_listings');
            $listings_map_height = get_directorist_option('listings_map_height', 350);
            $parameters = array(
                'view' => !empty($listing_view) ? $listing_view : 'grid',
                '_featured' => 1,
                'filterby' => '',
                'orderby' => !empty($listing_orderby) ? $listing_orderby : 'date',
                'order' => !empty($listing_order) ? $listing_order : 'asc',
                'listings_per_page' => (int)get_directorist_option('all_listing_page_items', 6),
                'show_pagination' => !empty($pagination) ? 'yes' : '',
                'header' => !empty($display_listings_header) ? 'yes' : '',
                'header_title' => !empty($listings_header_title) ? $listings_header_title : '',
                'category' => '',
                'location' => '',
                'tag' => '',
                'ids' => '',
                'columns' => !empty($listing_grid_columns) ? $listing_grid_columns : 3,
                'featured_only' => '',
                'popular_only' => '',
                'advanced_filter' => '',
                'display_preview_image' => 'yes',
                'action_before_after_loop' => 'yes',
                'logged_in_user_only' => '',
                'redirect_page_url' => '',
                'map_height' => !empty($listings_map_height) ? $listings_map_height : 350,
            );
            $params = apply_filters('atbdp_all_listings_params', $parameters);
            $atts = shortcode_atts($params, $atts);
            $categories = !empty($atts['category']) ? explode(',', $atts['category']) : '';
            $tags = !empty($atts['tag']) ? explode(',', $atts['tag']) : '';
            $locations = !empty($atts['location']) ? explode(',', $atts['location']) : '';
            $listing_id = !empty($atts['ids']) ? explode(',', $atts['ids']) : '';
            $columns = !empty($atts['columns']) ? $atts['columns'] : 3;
            $display_header = !empty($atts['header']) ? $atts['header'] : '';
            $header_title = !empty($atts['header_title']) ? $atts['header_title'] : '';
            $feature_only = !empty($atts['featured_only']) ? $atts['featured_only'] : '';
            $popular_only = !empty($atts['popular_only']) ? $atts['popular_only'] : '';
            $action_before_after_loop = !empty($atts['action_before_after_loop']) ? $atts['action_before_after_loop'] : '';
            $show_pagination = !empty($atts['show_pagination']) ? $atts['show_pagination'] : '';
            $display_image = !empty($atts['display_preview_image']) ? $atts['display_preview_image'] : '';
            $redirect_page_url = !empty($atts['redirect_page_url']) ? $atts['redirect_page_url'] : '';
            $map_height = !empty($atts['map_height']) ? $atts['map_height'] : '';
            $listing_type = !empty($atts['listing_type']) ? $atts['listing_type'] : '';
            $view = !empty($atts['view']) ? $atts['view'] : 'grid';
            //for pagination
            $paged = atbdp_get_paged_num();

            $has_featured = get_directorist_option('enable_featured_listing');
            if ($has_featured || is_fee_manager_active()) {
                $has_featured = $atts['_featured'];
            }
            if ('rand' == $atts['orderby']) {
                $current_order = atbdp_get_listings_current_order($atts['orderby']);
            } else {
                $current_order = atbdp_get_listings_current_order($atts['orderby'] . '-' . $atts['order']);
            }

            $view = atbdp_get_listings_current_view_name($atts['view']);

            $args = array(
                'post_type' => ATBDP_POST_TYPE,
                'post_status' => 'publish',
                'posts_per_page' => (int)$atts['listings_per_page'],
            );
            if ('yes' == $show_pagination) {
                $args['paged'] = $paged;
            } else {
                $args['no_found_rows'] = true;
            }

            $listingbyid_arg = array();

            if (!empty($listing_id)) {
                $listingbyid_arg = $listing_id;
                $args['post__in'] = $listingbyid_arg;
            }


            $tax_queries = array(); // initiate the tax query var to append to it different tax query

            if (!empty($categories) && !empty($locations) && !empty($tags)) {

                $tax_queries['tax_query'] = array(
                    'relation' => 'AND',
                    array(
                        'taxonomy' => ATBDP_CATEGORY,
                        'field' => 'slug',
                        'terms' => !empty($categories) ? $categories : array(),
                        'include_children' => true, /*@todo; Add option to include children or exclude it*/
                    ),
                    array(
                        'taxonomy' => ATBDP_LOCATION,
                        'field' => 'slug',
                        'terms' => !empty($locations) ? $locations : array(),
                        'include_children' => true, /*@todo; Add option to include children or exclude it*/
                    ),
                    array(
                        'taxonomy' => ATBDP_TAGS,
                        'field' => 'slug',
                        'terms' => !empty($tags) ? $tags : array(),
                        'include_children' => true, /*@todo; Add option to include children or exclude it*/
                    ),
                );
            } elseif (!empty($categories) && !empty($tags)) {
                $tax_queries['tax_query'] = array(
                    'relation' => 'AND',
                    array(
                        'taxonomy' => ATBDP_CATEGORY,
                        'field' => 'slug',
                        'terms' => !empty($categories) ? $categories : array(),
                        'include_children' => true, /*@todo; Add option to include children or exclude it*/
                    ),
                    array(
                        'taxonomy' => ATBDP_TAGS,
                        'field' => 'slug',
                        'terms' => !empty($tags) ? $tags : array(),
                        'include_children' => true, /*@todo; Add option to include children or exclude it*/
                    ),
                );
            } elseif (!empty($categories) && !empty($locations)) {
                $tax_queries['tax_query'] = array(
                    'relation' => 'AND',
                    array(
                        'taxonomy' => ATBDP_CATEGORY,
                        'field' => 'slug',
                        'terms' => !empty($categories) ? $categories : array(),
                        'include_children' => true, /*@todo; Add option to include children or exclude it*/
                    ),
                    array(
                        'taxonomy' => ATBDP_LOCATION,
                        'field' => 'slug',
                        'terms' => !empty($locations) ? $locations : array(),
                        'include_children' => true, /*@todo; Add option to include children or exclude it*/
                    ),

                );
            } elseif (!empty($tags) && !empty($locations)) {
                $tax_queries['tax_query'] = array(
                    'relation' => 'AND',
                    array(
                        'taxonomy' => ATBDP_TAGS,
                        'field' => 'slug',
                        'terms' => !empty($tags) ? $tags : array(),
                        'include_children' => true, /*@todo; Add option to include children or exclude it*/
                    ),
                    array(
                        'taxonomy' => ATBDP_LOCATION,
                        'field' => 'slug',
                        'terms' => !empty($locations) ? $locations : array(),
                        'include_children' => true, /*@todo; Add option to include children or exclude it*/
                    ),

                );
            } elseif (!empty($categories)) {
                $tax_queries['tax_query'] = array(
                    array(
                        'taxonomy' => ATBDP_CATEGORY,
                        'field' => 'slug',
                        'terms' => !empty($categories) ? $categories : array(),
                        'include_children' => true, /*@todo; Add option to include children or exclude it*/
                    )
                );
            } elseif (!empty($tags)) {
                $tax_queries['tax_query'] = array(
                    array(
                        'taxonomy' => ATBDP_TAGS,
                        'field' => 'slug',
                        'terms' => !empty($tags) ? $tags : array(),
                        'include_children' => true, /*@todo; Add option to include children or exclude it*/
                    )
                );
            } elseif (!empty($locations)) {
                $tax_queries['tax_query'] = array(
                    array(
                        'taxonomy' => ATBDP_LOCATION,
                        'field' => 'slug',
                        'terms' => !empty($locations) ? $locations : array(),
                        'include_children' => true, /*@todo; Add option to include children or exclude it*/
                    )
                );
            }
            $args['tax_query'] = $tax_queries;


            $meta_queries = array();

            $meta_queries['expired'] = array(
                'relation' => 'OR',
                array(
                    'key' => '_expiry_date',
                    'value' => current_time('mysql'),
                    'compare' => '>', // eg. expire date 6 <= current date 7 will return the post
                    'type' => 'DATETIME'
                ),
                array(
                    'key' => '_never_expire',
                    'value' => 1,
                )
            );
            $args['expired'] = $meta_queries;

            if ($has_featured) {

                if ('_featured' == $atts['filterby']) {
                    $meta_queries['_featured'] = array(
                        'key' => '_featured',
                        'value' => 1,
                        'compare' => '='
                    );

                } else {
                    $meta_queries['_featured'] = array(
                        'key' => '_featured',
                        'type' => 'NUMERIC',
                        'compare' => 'EXISTS',
                    );
                }

            }
            if ('yes' == $feature_only) {
                $meta_queries['_featured'] = array(
                    'key' => '_featured',
                    'value' => 1,
                    'compare' => '='
                );
            }

            $listings = get_atbdp_listings_ids();
            $rated = array();
            $listing_popular_by = get_directorist_option('listing_popular_by');
            $average_review_for_popular = get_directorist_option('average_review_for_popular', 4);
            $view_to_popular = get_directorist_option('views_for_popular');

            if (('yes' == $popular_only) || ('views-desc' === $current_order)) {
                if ($has_featured) {
                    if ('average_rating' === $listing_popular_by) {
                        if ($listings->have_posts()) {
                            while ($listings->have_posts()) {
                                $listings->the_post();
                                $listing_id = get_the_ID();
                                $average = ATBDP()->review->get_average($listing_id);
                                if ($average_review_for_popular <= $average) {
                                    $rated[] = get_the_ID();
                                }

                            }
                            $rating_id = array(
                                'post__in' => !empty($rated) ? $rated : array()
                            );
                            $args = array_merge($args, $rating_id);
                        }
                    } elseif ('view_count' === $listing_popular_by) {
                        $meta_queries['views'] = array(
                            'key' => '_atbdp_post_views_count',
                            'value' => $view_to_popular,
                            'type' => 'NUMERIC',
                            'compare' => '>=',
                        );

                        $args['orderby'] = array(
                            '_featured' => 'DESC',
                            'views' => 'DESC',
                        );

                    } else {
                        $meta_queries['views'] = array(
                            'key' => '_atbdp_post_views_count',
                            'value' => $view_to_popular,
                            'type' => 'NUMERIC',
                            'compare' => '>=',
                        );
                        $args['orderby'] = array(
                            '_featured' => 'DESC',
                            'views' => 'DESC',
                        );
                        if ($listings->have_posts()) {
                            while ($listings->have_posts()) {
                                $listings->the_post();
                                $listing_id = get_the_ID();
                                $average = ATBDP()->review->get_average($listing_id);
                                if ($average_review_for_popular <= $average) {
                                    $rated[] = get_the_ID();
                                }

                            }
                            $rating_id = array(
                                'post__in' => !empty($rated) ? $rated : array()
                            );
                            $args = array_merge($args, $rating_id);
                        }
                    }

                } else {
                    if ('average_rating' === $listing_popular_by) {
                        if ($listings->have_posts()) {
                            while ($listings->have_posts()) {
                                $listings->the_post();
                                $listing_id = get_the_ID();
                                $average = ATBDP()->review->get_average($listing_id);
                                if ($average_review_for_popular <= $average) {
                                    $rated[] = get_the_ID();
                                }

                            }
                            $rating_id = array(
                                'post__in' => !empty($rated) ? $rated : array()
                            );
                            $args = array_merge($args, $rating_id);
                        }
                    } elseif ('view_count' === $listing_popular_by) {
                        $meta_queries['views'] = array(
                            'key' => '_atbdp_post_views_count',
                            'value' => $view_to_popular,
                            'type' => 'NUMERIC',
                            'compare' => '>=',
                        );
                        $args['orderby'] = array(
                            'views' => 'DESC',
                        );
                    } else {
                        $meta_queries['views'] = array(
                            'key' => '_atbdp_post_views_count',
                            'value' => (int)$view_to_popular,
                            'type' => 'NUMERIC',
                            'compare' => '>=',
                        );
                        $args['orderby'] = array(
                            'views' => 'DESC',
                        );
                        if ($listings->have_posts()) {
                            while ($listings->have_posts()) {
                                $listings->the_post();
                                $listing_id = get_the_ID();
                                $average = ATBDP()->review->get_average($listing_id);
                                if ($average_review_for_popular <= $average) {
                                    $rated[] = get_the_ID();
                                }

                            }
                            $rating_id = array(
                                'post__in' => !empty($rated) ? $rated : array()
                            );
                            $args = array_merge($args, $rating_id);
                        }
                    }
                }
            }

            switch ($current_order) {
                case 'title-asc' :
                if ($has_featured) {
                    $args['meta_key'] = '_featured';
                    $args['orderby'] = array(
                        'meta_value_num' => 'DESC',
                        'title' => 'ASC',
                    );
                } else {
                    $args['orderby'] = 'title';
                    $args['order'] = 'ASC';
                };
                break;
                case 'title-desc' :
                if ($has_featured) {
                    $args['meta_key'] = '_featured';
                    $args['orderby'] = array(
                        'meta_value_num' => 'DESC',
                        'title' => 'DESC',
                    );
                } else {
                    $args['orderby'] = 'title';
                    $args['order'] = 'DESC';
                };
                break;
                case 'date-asc' :
                if ($has_featured) {
                    $args['meta_key'] = '_featured';
                    $args['orderby'] = array(
                        'meta_value_num' => 'DESC',
                        'date' => 'ASC',
                    );
                } else {
                    $args['orderby'] = 'date';
                    $args['order'] = 'ASC';
                };
                break;
                case 'date-desc' :
                if ($has_featured) {
                    $args['meta_key'] = '_featured';
                    $args['orderby'] = array(
                        'meta_value_num' => 'DESC',
                        'date' => 'DESC',
                    );
                } else {
                    $args['orderby'] = 'date';
                    $args['order'] = 'DESC';
                };
                break;
                case 'price-asc' :
                if ($has_featured) {
                    $meta_queries['price'] = array(
                        'key' => '_price',
                        'type' => 'NUMERIC',
                        'compare' => 'EXISTS',
                    );

                    $args['orderby'] = array(
                        '_featured' => 'DESC',
                        'price' => 'ASC',
                    );
                } else {
                    $args['meta_key'] = '_price';
                    $args['orderby'] = 'meta_value_num';
                    $args['order'] = 'ASC';
                };
                break;
                case 'price-desc' :
                if ($has_featured) {
                    $meta_queries['price'] = array(
                        'key' => '_price',
                        'type' => 'NUMERIC',
                        'compare' => 'EXISTS',
                    );

                    $args['orderby'] = array(
                        '_featured' => 'DESC',
                        'price' => 'DESC',
                    );
                } else {
                    $args['meta_key'] = '_price';
                    $args['orderby'] = 'meta_value_num';
                    $args['order'] = 'DESC';
                };
                break;
                case 'rand' :
                if ($has_featured) {
                    $args['meta_key'] = '_featured';
                    $args['orderby'] = 'meta_value_num rand';
                } else {
                    $args['orderby'] = 'rand';
                };
                break;
            }
            $meta_queries = apply_filters('atbdp_all_listings_meta_queries', $meta_queries);
            $count_meta_queries = count($meta_queries);
            if ($count_meta_queries) {
                $args['meta_query'] = ($count_meta_queries > 1) ? array_merge(array('relation' => 'AND'), $meta_queries) : $meta_queries;
            }

            $arguments = apply_filters('atbdp_all_listings_query_arguments', $args);
            $all_listings = new WP_Query($arguments);
            $paginate = get_directorist_option('paginate_all_listings');
            if ('yes' == $show_pagination) {
                $listing_count = '<span>' . $all_listings->found_posts . '</span>';
            } else {
                $listing_count = '<span>' . count($all_listings->posts) . '</span>';
            }
            $display_header = !empty($display_header) ? $display_header : '';
            $header_title_for_search = !empty($header_title) ? $header_title : '';
            $header_title = !empty($header_title) ? $listing_count . ' ' . $header_title : '';
            $listing_filters_button = !empty($atts['advanced_filter']) ? (('yes' === $atts['advanced_filter']) ? 1 : (('no' === $atts['advanced_filter']) ? 0 : $listing_filters_button)) : $listing_filters_button;
            $filters = get_directorist_option('listings_filter_button_text', __('Filters', 'directorist'));
            $text_placeholder = get_directorist_option('listings_search_text_placeholder', __('What are you looking for?', 'directorist'));
            $category_placeholder = get_directorist_option('listings_category_placeholder', __('Select a category', 'directorist'));
            $location_placeholder = get_directorist_option('listings_location_placeholder', __('Select a location', 'directorist'));
            $all_listing_title = !empty($all_listing_title) ? $all_listing_title : '';
            $data_for_template = compact('all_listings', 'all_listing_title', 'paged', 'paginate');
            $search_more_filters_fields = get_directorist_option('listing_filters_fields', array('search_text', 'search_category', 'search_location', 'search_price', 'search_price_range', 'search_rating', 'search_tag', 'search_custom_fields', 'radius_search'));
            $filters_button = get_directorist_option('listings_filters_button', array('reset_button', 'apply_button'));
            $reset_filters_text = get_directorist_option('listings_reset_text', __('Reset Filters', 'directorist'));
            $apply_filters_text = get_directorist_option('listings_apply_text', __('Apply Filters', 'directorist'));
            $sort_by_text = get_directorist_option('sort_by_text', __('Sort By', 'directorist'));
            $view_as_text = get_directorist_option('view_as_text', __('View As', 'directorist'));
            $view_as_items = get_directorist_option('listings_view_as_items', array('listings_grid', 'listings_list', 'listings_map'));
            $sort_by_items = get_directorist_option('listings_sort_by_items', array('a_z', 'z_a', 'latest', 'oldest', 'popular', 'price_low_high', 'price_high_low', 'random'));
            $listing_header_container_fluid = is_directoria_active() ? 'container' : 'container-fluid';
            $header_container_fluid = apply_filters('atbdp_listings_header_container_fluid', $listing_header_container_fluid);
            $listing_grid_container_fluid = is_directoria_active() ? 'container' : 'container-fluid';
            $grid_container_fluid = apply_filters('atbdp_listings_grid_container_fluid', $listing_grid_container_fluid);
            $listing_location_address = get_directorist_option('listing_location_address', 'map_api');
            $include = apply_filters('include_style_settings', true);

            ob_start();
            if ( $include ) {
                include ATBDP_DIR . 'public/assets/css/style.php';
            }
            if ( !empty($redirect_page_url) ) {
                $redirect = '<script>window.location="' . esc_url($redirect_page_url) . '"</script>';
                return $redirect;
            }


            if ( is_rtl() ){
                wp_enqueue_style('atbdp-search-style-rtl', ATBDP_PUBLIC_ASSETS . 'css/search-style-rtl.css');
            } else{
                wp_enqueue_style('atbdp-search-style', ATBDP_PUBLIC_ASSETS . 'css/search-style.css');
            }
            
            // Base Template Data
            !empty($args['data']) ? extract($args['data']) : array(); // data array contains all required var.
            $all_listings = !empty($all_listings) ? $all_listings : new WP_Query;
            $display_sortby_dropdown = get_directorist_option('display_sort_by', 1);
            $display_viewas_dropdown = get_directorist_option('display_view_as', 1);
            $display_image = !empty($display_image) ? $display_image : '';
            $show_pagination = !empty($show_pagination) ? $show_pagination : '';
            $paged = !empty($paged) ? $paged : '';

            $is_disable_price = get_directorist_option('disable_list_price');
            $view_as = get_directorist_option('grid_view_as', 'normal_grid');
            $column_width = 100 / (int)$columns . '%';

            if (is_rtl()) {
                wp_enqueue_style('atbdp-search-style-rtl', ATBDP_PUBLIC_ASSETS . 'css/search-style-rtl.css');
            } else {
                wp_enqueue_style('atbdp-search-style', ATBDP_PUBLIC_ASSETS . 'css/search-style.css');
            }

            // Header Template Data
            $address_label               = get_directorist_option('address_label',__('Address','directorist'));
            $fax_label                   = get_directorist_option('fax_label',__('Fax','directorist'));
            $email_label                 = get_directorist_option('email_label',__('Email','directorist'));
            $website_label               = get_directorist_option('website_label',__('Website','directorist'));
            $tag_label                   = get_directorist_option('tag_label',__('Tag','directorist'));
            $zip_label                   = get_directorist_option('zip_label',__('Zip','directorist'));
            $listing_filters_icon        = get_directorist_option('listing_filters_icon',1);
            $query_args = array(
                'parent'             => 0,
                'term_id'            => 0,
                'hide_empty'         => 0,
                'orderby'            => 'name',
                'order'              => 'asc',
                'show_count'         => 0,
                'single_only'        => 0,
                'pad_counts'         => true,
                'immediate_category' => 0,
                'active_term_id'     => 0,
                'ancestors'          => array()
            );
            $categories_fields = search_category_location_filter( $query_args, ATBDP_CATEGORY );
            $locations_fields  = search_category_location_filter( $query_args, ATBDP_LOCATION );
            $currency = get_directorist_option('g_currency', 'USD');
            $c_symbol = atbdp_currency_symbol($currency);


            if ( 'listings_with_map' == $view ) {
                $path = atbdp_get_theme_file("/directorist/shortcodes/listings/extension/listing-with-map/map-view.php");
                
                if ( $path ) {
                    include $path;
                } else {
                    include BDM_TEMPLATES_DIR . '/map-view.php';
                }

                return ob_get_clean();
            }

            $path = atbdp_get_theme_file("/directorist/shortcodes/listings-archive/listings-{$view}.php");
            if ( $path ) {
                include $path;
            } else {
                include ATBDP_TEMPLATES_DIR . "public-templates/shortcodes/listings-archive/listings-$view.php";
            }

            return ob_get_clean();
        }

        public function user_dashboard($atts)
        {
            $dashboard = new Directorist_Listing_Dashboard();
            return $dashboard->render_shortcode_user_dashboard($atts);
        }

        public function all_categories($atts)
        {
            ob_start();
            wp_enqueue_script('loc_cat_assets');
            $include = apply_filters('include_style_settings', true);
            if ($include) {
                include ATBDP_DIR . 'public/assets/css/style.php';
            }
            $display_categories_as = get_directorist_option('display_categories_as', 'grid');
            $categories_settings = array();
            $categories_settings['depth'] = get_directorist_option('categories_depth_number', 1);
            $categories_settings['show_count'] = get_directorist_option('display_listing_count', 1);
            $categories_settings['hide_empty'] = get_directorist_option('hide_empty_categories');
            $categories_settings['orderby'] = get_directorist_option('order_category_by', 'id');
            $categories_settings['order'] = get_directorist_option('sort_category_by', 'asc');

            $atts = shortcode_atts(array(
                'view' => $display_categories_as,
                'orderby' => $categories_settings['orderby'],
                'order' => $categories_settings['order'],
                'cat_per_page' => 100,
                'columns' => '',
                'slug' => '',
                'logged_in_user_only' => '',
                'redirect_page_url' => ''
            ), $atts);

            $logged_in_user_only = !empty($atts['logged_in_user_only']) ? $atts['logged_in_user_only'] : '';

            if ( 'yes' == $logged_in_user_only && ! atbdp_logged_in_user() ) {
                return $this->guard( ['type' => 'auth'] );
            }

            $categories = !empty($atts['slug']) ? explode(',', $atts['slug']) : array();
            $categories_settings['columns'] = !empty($atts['columns']) ? $atts['columns'] : get_directorist_option('categories_column_number', 3);
            $redirect_page_url = !empty($atts['redirect_page_url']) ? $atts['redirect_page_url'] : '';
            $args = array(
                'orderby' => $atts['orderby'],
                'order' => $atts['order'],
                'hide_empty' => !empty($categories_settings['hide_empty']) ? 1 : 0,
                'parent' => 0,
                'hierarchical' => !empty($categories_settings['hide_empty']) ? true : false,
                'slug' => !empty($categories) ? $categories : '',
            );

            $terms = get_terms(ATBDP_CATEGORY, apply_filters('atbdp_all_categories_argument', $args));
            $terms = array_slice($terms, 0, $atts['cat_per_page']);

            if (!empty($redirect_page_url)) {
                $redirect = '<script>window.location="' . esc_url($redirect_page_url) . '"</script>';
                return $redirect;
            }

            if (5 == $categories_settings['columns']) {
                $span = 'atbdp_col-5';
            } else {
                $span = 'col-md-' . floor(12 / $categories_settings['columns']). ' col-sm-6';
            }
            $container_fluid = 'container-fluid';

            if (!empty($terms) && !is_wp_error($terms)) {
                $grid_view_path = atbdp_get_theme_file("/directorist/shortcodes/categories/grid-view.php");
                $list_view_path = atbdp_get_theme_file("/directorist/shortcodes/categories/list-view.php");

                if ( 'grid' == $atts['view'] && $grid_view_path ) {
                    include $grid_view_path;
                }

                if ( 'grid' == $atts['view'] && ! $grid_view_path ) {
                    include ATBDP_TEMPLATES_DIR . "public-templates/shortcodes/all-categories/grid-view.php";
                }

                if ( 'list' == $atts['view'] && $list_view_path ) {
                    include $list_view_path;
                }

                if ( 'list' == $atts['view'] && ! $list_view_path ) {
                    include ATBDP_TEMPLATES_DIR . "public-templates/shortcodes/all-categories/list-view.php";
                }
            } else {
                _e('<p>No Results found!</p>', 'directorist');
            }
            return ob_get_clean();

        }

        public function atbdp_category($atts)
        {
            wp_enqueue_script('adminmainassets');
            wp_enqueue_script('atbdp-search-listing', ATBDP_PUBLIC_ASSETS . 'js/search-form-listing.js');
            wp_localize_script('atbdp-search-listing', 'atbdp_search', array(
                'ajaxnonce' => wp_create_nonce('bdas_ajax_nonce'),
                'ajax_url' => admin_url('admin-ajax.php'),
                'added_favourite' => __('Added to favorite', 'directorist'),
                'please_login' => __('Please login first', 'directorist')
            ));
            wp_enqueue_script('atbdp-range-slider');
            $radius_search_unit            = get_directorist_option('radius_search_unit', 'miles');
            if(!empty($radius_search_unit) && 'kilometers' == $radius_search_unit) {
                $miles = __(' Kilometers', 'directorist');
            }else{
                $miles = __(' Miles', 'directorist');
            }
            $default_radius_distance = get_directorist_option('listing_default_radius_distance', 0);
            wp_localize_script( 'atbdp-range-slider', 'atbdp_range_slider', array(
                'Miles'     =>  $miles,
                'default_val'   =>  $default_radius_distance
            ) );
            $category_slug = get_query_var('atbdp_category');

            $term = '';

            if ('' == $category_slug && !empty($atts['id'])) {
                $term = get_term_by('id', (int)$atts['id'], ATBDP_CATEGORY);
                $category_slug = $term->slug;
            } elseif ('' != $category_slug) {
                $term = get_term_by('slug', $category_slug, ATBDP_CATEGORY);
            }

            if ('' != $category_slug) {
                $listing_orderby = get_directorist_option('order_listing_by');
                $listing_view = get_directorist_option('default_listing_view');
                $listing_order = get_directorist_option('sort_listing_by');
                $listing_grid_columns = get_directorist_option('all_listing_columns', 3);
                $display_listings_header = get_directorist_option('display_listings_header', 1);
                $listings_header_title = get_directorist_option('all_listing_header_title', __('Items Found', 'directorist'));
                $filters_display = get_directorist_option('listings_display_filter', 'sliding');
                $pagination = get_directorist_option('paginate_all_listings');
                $listings_map_height = get_directorist_option('listings_map_height', 350);
                $params = apply_filters('atbdp_single_cat_param', array(
                    'view' => !empty($listing_view) ? $listing_view : 'grid',
                    '_featured' => 1,
                    'filterby' => '',
                    'orderby' => !empty($listing_orderby) ? $listing_orderby : 'date',
                    'order' => !empty($listing_order) ? $listing_order : 'asc',
                    'listings_per_page' => (int)get_directorist_option('all_listing_page_items', 6),
                    'pagination' => 1,
                    'show_pagination' => !empty($pagination) ? 'yes' : '',
                    'header' => !empty($display_listings_header) ? 'yes' : '',
                    'header_title' => !empty($listings_header_title) ? $listings_header_title : '',
                    'columns' => !empty($listing_grid_columns) ? $listing_grid_columns : 3,
                    'map_height' => !empty($listings_map_height) ? $listings_map_height : 350,
                    'logged_in_user_only' => '',
                    'redirect_page_url' => '',
                ));
                $atts = shortcode_atts($params, $atts);

                $logged_in_user_only = !empty($atts['logged_in_user_only']) ? $atts['logged_in_user_only'] : '';
                $redirect_page_url = !empty($atts['redirect_page_url']) ? $atts['redirect_page_url'] : '';
                
                if ( 'yes' === $logged_in_user_only && ! atbdp_logged_in_user() ) {
                    return $this->guard( ['type' => 'auth'] );
                }

                $columns = !empty($atts['columns']) ? $atts['columns'] : 3;
                $display_header = !empty($atts['header']) ? $atts['header'] : '';
                $header_title = !empty($atts['header_title']) ? $atts['header_title'] : '';
                $show_pagination = !empty($atts['show_pagination']) ? $atts['show_pagination'] : '';
                $map_height = !empty($atts['map_height']) ? $atts['map_height'] : '';
                //for pagination
                $paged = atbdp_get_paged_num();

                $has_featured = get_directorist_option('enable_featured_listing');
                if ($has_featured || is_fee_manager_active()) {
                    $has_featured = $atts['_featured'];
                }

                $current_order = atbdp_get_listings_current_order($atts['orderby'] . '-' . $atts['order']);
                $view = atbdp_get_listings_current_view_name($atts['view']);

                $args = array(
                    'post_type' => ATBDP_POST_TYPE,
                    'post_status' => 'publish',
                    'posts_per_page' => (int)$atts['listings_per_page']
                );

                if ('yes' == $show_pagination) {
                    $args['paged'] = $paged;
                } else {
                    $args['no_found_rows'] = true;
                }
                $tax_queries[] = array(
                    'taxonomy' => ATBDP_CATEGORY,
                    'field' => 'slug',
                    'terms' => $category_slug,
                    'include_children' => true,
                );

                $args['tax_query'] = $tax_queries;

                $meta_queries = array();
                $meta_queries['expired'] = array(
                    'relation' => 'OR',
                    array(
                        'key' => '_expiry_date',
                        'value' => current_time('mysql'),
                        'compare' => '>', // eg. expire date 6 <= current date 7 will return the post
                        'type' => 'DATETIME'
                    ),
                    array(
                        'key' => '_never_expire',
                        'value' => 1,
                    )

                );
                $args['expired'] = $meta_queries;
                if ($has_featured) {

                    if ('_featured' == $atts['filterby']) {
                        $meta_queries['_featured'] = array(
                            'key' => '_featured',
                            'value' => 1,
                            'compare' => '='
                        );

                    } else {
                        $meta_queries['_featured'] = array(
                            'key' => '_featured',
                            'type' => 'NUMERIC',
                            'compare' => 'EXISTS',
                        );
                    }

                }

                switch ($current_order) {
                    case 'title-asc' :
                    if ($has_featured) {
                        $args['meta_key'] = '_featured';
                        $args['orderby'] = array(
                            'meta_value_num' => 'DESC',
                            'title' => 'ASC',
                        );
                    } else {
                        $args['orderby'] = 'title';
                        $args['order'] = 'ASC';
                    };
                    break;
                    case 'title-desc' :
                    if ($has_featured) {
                        $args['meta_key'] = '_featured';
                        $args['orderby'] = array(
                            'meta_value_num' => 'DESC',
                            'title' => 'DESC',
                        );
                    } else {
                        $args['orderby'] = 'title';
                        $args['order'] = 'DESC';
                    };
                    break;
                    case 'date-asc' :
                    if ($has_featured) {
                        $args['meta_key'] = '_featured';
                        $args['orderby'] = array(
                            'meta_value_num' => 'DESC',
                            'date' => 'ASC',
                        );
                    } else {
                        $args['orderby'] = 'date';
                        $args['order'] = 'ASC';
                    };
                    break;
                    case 'date-desc' :
                    if ($has_featured) {
                        $args['meta_key'] = '_featured';
                        $args['orderby'] = array(
                            'meta_value_num' => 'DESC',
                            'date' => 'DESC',
                        );
                    } else {
                        $args['orderby'] = 'date';
                        $args['order'] = 'DESC';
                    };
                    break;
                    case 'price-asc' :
                    if ($has_featured) {
                        $meta_queries['price'] = array(
                            'key' => '_price',
                            'type' => 'NUMERIC',
                            'compare' => 'EXISTS',
                        );

                        $args['orderby'] = array(
                            '_featured' => 'DESC',
                            'price' => 'ASC',
                        );
                    } else {
                        $args['meta_key'] = '_price';
                        $args['orderby'] = 'meta_value_num';
                        $args['order'] = 'ASC';
                    };
                    break;
                    case 'price-desc' :
                    if ($has_featured) {
                        $meta_queries['price'] = array(
                            'key' => '_price',
                            'type' => 'NUMERIC',
                            'compare' => 'EXISTS',
                        );

                        $args['orderby'] = array(
                            '_featured' => 'DESC',
                            'price' => 'DESC',
                        );
                    } else {
                        $args['meta_key'] = '_price';
                        $args['orderby'] = 'meta_value_num';
                        $args['order'] = 'DESC';
                    };
                    break;
                    case 'views-desc' :
                    $listings = get_atbdp_listings_ids();
                    $rated = array();
                    $listing_popular_by = get_directorist_option('listing_popular_by');
                    $average_review_for_popular = get_directorist_option('average_review_for_popular', 4);
                    $view_to_popular = get_directorist_option('views_for_popular');
                    if ($has_featured) {
                        if ('average_rating' === $listing_popular_by) {
                            if ($listings->have_posts()) {
                                while ($listings->have_posts()) {
                                    $listings->the_post();
                                    $listing_id = get_the_ID();
                                    $average = ATBDP()->review->get_average($listing_id);
                                    if ($average_review_for_popular <= $average) {
                                        $rated[] = get_the_ID();
                                    }

                                }
                                $rating_id = array(
                                    'post__in' => !empty($rated) ? $rated : array()
                                );
                                $args = array_merge($args, $rating_id);
                            }
                        } elseif ('view_count' === $listing_popular_by) {
                            $meta_queries['views'] = array(
                                'key' => '_atbdp_post_views_count',
                                'value' => $view_to_popular,
                                'type' => 'NUMERIC',
                                'compare' => '>=',
                            );
                            $args['orderby'] = array(
                                '_featured' => 'DESC',
                                'views' => 'DESC',
                            );
                        } else {
                            $meta_queries['views'] = array(
                                'key' => '_atbdp_post_views_count',
                                'value' => $view_to_popular,
                                'type' => 'NUMERIC',
                                'compare' => '>=',
                            );
                            $args['orderby'] = array(
                                '_featured' => 'DESC',
                                'views' => 'DESC',
                            );
                            if ($listings->have_posts()) {
                                while ($listings->have_posts()) {
                                    $listings->the_post();
                                    $listing_id = get_the_ID();
                                    $average = ATBDP()->review->get_average($listing_id);
                                    if ($average_review_for_popular <= $average) {
                                        $rated[] = get_the_ID();
                                    }

                                }
                                $rating_id = array(
                                    'post__in' => !empty($rated) ? $rated : array()
                                );
                                $args = array_merge($args, $rating_id);
                            }
                        }

                    } else {
                        if ('average_rating' === $listing_popular_by) {
                            if ($listings->have_posts()) {
                                while ($listings->have_posts()) {
                                    $listings->the_post();
                                    $listing_id = get_the_ID();
                                    $average = ATBDP()->review->get_average($listing_id);
                                    if ($average_review_for_popular <= $average) {
                                        $rated[] = get_the_ID();
                                    }

                                }
                                $rating_id = array(
                                    'post__in' => !empty($rated) ? $rated : array()
                                );
                                $args = array_merge($args, $rating_id);
                            }
                        } elseif ('view_count' === $listing_popular_by) {
                            $meta_queries['views'] = array(
                                'key' => '_atbdp_post_views_count',
                                'value' => $view_to_popular,
                                'type' => 'NUMERIC',
                                'compare' => '>=',
                            );
                            $args['orderby'] = array(
                                'views' => 'DESC',
                            );
                        } else {
                            $meta_queries['views'] = array(
                                'key' => '_atbdp_post_views_count',
                                'value' => (int)$view_to_popular,
                                'type' => 'NUMERIC',
                                'compare' => '>=',
                            );
                            $args['orderby'] = array(
                                'views' => 'DESC',
                            );
                            if ($listings->have_posts()) {
                                while ($listings->have_posts()) {
                                    $listings->the_post();
                                    $listing_id = get_the_ID();
                                    $average = ATBDP()->review->get_average($listing_id);
                                    if ($average_review_for_popular <= $average) {
                                        $rated[] = get_the_ID();
                                    }

                                }
                                $rating_id = array(
                                    'post__in' => !empty($rated) ? $rated : array()
                                );
                                $args = array_merge($args, $rating_id);
                            }
                        }
                    }
                    break;
                    case 'rand' :
                    if ($has_featured) {
                        $args['meta_key'] = '_featured';
                        $args['orderby'] = 'meta_value_num rand';
                    } else {
                        $args['orderby'] = 'rand';
                    };
                    break;
                }
                $meta_queries = apply_filters('atbdp_single_category_meta_queries', $meta_queries);
                $count_meta_queries = count($meta_queries);
                if ($count_meta_queries) {
                    $args['meta_query'] = ($count_meta_queries > 1) ? array_merge(array('relation' => 'AND'), $meta_queries) : $meta_queries;
                }

                $all_listings = new WP_Query($args);
                if ('yes' == $show_pagination) {
                    $listing_count = '<span>' . $all_listings->found_posts . '</span>';
                } else {
                    $listing_count = '<span>' . count($all_listings->posts) . '</span>';
                }
                $display_header = !empty($display_header) ? $display_header : '';
                $header_title = !empty($header_title) ? $listing_count . ' ' . $header_title : '';
                $listing_filters_button = get_directorist_option('listing_filters_button', 1);
                $filters = get_directorist_option('listings_filter_button_text', __('Filters', 'directorist'));
                $text_placeholder = get_directorist_option('listings_search_text_placeholder', __('What are you looking for?', 'directorist'));
                $category_placeholder = get_directorist_option('listings_category_placeholder', __('Select a category', 'directorist'));
                $location_placeholder = get_directorist_option('listings_location_placeholder', __('Select a location', 'directorist'));
                //$data_for_template            = compact('all_listings', 'all_listing_title', 'paged', 'paginate');
                $search_more_filters_fields = get_directorist_option('listing_filters_fields', array('search_text', 'search_category', 'search_location', 'search_price', 'search_price_range', 'search_rating', 'search_tag', 'search_custom_fields', 'radius_search'));
                $filters_button = get_directorist_option('listings_filters_button', array('reset_button', 'apply_button'));
                $reset_filters_text = get_directorist_option('listings_reset_text', __('Reset Filters', 'directorist'));
                $apply_filters_text = get_directorist_option('listings_apply_text', __('Apply Filters', 'directorist'));
                $sort_by_text = get_directorist_option('sort_by_text', __('Sort By', 'directorist'));
                $view_as_text = get_directorist_option('view_as_text', __('View As', 'directorist'));
                //$data_for_template            = compact('all_listings', 'all_listing_title', 'paged', 'paginate');
                $view_as_items = get_directorist_option('listings_view_as_items', array('listings_grid', 'listings_list', 'listings_map'));
                $sort_by_items = get_directorist_option('listings_sort_by_items', array('a_z', 'z_a', 'latest', 'oldest', 'popular', 'price_low_high', 'price_high_low', 'random'));
                $listing_header_container_fluid = is_directoria_active() ? 'container' : 'container-fluid';
                $header_container_fluid = apply_filters('atbdp_single_cat_header_container_fluid', $listing_header_container_fluid);
                $listing_grid_container_fluid = is_directoria_active() ? 'container' : 'container-fluid';
                $grid_container_fluid = apply_filters('atbdp_single_cat_grid_container_fluid', $listing_grid_container_fluid);
                $listing_location_address = get_directorist_option('listing_location_address', 'map_api');
                ob_start();
                $include = apply_filters('include_style_settings', true);
                if ($include) {
                    include ATBDP_DIR . 'public/assets/css/style.php';
                }
                if (!empty($redirect_page_url)) {
                    $redirect = '<script>window.location="' . esc_url($redirect_page_url) . '"</script>';
                    return $redirect;
                }
                
                
                // Base Template Data
                !empty($args['data']) ? extract($args['data']) : array(); // data array contains all required var.
                $all_listings = !empty($all_listings) ? $all_listings : new WP_Query;
                $display_sortby_dropdown = get_directorist_option('display_sort_by', 1);
                $display_viewas_dropdown = get_directorist_option('display_view_as', 1);
                $display_image = !empty($display_image) ? $display_image : '';
                $show_pagination = !empty($show_pagination) ? $show_pagination : '';
                $paged = !empty($paged) ? $paged : '';

                $is_disable_price = get_directorist_option('disable_list_price');
                $view_as = get_directorist_option('grid_view_as', 'normal_grid');
                $column_width = 100 / (int)$columns . '%';

                if (is_rtl()) {
                    wp_enqueue_style('atbdp-search-style-rtl', ATBDP_PUBLIC_ASSETS . 'css/search-style-rtl.css');
                } else {
                    wp_enqueue_style('atbdp-search-style', ATBDP_PUBLIC_ASSETS . 'css/search-style.css');
                }

                // Header Template Data
                $address_label               = get_directorist_option('address_label',__('Address','directorist'));
                $fax_label                   = get_directorist_option('fax_label',__('Fax','directorist'));
                $email_label                 = get_directorist_option('email_label',__('Email','directorist'));
                $website_label               = get_directorist_option('website_label',__('Website','directorist'));
                $tag_label                   = get_directorist_option('tag_label',__('Tag','directorist'));
                $zip_label                   = get_directorist_option('zip_label',__('Zip','directorist'));
                $listing_filters_icon        = get_directorist_option('listing_filters_icon',1);
                $query_args = array(
                    'parent'             => 0,
                    'term_id'            => 0,
                    'hide_empty'         => 0,
                    'orderby'            => 'name',
                    'order'              => 'asc',
                    'show_count'         => 0,
                    'single_only'        => 0,
                    'pad_counts'         => true,
                    'immediate_category' => 0,
                    'active_term_id'     => 0,
                    'ancestors'          => array()
                );
                $categories_fields = search_category_location_filter( $query_args, ATBDP_CATEGORY );
                $locations_fields  = search_category_location_filter( $query_args, ATBDP_LOCATION );
                $currency = get_directorist_option('g_currency', 'USD');
                $c_symbol = atbdp_currency_symbol($currency);

                // If listings_with_map is active
                if ( 'listings_with_map' == $view ) {
                    $path = atbdp_get_theme_file("/directorist/shortcodes/listings/extension/listing-with-map/map-view.php");
                    
                    if ( $path ) {
                        include $path;
                    } else {
                        include BDM_TEMPLATES_DIR . '/map-view.php';
                    }

                    return ob_get_clean();
                }

                // Default Template
                $path = atbdp_get_theme_file("/directorist/shortcodes/listings-archive/listings-{$view}.php");
                if ( $path ) {
                    include $path;
                } else {
                    include ATBDP_TEMPLATES_DIR . "public-templates/shortcodes/listings-archive/listings-$view.php";
                }

                return ob_get_clean();

            }
            return '<span>' . __('No Results Found.', 'directorist') . '</span>';
        }

        public function all_locations($atts)
        {
            wp_enqueue_script('loc_cat_assets');
            ob_start();
            $include = apply_filters('include_style_settings', true);
            if ($include) {
                include ATBDP_DIR . 'public/assets/css/style.php';
            }
            $display_locations_as = get_directorist_option('display_locations_as', 'grid');
            $locations_settings = array();
            $locations_settings['depth'] = get_directorist_option('locations_depth_number', 1);
            $locations_settings['show_count'] = get_directorist_option('display_location_listing_count', 1);
            $locations_settings['hide_empty'] = get_directorist_option('hide_empty_locations');
            $locations_settings['orderby'] = get_directorist_option('order_location_by', 'id');
            $locations_settings['order'] = get_directorist_option('sort_location_by', 'asc');

            $atts = shortcode_atts(array(
                'view' => $display_locations_as,
                'orderby' => $locations_settings['orderby'],
                'order' => $locations_settings['order'],
                'loc_per_page' => 100,
                'columns' => '',
                'slug' => '',
                'logged_in_user_only' => '',
                'redirect_page_url' => ''
            ), $atts);
            $locations_settings['columns'] = !empty($atts['columns']) ? $atts['columns'] : get_directorist_option('locations_column_number', 3);
            $locations = !empty($atts['slug']) ? explode(',', $atts['slug']) : array();
            $logged_in_user_only = !empty($atts['logged_in_user_only']) ? $atts['logged_in_user_only'] : '';
            $redirect_page_url = !empty($atts['redirect_page_url']) ? $atts['redirect_page_url'] : '';
            
            if ( 'yes' === $logged_in_user_only && ! atbdp_logged_in_user() ) {
                return $this->guard( ['type' => 'auth'] );
            }
            
            $args = array(
                'orderby' => $atts['orderby'],
                'order' => $atts['order'],
                'hide_empty' => !empty($locations_settings['hide_empty']) ? 1 : 0,
                'parent' => 0,
                'hierarchical' => !empty($locations_settings['hide_empty']) ? true : false,
                'slug' => !empty($locations) ? $locations : ''
            );

            $terms = get_terms(ATBDP_LOCATION, apply_filters('atbdp_all_locations_argument', $args));
            $terms = array_slice($terms, 0, $atts['loc_per_page']);
            if (!empty($redirect_page_url)) {
                $redirect = '<script>window.location="' . esc_url($redirect_page_url) . '"</script>';
                return $redirect;
            }
            
            
            if (!empty($terms) && !is_wp_error($terms)) {
                $grid_view_path = atbdp_get_theme_file("/directorist/shortcodes/locations/grid-view.php");
                $list_view_path = atbdp_get_theme_file("/directorist/shortcodes/locations/list-view.php");

                if ( 'grid' == $atts['view'] && $grid_view_path ) {
                    include $grid_view_path;
                }

                if ( 'grid' == $atts['view'] && ! $grid_view_path ) {
                    include ATBDP_TEMPLATES_DIR . "public-templates/shortcodes/locations/grid-view.php";
                }

                if ( 'list' == $atts['view'] && $list_view_path ) {
                    include $list_view_path;
                }

                if ( 'list' == $atts['view'] && ! $list_view_path ) {
                    include ATBDP_TEMPLATES_DIR . "public-templates/shortcodes/locations/list-view.php";
                }
            } else {
                _e('<p>No Results found!</p>', 'directorist');
            }


            return ob_get_clean();

        }

        public function atbdp_location($atts)
        {
            wp_enqueue_script('adminmainassets');
            $term_slug = get_query_var('atbdp_location');
            wp_enqueue_script('atbdp-search-listing', ATBDP_PUBLIC_ASSETS . 'js/search-form-listing.js');
            wp_localize_script('atbdp-search-listing', 'atbdp_search', array(
                'ajaxnonce' => wp_create_nonce('bdas_ajax_nonce'),
                'ajax_url' => admin_url('admin-ajax.php'),
                'added_favourite' => __('Added to favorite', 'directorist'),
                'please_login' => __('Please login first', 'directorist')
            ));
            wp_enqueue_script('atbdp-range-slider');
            $radius_search_unit            = get_directorist_option('radius_search_unit', 'miles');
            if(!empty($radius_search_unit) && 'kilometers' == $radius_search_unit) {
                $miles = __(' Kilometers', 'directorist');
            }else{
                $miles = __(' Miles', 'directorist');
            }
            $default_radius_distance = get_directorist_option('listing_default_radius_distance', 0);
            wp_localize_script( 'atbdp-range-slider', 'atbdp_range_slider', array(
                'Miles'     =>  $miles,
                'default_val'   =>  $default_radius_distance
            ) );
            $term = '';

            if ('' == $term_slug && !empty($atts['id'])) {
                $term = get_term_by('id', (int)$atts['id'], ATBDP_LOCATION);
                $term_slug = $term->slug;
            } elseif ('' != $term_slug) {
                $term = get_term_by('slug', $term_slug, ATBDP_LOCATION);
            }

            if ('' != $term_slug) {
                $listing_orderby = get_directorist_option('order_listing_by');
                $listing_view = get_directorist_option('default_listing_view');
                $listing_order = get_directorist_option('sort_listing_by');
                $listing_grid_columns = get_directorist_option('all_listing_columns', 3);
                $display_listings_header = get_directorist_option('display_listings_header', 1);
                $listings_header_title = get_directorist_option('all_listing_header_title', __('Items Found', 'directorist'));
                $filters_display = get_directorist_option('listings_display_filter', 'sliding');
                $pagination = get_directorist_option('paginate_all_listings');
                $listings_map_height = get_directorist_option('listings_map_height', 350);
                $params = apply_filters('atbdp_single_location_param', array(
                    'view' => !empty($listing_view) ? $listing_view : 'grid',
                    '_featured' => 1,
                    'filterby' => '',
                    'orderby' => !empty($listing_orderby) ? $listing_orderby : 'date',
                    'order' => !empty($listing_order) ? $listing_order : 'asc',
                    'listings_per_page' => (int)get_directorist_option('all_listing_page_items', 6),
                    'show_pagination' => !empty($pagination) ? 'yes' : '',
                    'header' => !empty($display_listings_header) ? 'yes' : '',
                    'header_title' => !empty($listings_header_title) ? $listings_header_title : '',
                    'columns' => !empty($listing_grid_columns) ? $listing_grid_columns : 3,
                    'map_height' => !empty($listings_map_height) ? $listings_map_height : 350,
                    'logged_in_user_only' => '',
                    'redirect_page_url' => ''
                ));
                $atts = shortcode_atts($params, $atts);

                $logged_in_user_only = !empty($atts['logged_in_user_only']) ? $atts['logged_in_user_only'] : '';
                $redirect_page_url = !empty($atts['redirect_page_url']) ? $atts['redirect_page_url'] : '';

                if ( 'yes' === $logged_in_user_only && ! atbdp_logged_in_user() ) {
                    return $this->guard( ['type' => 'auth'] );
                }                

                $columns = !empty($atts['columns']) ? $atts['columns'] : 3;
                $display_header = !empty($atts['header']) ? $atts['header'] : '';
                $header_title = !empty($atts['header_title']) ? $atts['header_title'] : '';
                $header_sub_title = !empty($atts['header_sub_title']) ? $atts['header_sub_title'] : '';
                $show_pagination = !empty($atts['show_pagination']) ? $atts['show_pagination'] : '';
                $map_height = !empty($atts['map_height']) ? $atts['map_height'] : '';
                //for pagination
                $paged = atbdp_get_paged_num();

                $has_featured = get_directorist_option('enable_featured_listing');
                if ($has_featured || is_fee_manager_active()) {
                    $has_featured = $atts['_featured'];
                }
                if ('rand' == $atts['orderby']) {
                    $current_order = atbdp_get_listings_current_order($atts['orderby']);
                } else {
                    $current_order = atbdp_get_listings_current_order($atts['orderby'] . '-' . $atts['order']);
                }
                $view = atbdp_get_listings_current_view_name($atts['view']);
                $args = array(
                    'post_type' => ATBDP_POST_TYPE,
                    'post_status' => 'publish',
                    'posts_per_page' => (int)$atts['listings_per_page']
                );
                if ('yes' == $show_pagination) {
                    $args['paged'] = $paged;
                } else {
                    $args['no_found_rows'] = true;
                }
                $tax_queries[] = array(
                    'taxonomy' => ATBDP_LOCATION,
                    'field' => 'slug',
                    'terms' => $term_slug,
                    'include_children' => true,
                );

                $args['tax_query'] = $tax_queries;

                $meta_queries = array();
                $meta_queries['expired'] = array(
                    'relation' => 'OR',
                    array(
                        'key' => '_expiry_date',
                        'value' => current_time('mysql'),
                        'compare' => '>', // eg. expire date 6 <= current date 7 will return the post
                        'type' => 'DATETIME'
                    ),
                    array(
                        'key' => '_never_expire',
                        'value' => 1,
                    )

                );
                $args['expired'] = $meta_queries;
                if ($has_featured) {

                    if ('_featured' == $atts['filterby']) {
                        $meta_queries['_featured'] = array(
                            'key' => '_featured',
                            'value' => 1,
                            'compare' => '='
                        );

                    } else {
                        $meta_queries['_featured'] = array(
                            'key' => '_featured',
                            'type' => 'NUMERIC',
                            'compare' => 'EXISTS',
                        );
                    }

                }


                switch ($current_order) {
                    case 'title-asc' :
                    if ($has_featured) {
                        $args['meta_key'] = '_featured';
                        $args['orderby'] = array(
                            'meta_value_num' => 'DESC',
                            'title' => 'ASC',
                        );
                    } else {
                        $args['orderby'] = 'title';
                        $args['order'] = 'ASC';
                    };
                    break;
                    case 'title-desc' :
                    if ($has_featured) {
                        $args['meta_key'] = '_featured';
                        $args['orderby'] = array(
                            'meta_value_num' => 'DESC',
                            'title' => 'DESC',
                        );
                    } else {
                        $args['orderby'] = 'title';
                        $args['order'] = 'DESC';
                    };
                    break;
                    case 'date-asc' :
                    if ($has_featured) {
                        $args['meta_key'] = '_featured';
                        $args['orderby'] = array(
                            'meta_value_num' => 'DESC',
                            'date' => 'ASC',
                        );
                    } else {
                        $args['orderby'] = 'date';
                        $args['order'] = 'ASC';
                    };
                    break;
                    case 'date-desc' :
                    if ($has_featured) {
                        $args['meta_key'] = '_featured';
                        $args['orderby'] = array(
                            'meta_value_num' => 'DESC',
                            'date' => 'DESC',
                        );
                    } else {
                        $args['orderby'] = 'date';
                        $args['order'] = 'DESC';
                    };
                    break;
                    case 'price-asc' :
                    if ($has_featured) {
                        $meta_queries['price'] = array(
                            'key' => '_price',
                            'type' => 'NUMERIC',
                            'compare' => 'EXISTS',
                        );

                        $args['orderby'] = array(
                            '_featured' => 'DESC',
                            'price' => 'ASC',
                        );
                    } else {
                        $args['meta_key'] = '_price';
                        $args['orderby'] = 'meta_value_num';
                        $args['order'] = 'ASC';
                    };
                    break;
                    case 'price-desc' :
                    if ($has_featured) {
                        $meta_queries['price'] = array(
                            'key' => '_price',
                            'type' => 'NUMERIC',
                            'compare' => 'EXISTS',
                        );

                        $args['orderby'] = array(
                            '_featured' => 'DESC',
                            'price' => 'DESC',
                        );
                    } else {
                        $args['meta_key'] = '_price';
                        $args['orderby'] = 'meta_value_num';
                        $args['order'] = 'DESC';
                    };
                    break;
                    case 'views-desc' :
                    $listings = get_atbdp_listings_ids();
                    $rated = array();
                    $listing_popular_by = get_directorist_option('listing_popular_by');
                    $average_review_for_popular = get_directorist_option('average_review_for_popular', 4);
                    $view_to_popular = get_directorist_option('views_for_popular');
                    if ($has_featured) {
                        if ('average_rating' === $listing_popular_by) {
                            if ($listings->have_posts()) {
                                while ($listings->have_posts()) {
                                    $listings->the_post();
                                    $listing_id = get_the_ID();
                                    $average = ATBDP()->review->get_average($listing_id);
                                    if ($average_review_for_popular <= $average) {
                                        $rated[] = get_the_ID();
                                    }

                                }
                                $rating_id = array(
                                    'post__in' => !empty($rated) ? $rated : array()
                                );
                                $args = array_merge($args, $rating_id);
                            }
                        } elseif ('view_count' === $listing_popular_by) {
                            $meta_queries['views'] = array(
                                'key' => '_atbdp_post_views_count',
                                'value' => $view_to_popular,
                                'type' => 'NUMERIC',
                                'compare' => '>=',
                            );
                            $args['orderby'] = array(
                                '_featured' => 'DESC',
                                'views' => 'DESC',
                            );
                        } else {
                            $meta_queries['views'] = array(
                                'key' => '_atbdp_post_views_count',
                                'value' => $view_to_popular,
                                'type' => 'NUMERIC',
                                'compare' => '>=',
                            );
                            $args['orderby'] = array(
                                '_featured' => 'DESC',
                                'views' => 'DESC',
                            );
                            if ($listings->have_posts()) {
                                while ($listings->have_posts()) {
                                    $listings->the_post();
                                    $listing_id = get_the_ID();
                                    $average = ATBDP()->review->get_average($listing_id);
                                    if ($average_review_for_popular <= $average) {
                                        $rated[] = get_the_ID();
                                    }

                                }
                                $rating_id = array(
                                    'post__in' => !empty($rated) ? $rated : array()
                                );
                                $args = array_merge($args, $rating_id);
                            }
                        }

                    } else {
                        if ('average_rating' === $listing_popular_by) {
                            if ($listings->have_posts()) {
                                while ($listings->have_posts()) {
                                    $listings->the_post();
                                    $listing_id = get_the_ID();
                                    $average = ATBDP()->review->get_average($listing_id);
                                    if ($average_review_for_popular <= $average) {
                                        $rated[] = get_the_ID();
                                    }

                                }
                                $rating_id = array(
                                    'post__in' => !empty($rated) ? $rated : array()
                                );
                                $args = array_merge($args, $rating_id);
                            }
                        } elseif ('view_count' === $listing_popular_by) {
                            $meta_queries['views'] = array(
                                'key' => '_atbdp_post_views_count',
                                'value' => $view_to_popular,
                                'type' => 'NUMERIC',
                                'compare' => '>=',
                            );
                            $args['orderby'] = array(
                                'views' => 'DESC',
                            );
                        } else {
                            $meta_queries['views'] = array(
                                'key' => '_atbdp_post_views_count',
                                'value' => (int)$view_to_popular,
                                'type' => 'NUMERIC',
                                'compare' => '>=',
                            );
                            $args['orderby'] = array(
                                'views' => 'DESC',
                            );
                            if ($listings->have_posts()) {
                                while ($listings->have_posts()) {
                                    $listings->the_post();
                                    $listing_id = get_the_ID();
                                    $average = ATBDP()->review->get_average($listing_id);
                                    if ($average_review_for_popular <= $average) {
                                        $rated[] = get_the_ID();
                                    }

                                }
                                $rating_id = array(
                                    'post__in' => !empty($rated) ? $rated : array()
                                );
                                $args = array_merge($args, $rating_id);
                            }
                        }
                    }
                    break;
                    case 'rand' :
                    if ($has_featured) {
                        $args['meta_key'] = '_featured';
                        $args['orderby'] = 'meta_value_num rand';
                    } else {
                        $args['orderby'] = 'rand';
                    };
                    break;
                }
                $meta_queries = apply_filters('atbdp_single_location_meta_queries', $meta_queries);
                $count_meta_queries = count($meta_queries);
                if ($count_meta_queries) {
                    $args['meta_query'] = ($count_meta_queries > 1) ? array_merge(array('relation' => 'AND'), $meta_queries) : $meta_queries;
                }

                $all_listings = new WP_Query($args);
                if ('yes' == $show_pagination) {
                    $listing_count = '<span>' . $all_listings->found_posts . '</span>';
                } else {
                    $listing_count = '<span>' . count($all_listings->posts) . '</span>';
                }
                $display_header = !empty($display_header) ? $display_header : '';
                $header_title = !empty($header_title) ? $listing_count . ' ' . $header_title : '';
                $listing_filters_button = get_directorist_option('listing_filters_button', 1);
                $filters = get_directorist_option('listings_filter_button_text', __('Filters', 'directorist'));
                $text_placeholder = get_directorist_option('listings_search_text_placeholder', __('What are you looking for?', 'directorist'));
                $category_placeholder = get_directorist_option('listings_category_placeholder', __('Select a category', 'directorist'));
                $location_placeholder = get_directorist_option('listings_location_placeholder', __('Select a location', 'directorist'));
                $sort_by_text = get_directorist_option('sort_by_text', __('Sort By', 'directorist'));
                $view_as_text = get_directorist_option('view_as_text', __('View As', 'directorist'));
                //  $data_for_template            = compact('all_listings', 'all_listing_title', 'paged', 'paginate');
                $search_more_filters_fields = get_directorist_option('listing_filters_fields', array('search_text', 'search_category', 'search_location', 'search_price', 'search_price_range', 'search_rating', 'search_tag', 'search_custom_fields', 'radius_search'));
                $filters_button = get_directorist_option('listings_filters_button', array('reset_button', 'apply_button'));
                $reset_filters_text = get_directorist_option('listings_reset_text', __('Reset Filters', 'directorist'));
                $apply_filters_text = get_directorist_option('listings_apply_text', __('Apply Filters', 'directorist'));
                // $data_for_template = compact('all_listings', 'all_listing_title', 'paged', 'paginate');
                $view_as_items = get_directorist_option('listings_view_as_items', array('listings_grid', 'listings_list', 'listings_map'));
                $sort_by_items = get_directorist_option('listings_sort_by_items', array('a_z', 'z_a', 'latest', 'oldest', 'popular', 'price_low_high', 'price_high_low', 'random'));
                $listing_header_container_fluid = is_directoria_active() ? 'container' : 'container-fluid';
                $header_container_fluid = apply_filters('atbdp_single_loc_header_container_fluid', $listing_header_container_fluid);
                $listing_grid_container_fluid = is_directoria_active() ? 'container' : 'container-fluid';
                $grid_container_fluid = apply_filters('atbdp_single_loc_grid_container_fluid', $listing_grid_container_fluid);
                $listing_location_address = get_directorist_option('listing_location_address', 'map_api');
                ob_start();
                $include = apply_filters('include_style_settings', true);
                if ($include) {
                    include ATBDP_DIR . 'public/assets/css/style.php';
                }
                if (!empty($redirect_page_url)) {
                    $redirect = '<script>window.location="' . esc_url($redirect_page_url) . '"</script>';
                    return $redirect;
                }

                // Base Template Data
                !empty($args['data']) ? extract($args['data']) : array(); // data array contains all required var.
                $all_listings = !empty($all_listings) ? $all_listings : new WP_Query;
                $display_sortby_dropdown = get_directorist_option('display_sort_by', 1);
                $display_viewas_dropdown = get_directorist_option('display_view_as', 1);
                $display_image = !empty($display_image) ? $display_image : '';
                $show_pagination = !empty($show_pagination) ? $show_pagination : '';
                $paged = !empty($paged) ? $paged : '';

                $is_disable_price = get_directorist_option('disable_list_price');
                $view_as = get_directorist_option('grid_view_as', 'normal_grid');
                $column_width = 100 / (int)$columns . '%';

                if (is_rtl()) {
                    wp_enqueue_style('atbdp-search-style-rtl', ATBDP_PUBLIC_ASSETS . 'css/search-style-rtl.css');
                } else {
                    wp_enqueue_style('atbdp-search-style', ATBDP_PUBLIC_ASSETS . 'css/search-style.css');
                }

                // Header Template Data
                $address_label               = get_directorist_option('address_label',__('Address','directorist'));
                $fax_label                   = get_directorist_option('fax_label',__('Fax','directorist'));
                $email_label                 = get_directorist_option('email_label',__('Email','directorist'));
                $website_label               = get_directorist_option('website_label',__('Website','directorist'));
                $tag_label                   = get_directorist_option('tag_label',__('Tag','directorist'));
                $zip_label                   = get_directorist_option('zip_label',__('Zip','directorist'));
                $listing_filters_icon        = get_directorist_option('listing_filters_icon',1);
                $query_args = array(
                    'parent'             => 0,
                    'term_id'            => 0,
                    'hide_empty'         => 0,
                    'orderby'            => 'name',
                    'order'              => 'asc',
                    'show_count'         => 0,
                    'single_only'        => 0,
                    'pad_counts'         => true,
                    'immediate_category' => 0,
                    'active_term_id'     => 0,
                    'ancestors'          => array()
                );
                $categories_fields = search_category_location_filter( $query_args, ATBDP_CATEGORY );
                $locations_fields  = search_category_location_filter( $query_args, ATBDP_LOCATION );
                $currency = get_directorist_option('g_currency', 'USD');
                $c_symbol = atbdp_currency_symbol($currency);

                // If listings_with_map is active
                if ( 'listings_with_map' == $view ) {
                    $path = atbdp_get_theme_file("/directorist/shortcodes/listings/extension/listing-with-map/map-view.php");

                    if ( $path ) {
                        include $path;
                    } else {
                        include BDM_TEMPLATES_DIR . '/map-view.php';
                    }
                    return ob_get_clean();
                }

                // Default Template
                $path = atbdp_get_theme_file("/directorist/shortcodes/listings-archive/listings-{$view}.php");
                if ( $path ) {
                    include $path;
                } else {
                    include ATBDP_TEMPLATES_DIR . "public-templates/shortcodes/listings-archive/listings-$view.php";
                }

                return ob_get_clean();

            }
            return '<span>' . __('No Results Found.', 'directorist') . '</span>';
        }

        public function atbdp_tag($atts)
        {
            wp_enqueue_script('adminmainassets');
            wp_enqueue_script('atbdp-search-listing', ATBDP_PUBLIC_ASSETS . 'js/search-form-listing.js');
            wp_localize_script('atbdp-search-listing', 'atbdp_search', array(
                'ajaxnonce' => wp_create_nonce('bdas_ajax_nonce'),
                'ajax_url' => admin_url('admin-ajax.php'),
                'added_favourite' => __('Added to favorite', 'directorist'),
                'please_login' => __('Please login first', 'directorist')
            ));
            wp_enqueue_script('atbdp-range-slider');
            $radius_search_unit            = get_directorist_option('radius_search_unit', 'miles');
            if(!empty($radius_search_unit) && 'kilometers' == $radius_search_unit) {
                $miles = __(' Kilometers', 'directorist');
            }else{
                $miles = __(' Miles', 'directorist');
            }
            $default_radius_distance = get_directorist_option('listing_default_radius_distance', 0);
            wp_localize_script( 'atbdp-range-slider', 'atbdp_range_slider', array(
                'Miles'     =>  $miles,
                'default_val'   =>  $default_radius_distance
            ) );
            $term_slug = get_query_var('atbdp_tag');

            $term = '';

            if ('' == $term_slug && !empty($atts['id'])) {
                $term = get_term_by('id', (int)$atts['id'], ATBDP_TAGS);
                $term_slug = $term->slug;
            } elseif ('' != $term_slug) {
                $term = get_term_by('slug', $term_slug, ATBDP_TAGS);
            }

            if ('' != $term_slug) {
                $listing_orderby = get_directorist_option('order_listing_by');
                $listing_view = get_directorist_option('default_listing_view');
                $listing_order = get_directorist_option('sort_listing_by');
                $listing_grid_columns = get_directorist_option('all_listing_columns', 3);
                $display_listings_header = get_directorist_option('display_listings_header', 1);
                $listings_header_title = get_directorist_option('all_listing_header_title', __('Items Found', 'directorist'));
                $listings_header_sub_title = get_directorist_option('listings_header_sub_title', __('Total Listing Found: ', 'directorist'));
                $filters_display = get_directorist_option('listings_display_filter', 'sliding');
                $pagination = get_directorist_option('paginate_all_listings');
                $listings_map_height = get_directorist_option('listings_map_height', 350);
                $params = apply_filters('atbdp_single_tag_param', array(
                    'view' => !empty($listing_view) ? $listing_view : 'grid',
                    '_featured' => 1,
                    'filterby' => '',
                    'orderby' => !empty($listing_orderby) ? $listing_orderby : 'date',
                    'order' => !empty($listing_order) ? $listing_order : 'asc',
                    'listings_per_page' => (int)get_directorist_option('all_listing_page_items', 6),
                    'pagination' => 1,
                    'show_pagination' => !empty($pagination) ? 'yes' : '',
                    'header' => !empty($display_listings_header) ? 'yes' : '',
                    'header_title' => !empty($listings_header_title) ? $listings_header_title : '',
                    'header_sub_title' => !empty($listings_header_sub_title) ? $listings_header_sub_title : '',
                    'columns' => !empty($listing_grid_columns) ? $listing_grid_columns : 3,
                    'map_height' => !empty($listings_map_height) ? $listings_map_height : 350,
                    'logged_in_user_only' => '',
                    'redirect_page_url' => ''
                ));
                $atts = shortcode_atts($params, $atts);
                $logged_in_user_only = !empty($atts['logged_in_user_only']) ? $atts['logged_in_user_only'] : '';
                $redirect_page_url = !empty($atts['redirect_page_url']) ? $atts['redirect_page_url'] : '';
                
                if ( 'yes' === $logged_in_user_only && ! atbdp_logged_in_user() ) {
                    return $this->guard( ['type' => 'auth'] );
                }
                
                $columns = !empty($atts['columns']) ? $atts['columns'] : 3;
                $display_header = !empty($atts['header']) ? $atts['header'] : '';
                $header_title = !empty($atts['header_title']) ? $atts['header_title'] : '';
                $header_sub_title = !empty($atts['header_sub_title']) ? $atts['header_sub_title'] : '';
                $show_pagination = !empty($atts['show_pagination']) ? $atts['show_pagination'] : '';
                $map_height = !empty($atts['map_height']) ? $atts['map_height'] : '';
                //for pagination
                $paged = atbdp_get_paged_num();

                $has_featured = get_directorist_option('enable_featured_listing');
                if ($has_featured || is_fee_manager_active()) {
                    $has_featured = $atts['_featured'];
                }
                $current_order = atbdp_get_listings_current_order($atts['orderby'] . '-' . $atts['order']);
                $view = atbdp_get_listings_current_view_name($atts['view']);
                $args = array(
                    'post_type' => ATBDP_POST_TYPE,
                    'post_status' => 'publish',
                    'posts_per_page' => (int)$atts['listings_per_page']
                );
                if ('yes' == $show_pagination) {
                    $args['paged'] = $paged;
                } else {
                    $args['no_found_rows'] = true;
                }
                $tax_queries[] = array(
                    'taxonomy' => ATBDP_TAGS,
                    'field' => 'slug',
                    'terms' => $term_slug,
                    'include_children' => true,
                );

                $args['tax_query'] = $tax_queries;

                $meta_queries = array();
                $meta_queries['expired'] = array(
                    'relation' => 'OR',
                    array(
                        'key' => '_expiry_date',
                        'value' => current_time('mysql'),
                        'compare' => '>', // eg. expire date 6 <= current date 7 will return the post
                        'type' => 'DATETIME'
                    ),
                    array(
                        'key' => '_never_expire',
                        'value' => 1,
                    )

                );
                $args['expired'] = $meta_queries;
                if ($has_featured) {

                    if ('_featured' == $atts['filterby']) {
                        $meta_queries['_featured'] = array(
                            'key' => '_featured',
                            'value' => 1,
                            'compare' => '='
                        );

                    } else {
                        $meta_queries['_featured'] = array(
                            'key' => '_featured',
                            'type' => 'NUMERIC',
                            'compare' => 'EXISTS',
                        );
                    }
                }

                switch ($current_order) {
                    case 'title-asc' :
                    if ($has_featured) {
                        $args['meta_key'] = '_featured';
                        $args['orderby'] = array(
                            'meta_value_num' => 'DESC',
                            'title' => 'ASC',
                        );
                    } else {
                        $args['orderby'] = 'title';
                        $args['order'] = 'ASC';
                    };
                    break;
                    case 'title-desc' :
                    if ($has_featured) {
                        $args['meta_key'] = '_featured';
                        $args['orderby'] = array(
                            'meta_value_num' => 'DESC',
                            'title' => 'DESC',
                        );
                    } else {
                        $args['orderby'] = 'title';
                        $args['order'] = 'DESC';
                    };
                    break;
                    case 'date-asc' :
                    if ($has_featured) {
                        $args['meta_key'] = '_featured';
                        $args['orderby'] = array(
                            'meta_value_num' => 'DESC',
                            'date' => 'ASC',
                        );
                    } else {
                        $args['orderby'] = 'date';
                        $args['order'] = 'ASC';
                    };
                    break;
                    case 'date-desc' :
                    if ($has_featured) {
                        $args['meta_key'] = '_featured';
                        $args['orderby'] = array(
                            'meta_value_num' => 'DESC',
                            'date' => 'DESC',
                        );
                    } else {
                        $args['orderby'] = 'date';
                        $args['order'] = 'DESC';
                    };
                    break;
                    case 'price-asc' :
                    if ($has_featured) {
                        $meta_queries['price'] = array(
                            'key' => '_price',
                            'type' => 'NUMERIC',
                            'compare' => 'EXISTS',
                        );

                        $args['orderby'] = array(
                            '_featured' => 'DESC',
                            'price' => 'ASC',
                        );
                    } else {
                        $args['meta_key'] = '_price';
                        $args['orderby'] = 'meta_value_num';
                        $args['order'] = 'ASC';
                    };
                    break;
                    case 'price-desc' :
                    if ($has_featured) {
                        $meta_queries['price'] = array(
                            'key' => '_price',
                            'type' => 'NUMERIC',
                            'compare' => 'EXISTS',
                        );

                        $args['orderby'] = array(
                            '_featured' => 'DESC',
                            'price' => 'DESC',
                        );
                    } else {
                        $args['meta_key'] = '_price';
                        $args['orderby'] = 'meta_value_num';
                        $args['order'] = 'DESC';
                    };
                    break;
                    case 'views-desc' :
                    $listings = get_atbdp_listings_ids();
                    $rated = array();
                    $listing_popular_by = get_directorist_option('listing_popular_by');
                    $average_review_for_popular = get_directorist_option('average_review_for_popular', 4);
                    $view_to_popular = get_directorist_option('views_for_popular');
                    if ($has_featured) {
                        if ('average_rating' === $listing_popular_by) {
                            if ($listings->have_posts()) {
                                while ($listings->have_posts()) {
                                    $listings->the_post();
                                    $listing_id = get_the_ID();
                                    $average = ATBDP()->review->get_average($listing_id);
                                    if ($average_review_for_popular <= $average) {
                                        $rated[] = get_the_ID();
                                    }
                                }
                                $rating_id = array(
                                    'post__in' => !empty($rated) ? $rated : array()
                                );
                                $args = array_merge($args, $rating_id);
                            }
                        } elseif ('view_count' === $listing_popular_by) {
                            $meta_queries['views'] = array(
                                'key' => '_atbdp_post_views_count',
                                'value' => $view_to_popular,
                                'type' => 'NUMERIC',
                                'compare' => '>=',
                            );
                            $args['orderby'] = array(
                                '_featured' => 'DESC',
                                'views' => 'DESC',
                            );
                        } else {
                            $meta_queries['views'] = array(
                                'key' => '_atbdp_post_views_count',
                                'value' => $view_to_popular,
                                'type' => 'NUMERIC',
                                'compare' => '>=',
                            );
                            $args['orderby'] = array(
                                '_featured' => 'DESC',
                                'views' => 'DESC',
                            );
                            if ($listings->have_posts()) {
                                while ($listings->have_posts()) {
                                    $listings->the_post();
                                    $listing_id = get_the_ID();
                                    $average = ATBDP()->review->get_average($listing_id);
                                    if ($average_review_for_popular <= $average) {
                                        $rated[] = get_the_ID();
                                    }
                                }
                                $rating_id = array(
                                    'post__in' => !empty($rated) ? $rated : array()
                                );
                                $args = array_merge($args, $rating_id);
                            }
                        }

                    } else {
                        if ('average_rating' === $listing_popular_by) {
                            if ($listings->have_posts()) {
                                while ($listings->have_posts()) {
                                    $listings->the_post();
                                    $listing_id = get_the_ID();
                                    $average = ATBDP()->review->get_average($listing_id);
                                    if ($average_review_for_popular <= $average) {
                                        $rated[] = get_the_ID();
                                    }

                                }
                                $rating_id = array(
                                    'post__in' => !empty($rated) ? $rated : array()
                                );
                                $args = array_merge($args, $rating_id);
                            }
                        } elseif ('view_count' === $listing_popular_by) {
                            $meta_queries['views'] = array(
                                'key' => '_atbdp_post_views_count',
                                'value' => $view_to_popular,
                                'type' => 'NUMERIC',
                                'compare' => '>=',
                            );
                            $args['orderby'] = array(
                                'views' => 'DESC',
                            );
                        } else {
                            $meta_queries['views'] = array(
                                'key' => '_atbdp_post_views_count',
                                'value' => (int)$view_to_popular,
                                'type' => 'NUMERIC',
                                'compare' => '>=',
                            );
                            $args['orderby'] = array(
                                'views' => 'DESC',
                            );
                            if ($listings->have_posts()) {
                                while ($listings->have_posts()) {
                                    $listings->the_post();
                                    $listing_id = get_the_ID();
                                    $average = ATBDP()->review->get_average($listing_id);
                                    if ($average_review_for_popular <= $average) {
                                        $rated[] = get_the_ID();
                                    }

                                }
                                $rating_id = array(
                                    'post__in' => !empty($rated) ? $rated : array()
                                );
                                $args = array_merge($args, $rating_id);
                            }
                        }
                    }
                    break;
                    case 'rand' :
                    if ($has_featured) {
                        $args['meta_key'] = '_featured';
                        $args['orderby'] = 'meta_value_num rand';
                    } else {
                        $args['orderby'] = 'rand';
                    };
                    break;
                }
                $meta_queries = apply_filters('atbdp_single_tag_meta_queries', $meta_queries);
                $count_meta_queries = count($meta_queries);
                if ($count_meta_queries) {
                    $args['meta_query'] = ($count_meta_queries > 1) ? array_merge(array('relation' => 'AND'), $meta_queries) : $meta_queries;
                }

                $all_listings = new WP_Query($args);
                if ('yes' == $show_pagination) {
                    $listing_count = '<span>' . $all_listings->found_posts . '</span>';
                } else {
                    $listing_count = '<span>' . count($all_listings->posts) . '</span>';
                }
                $display_header = !empty($display_header) ? $display_header : '';
                $header_title = !empty($header_title) ? $listing_count . ' ' . $header_title : '';
                $listing_filters_button = get_directorist_option('listing_filters_button', 1);
                $filters = get_directorist_option('listings_filter_button_text', __('Filters', 'directorist'));
                $text_placeholder = get_directorist_option('listings_search_text_placeholder', __('What are you looking for?', 'directorist'));
                $category_placeholder = get_directorist_option('listings_category_placeholder', __('Select a category', 'directorist'));
                $location_placeholder = get_directorist_option('listings_location_placeholder', __('Select a location', 'directorist'));
                //$data_for_template            = compact('all_listings', 'all_listing_title', 'paged', 'paginate');
                $search_more_filters_fields = get_directorist_option('listing_filters_fields', array('search_text', 'search_category', 'search_location', 'search_price', 'search_price_range', 'search_rating', 'search_tag', 'search_custom_fields', 'radius_search'));
                $filters_button = get_directorist_option('listings_filters_button', array('reset_button', 'apply_button'));
                $reset_filters_text = get_directorist_option('listings_reset_text', __('Reset Filters', 'directorist'));
                $apply_filters_text = get_directorist_option('listings_apply_text', __('Apply Filters', 'directorist'));
                $sort_by_text = get_directorist_option('sort_by_text', __('Sort By', 'directorist'));
                $view_as_text = get_directorist_option('view_as_text', __('View As', 'directorist'));
                //$data_for_template            = compact('all_listings', 'all_listing_title', 'paged', 'paginate');
                $view_as_items = get_directorist_option('listings_view_as_items', array('listings_grid', 'listings_list', 'listings_map'));
                $sort_by_items = get_directorist_option('listings_sort_by_items', array('a_z', 'z_a', 'latest', 'oldest', 'popular', 'price_low_high', 'price_high_low', 'random'));
                $listing_header_container_fluid = is_directoria_active() ? 'container' : 'container-fluid';
                $header_container_fluid = apply_filters('atbdp_single_tag_header_container_fluid', $listing_header_container_fluid);
                $listing_grid_container_fluid = is_directoria_active() ? 'container' : 'container-fluid';
                $grid_container_fluid = apply_filters('atbdp_single_tag_grid_container_fluid', $listing_grid_container_fluid);
                $listing_location_address = get_directorist_option('listing_location_address', 'map_api');
                ob_start();
                $include = apply_filters('include_style_settings', true);
                if ($include) {
                    include ATBDP_DIR . 'public/assets/css/style.php';
                }
                if ( !empty($redirect_page_url) ) {
                    $redirect = '<script>window.location="' . esc_url($redirect_page_url) . '"</script>';
                    return $redirect;
                }
                
                // Base Template Data
                !empty($args['data']) ? extract($args['data']) : array(); // data array contains all required var.
                $all_listings = !empty($all_listings) ? $all_listings : new WP_Query;
                $display_sortby_dropdown = get_directorist_option('display_sort_by', 1);
                $display_viewas_dropdown = get_directorist_option('display_view_as', 1);
                $display_image = !empty($display_image) ? $display_image : '';
                $show_pagination = !empty($show_pagination) ? $show_pagination : '';
                $paged = !empty($paged) ? $paged : '';

                $is_disable_price = get_directorist_option('disable_list_price');
                $view_as = get_directorist_option('grid_view_as', 'normal_grid');
                $column_width = 100 / (int)$columns . '%';

                if (is_rtl()) {
                    wp_enqueue_style('atbdp-search-style-rtl', ATBDP_PUBLIC_ASSETS . 'css/search-style-rtl.css');
                } else {
                    wp_enqueue_style('atbdp-search-style', ATBDP_PUBLIC_ASSETS . 'css/search-style.css');
                }

                // Header Template Data
                $address_label               = get_directorist_option('address_label',__('Address','directorist'));
                $fax_label                   = get_directorist_option('fax_label',__('Fax','directorist'));
                $email_label                 = get_directorist_option('email_label',__('Email','directorist'));
                $website_label               = get_directorist_option('website_label',__('Website','directorist'));
                $tag_label                   = get_directorist_option('tag_label',__('Tag','directorist'));
                $zip_label                   = get_directorist_option('zip_label',__('Zip','directorist'));
                $listing_filters_icon        = get_directorist_option('listing_filters_icon',1);
                $query_args = array(
                    'parent'             => 0,
                    'term_id'            => 0,
                    'hide_empty'         => 0,
                    'orderby'            => 'name',
                    'order'              => 'asc',
                    'show_count'         => 0,
                    'single_only'        => 0,
                    'pad_counts'         => true,
                    'immediate_category' => 0,
                    'active_term_id'     => 0,
                    'ancestors'          => array()
                );
                $categories_fields = search_category_location_filter( $query_args, ATBDP_CATEGORY );
                $locations_fields  = search_category_location_filter( $query_args, ATBDP_LOCATION );
                $currency = get_directorist_option('g_currency', 'USD');
                $c_symbol = atbdp_currency_symbol($currency);

                // If listings_with_map is active
                if ( 'listings_with_map' == $view ) {
                    $path = atbdp_get_theme_file("/directorist/shortcodes/listings/extension/listing-with-map/map-view.php");
                    
                    if ( $path ) { include $path; } 
                    else { include BDM_TEMPLATES_DIR . '/map-view.php'; }

                    return ob_get_clean();
                }

                // Default Template
                $path = atbdp_get_theme_file("/directorist/shortcodes/listings-archive/listings-{$view}.php");
                if ( $path ) { include $path;
                } else {
                    include ATBDP_TEMPLATES_DIR . "public-templates/shortcodes/listings-archive/listings-$view.php";
                }

                return ob_get_clean();

            }
            return '<span>' . __('No Results Found.', 'directorist') . '</span>';
        }

        public function search_listing($atts, $content = null)
        {
            $search_title = get_directorist_option('search_title', __("Search here", 'directorist'));
            $search_subtitle = get_directorist_option('search_subtitle', __("Find the best match of your interest
                ", 'directorist'));
            $search_fields = get_directorist_option('search_tsc_fields', array('search_text', 'search_category', 'search_location'));
            $search_more_filter = get_directorist_option('search_more_filter', 1);
            $search_button = get_directorist_option('search_button', 1);
            $search_more_filters_fields = get_directorist_option('search_more_filters_fields', array('search_price', 'search_price_range', 'search_rating', 'search_tag', 'search_custom_fields', 'radius_search'));
            $search_filters = get_directorist_option('search_filters', array('search_reset_filters', 'search_apply_filters'));
            $search_more_filters = get_directorist_option('search_more_filters', __('More Filters', 'directorist'));
            $search_listing_text = get_directorist_option('search_listing_text', __('Search Listing', 'directorist'));
            $search_reset_text = get_directorist_option('search_reset_text', __('Reset Filters', 'directorist'));
            $search_apply_text = get_directorist_option('search_apply_filter', __('Apply Filters', 'directorist'));
            $search_location_address = get_directorist_option('search_location_address', 'address');
            $filters_display = get_directorist_option('home_display_filter', 'overlapping');
            $atts = shortcode_atts(array(
                'show_title_subtitle' => 'yes',
                'search_bar_title' => !empty($search_title) ? $search_title : '',
                'search_bar_sub_title' => !empty($search_subtitle) ? $search_subtitle : '',
                'text_field' => in_array('search_text', $search_fields) ? 'yes' : '',
                'category_field' => in_array('search_category', $search_fields) ? 'yes' : '',
                'location_field' => in_array('search_location', $search_fields) ? 'yes' : '',
                'search_button' => !empty($search_button) ? 'yes' : '',
                'search_button_text' => !empty($search_listing_text) ? $search_listing_text : 'Search Listing',
                'more_filters_button' => !empty($search_more_filter) ? 'yes' : '',
                'more_filters_text' => !empty($search_more_filters) ? $search_more_filters : 'More Filters',
                'price_min_max_field' => in_array('search_price', $search_more_filters_fields) ? 'yes' : '',
                'price_range_field' => in_array('search_price_range', $search_more_filters_fields) ? 'yes' : '',
                'rating_field' => in_array('search_rating', $search_more_filters_fields) ? 'yes' : '',
                'tag_field' => in_array('search_tag', $search_more_filters_fields) ? 'yes' : '',
                'open_now_field' => in_array('search_open_now', $search_more_filters_fields) ? 'yes' : '',
                'custom_fields' => in_array('search_custom_fields', $search_more_filters_fields) ? 'yes' : '',
                'website_field' => in_array('search_website', $search_more_filters_fields) ? 'yes' : '',
                'email_field' => in_array('search_email', $search_more_filters_fields) ? 'yes' : '',
                'phone_field' => in_array('search_phone', $search_more_filters_fields) ? 'yes' : '',
                'fax' => in_array('search_fax', $search_more_filters_fields) ? 'yes' : '',
                'address_field' => in_array('search_address', $search_more_filters_fields) ? 'yes' : '',
                'zip_code_field' => in_array('search_zip_code', $search_more_filters_fields) ? 'yes' : '',
                'radius_search' => in_array('radius_search', $search_more_filters_fields) ? 'yes' : '',
                'reset_filters_button' => in_array('search_reset_filters', $search_filters) ? 'yes' : '',
                'apply_filters_button' => in_array('search_apply_filters', $search_filters) ? 'yes' : '',
                'reset_filters_text' => !empty($search_reset_text) ? $search_reset_text : 'Reset Filters',
                'apply_filters_text' => !empty($search_apply_text) ? $search_apply_text : 'Apply Filters',
                'logged_in_user_only' => '',
                'redirect_page_url' => '',
                'more_filters_display' => !empty($filters_display) ? $filters_display : 'overlapping'
            ), $atts);

            $search_bar_title = (!empty($atts['search_bar_title'])) ? $atts['search_bar_title'] : '';
            $search_bar_sub_title = (!empty($atts['search_bar_sub_title'])) ? $atts['search_bar_sub_title'] : '';
            $text_field = (!empty($atts['text_field']) && 'yes' == $atts['text_field']) ? $atts['text_field'] : '';
            $category_field = (!empty($atts['category_field']) && 'yes' == $atts['category_field']) ? $atts['category_field'] : '';
            $location_field = (!empty($atts['location_field']) && 'yes' == $atts['location_field']) ? $atts['location_field'] : '';
            $search_button = (!empty($atts['search_button']) && 'yes' == $atts['search_button']) ? $atts['search_button'] : '';
            $search_button_text = (!empty($atts['search_button_text'])) ? $atts['search_button_text'] : '';
            $more_filters_button = (!empty($atts['more_filters_button']) && 'yes' == $atts['more_filters_button']) ? $atts['more_filters_button'] : '';
            $more_filters_text = (!empty($atts['more_filters_text'])) ? $atts['more_filters_text'] : '';
            $price_min_max_field = (!empty($atts['price_min_max_field']) && 'yes' == $atts['price_min_max_field']) ? $atts['price_min_max_field'] : '';
            $price_range_field = (!empty($atts['price_range_field']) && 'yes' == $atts['price_range_field']) ? $atts['price_range_field'] : '';
            $rating_field = (!empty($atts['rating_field']) && 'yes' == $atts['rating_field']) ? $atts['rating_field'] : '';
            $tag_field = (!empty($atts['tag_field']) && 'yes' == $atts['tag_field']) ? $atts['tag_field'] : '';
            $open_now_field = (!empty($atts['open_now_field']) && 'yes' == $atts['open_now_field']) ? $atts['open_now_field'] : '';
            $custom_fields = (!empty($atts['custom_fields']) && 'yes' == $atts['custom_fields']) ? $atts['custom_fields'] : '';
            $website_field = (!empty($atts['website_field']) && 'yes' == $atts['website_field']) ? $atts['website_field'] : '';
            $email_field = (!empty($atts['email_field']) && 'yes' == $atts['email_field']) ? $atts['email_field'] : '';
            $phone_field = (!empty($atts['phone_field']) && 'yes' == $atts['phone_field']) ? $atts['phone_field'] : '';
            $phone_field2 = (!empty($atts['phone_field2']) && 'yes' == $atts['phone_field2']) ? $atts['phone_field2'] : '';
            $fax = (!empty($atts['fax']) && 'yes' == $atts['fax']) ? $atts['fax'] : '';
            $address_field = (!empty($atts['address_field']) && 'yes' == $atts['address_field']) ? $atts['address_field'] : '';
            $zip_code_field = (!empty($atts['zip_code_field']) && 'yes' == $atts['zip_code_field']) ? $atts['zip_code_field'] : '';
            $radius_search = (!empty($atts['radius_search']) && 'yes' == $atts['radius_search']) ? $atts['radius_search'] : '';
            $reset_filters_button = (!empty($atts['reset_filters_button']) && 'yes' == $atts['reset_filters_button']) ? $atts['reset_filters_button'] : '';
            $apply_filters_button = (!empty($atts['apply_filters_button']) && 'yes' == $atts['apply_filters_button']) ? $atts['apply_filters_button'] : '';
            $reset_filters_text = (!empty($atts['reset_filters_text'])) ? $atts['reset_filters_text'] : '';
            $apply_filters_text = (!empty($atts['apply_filters_text'])) ? $atts['apply_filters_text'] : '';
            $show_title_subtitle = ('yes' === $atts['show_title_subtitle']) ? $atts['show_title_subtitle'] : '';
            $logged_in_user_only = !empty($atts['logged_in_user_only']) ? $atts['logged_in_user_only'] : '';
            
            if ( 'yes' === $logged_in_user_only && ! atbdp_logged_in_user() ) {
                return $this->guard( ['type' => 'auth'] );
            }

            $redirect_page_url = !empty($atts['redirect_page_url']) ? $atts['redirect_page_url'] : '';
            $filters_display = !empty($atts['more_filters_display']) ? $atts['more_filters_display'] : 'overlapping';
            ob_start();
            $include = apply_filters('include_style_settings', true);
            if ($include) {
                include ATBDP_DIR . 'public/assets/css/style.php';
            }
            if (!empty($redirect_page_url)) {
                $redirect = '<script>window.location="' . esc_url($redirect_page_url) . '"</script>';
                return $redirect;
            }

            // Template Data
            $categories = get_terms(ATBDP_CATEGORY, array('hide_empty' => 0));
            $locations = get_terms(ATBDP_LOCATION, array('hide_empty' => 0));
            $search_placeholder = get_directorist_option('search_placeholder', __('What are you looking for?', 'directorist'));
            $search_category_placeholder = get_directorist_option('search_category_placeholder', __('Select a category', 'directorist'));
            $search_location_placeholder = get_directorist_option('search_location_placeholder', __('location', 'directorist'));
            $show_popular_category = get_directorist_option('show_popular_category', 1);
            $show_connector = get_directorist_option('show_connector', 1);
            $search_border = get_directorist_option('search_border', 1);
            $display_more_filter_icon = get_directorist_option('search_more_filter_icon', 1);
            $search_button_icon = get_directorist_option('search_button_icon', 1);

            $connectors_title = get_directorist_option('connectors_title', __('Or', 'directorist'));
            $popular_cat_title = get_directorist_option('popular_cat_title', __('Browse by popular categories', 'directorist'));
            $popular_cat_num = get_directorist_option('popular_cat_num', 10);
            $require_text = get_directorist_option('require_search_text');
            $require_cat = get_directorist_option('require_search_category');
            $require_loc = get_directorist_option('require_search_location');
            $require_text = !empty($require_text) ? "required" : "";
            $require_cat = !empty($require_cat) ? "required" : "";
            $require_loc = !empty($require_loc) ? "required" : "";

            $default = get_template_directory_uri() . '/images/home_page_bg.jpg';
            $theme_home_bg_image = get_theme_mod('directoria_home_bg');
            $search_home_bg = get_directorist_option('search_home_bg');
            $display_more_filter_search = get_directorist_option('search_more_filter', 1);
            $search_filters = get_directorist_option('search_filters', array('reset_button', 'apply_button'));
            $search_more_filters_fields = get_directorist_option('search_more_filters_fields', array('search_price', 'search_price_range', 'search_rating', 'search_tag', 'search_custom_fields', 'radius_search'));
            $tag_label = get_directorist_option('tag_label', __('Tag', 'directorist'));
            $address_label = get_directorist_option('address_label', __('Address', 'directorist'));
            $fax_label = get_directorist_option('fax_label', __('Fax', 'directorist'));
            $email_label = get_directorist_option('email_label', __('Email', 'directorist'));
            $website_label = get_directorist_option('website_label', __('Website', 'directorist'));
            $zip_label = get_directorist_option('zip_label', __('Zip', 'directorist'));
            $currency = get_directorist_option('g_currency', 'USD');
            $c_symbol = atbdp_currency_symbol($currency);
            $front_bg_image = (!empty($theme_home_bg_image)) ? $theme_home_bg_image : $search_home_bg;
            if (is_rtl()) {
                wp_enqueue_style('atbdp-search-style-rtl', ATBDP_PUBLIC_ASSETS . 'css/search-style-rtl.css');
            } else {
                wp_enqueue_style('atbdp-search-style', ATBDP_PUBLIC_ASSETS . 'css/search-style.css');
            }
            wp_enqueue_script('atbdp-search-listing', ATBDP_PUBLIC_ASSETS . 'js/search-form-listing.js');
            wp_localize_script('atbdp-search-listing', 'atbdp_search', array(
                'ajaxnonce' => wp_create_nonce('bdas_ajax_nonce'),
                'ajax_url' => admin_url('admin-ajax.php'),
            ));
            $container_fluid = is_directoria_active() ? 'container' : 'container-fluid';
            $search_home_bg_image = !empty($front_bg_image) ? $front_bg_image : $default;
            $query_args = array(
                'parent' => 0,
                'term_id' => 0,
                'hide_empty' => 0,
                'orderby' => 'name',
                'order' => 'asc',
                'show_count' => 0,
                'single_only' => 0,
                'pad_counts' => true,
                'immediate_category' => 0,
                'active_term_id' => 0,
                'ancestors' => array()
            );
            $categories_fields = search_category_location_filter($query_args, ATBDP_CATEGORY);
            $locations_fields = search_category_location_filter($query_args, ATBDP_LOCATION);

            // Default Template
            $path = atbdp_get_theme_file("/directorist/shortcodes/listings/search-listing.php");
            if ( $path ) {
                include $path;
            } else {
                include ATBDP_TEMPLATES_DIR . "public-templates/shortcodes/search-listing.php";
            }

            ATBDP()->enquirer->search_listing_scripts_styles();
            return ob_get_clean();
        }

        public function author_profile($atts)
        {
            $author = new Directorist_Listing_Author();
            return $author->render_shortcode_author_profile($atts);
        }

        public function add_listing($atts)
        {
            ob_start();
            $include = apply_filters('include_style_settings', true);
            if ($include) {
                include ATBDP_DIR . 'public/assets/css/style.php';
            }
            wp_enqueue_script('adminmainassets');
            $guest_submission = get_directorist_option('guest_listings', 0);

            if ( false === $guest_submission && ! atbdp_logged_in_user() ) {
                return $this->guard( ['type' => 'auth'] );
            }

            global $wp;
            global $pagenow;
            $current_url = home_url(add_query_arg(array(), $wp->request));

            $p_id = get_query_var('atbdp_listing_id', 0);
            if (!empty($p_id)) {
                $p_id = absint($p_id);
                $listing = get_post($p_id);
                // kick the user out if he tries to edit the listing of other user
                if ($listing->post_author != get_current_user_id() && !current_user_can('edit_others_at_biz_dirs')) {
                    echo '<p class="error">' . __('You do not have permission to edit this listing', 'directorist') . '</p>';
                    return;
                }
                $listing_info['never_expire'] = get_post_meta($p_id, '_never_expire', true);
                $listing_info['featured'] = get_post_meta($p_id, '_featured', true);
                $listing_info['listing_type'] = get_post_meta($p_id, '_listing_type', true);
                $listing_info['price'] = get_post_meta($p_id, '_price', true);
                $listing_info['videourl'] = get_post_meta($p_id, '_videourl', true);
                $listing_info['price_range'] = get_post_meta($p_id, '_price_range', true);
                $listing_info['atbd_listing_pricing'] = get_post_meta($p_id, '_atbd_listing_pricing', true);
                $listing_info['listing_status'] = get_post_meta($p_id, '_listing_status', true);
                $listing_info['tagline'] = get_post_meta($p_id, '_tagline', true);
                $listing_info['atbdp_post_views_count'] = get_post_meta($p_id, '_atbdp_post_views_count', true);
                $listing_info['excerpt'] = get_post_meta($p_id, '_excerpt', true);
                $listing_info['address'] = get_post_meta($p_id, '_address', true);
                $listing_info['phone'] = get_post_meta($p_id, '_phone', true);
                $listing_info['phone2'] = get_post_meta($p_id, '_phone2', true);
                $listing_info['fax'] = get_post_meta($p_id, '_fax', true);
                $listing_info['email'] = get_post_meta($p_id, '_email', true);
                $listing_info['website'] = get_post_meta($p_id, '_website', true);
                $listing_info['zip'] = get_post_meta($p_id, '_zip', true);
                $listing_info['social'] = get_post_meta($p_id, '_social', true);
                $listing_info['faqs'] = get_post_meta($p_id, '_faqs', true);
                $listing_info['manual_lat'] = get_post_meta($p_id, '_manual_lat', true);
                $listing_info['manual_lng'] = get_post_meta($p_id, '_manual_lng', true);
                $listing_info['hide_map'] = get_post_meta($p_id, '_hide_map', true);
                $listing_info['bdbh'] = get_post_meta($p_id, '_bdbh', true);
                $listing_info['enable247hour'] = get_post_meta($p_id, '_enable247hour', true);
                $listing_info['disable_bz_hour_listing'] = get_post_meta($p_id, '_disable_bz_hour_listing', true);
                $listing_info['hide_contact_info'] = get_post_meta($p_id, '_hide_contact_info', true);
                $listing_info['hide_contact_owner'] = get_post_meta($p_id, '_hide_contact_owner', true);
                $listing_info['expiry_date'] = get_post_meta($p_id, '_expiry_date', true);
                $listing_info['t_c_check'] = get_post_meta($p_id, '_t_c_check', true);
                $listing_info['privacy_policy'] = get_post_meta($p_id, '_privacy_policy', true);
                $listing_info['id_itself'] = $p_id;

                extract($listing_info);
                $listing_img = atbdp_get_listing_attachment_ids($p_id);
                //for editing page
                $p_tags = wp_get_post_terms($p_id, ATBDP_TAGS);
                $p_locations = wp_get_post_terms($p_id, ATBDP_LOCATION);
                $p_cats = wp_get_post_terms($p_id, ATBDP_CATEGORY);
            }
            // prevent the error if it is not edit listing page when listing info var is not defined.
            if (empty($listing_info)) {
                $listing_info = array();
            }

            $t = get_the_title();
            $t = !empty($t) ? esc_html($t) : __('No Title ', 'directorist');
            $tg = !empty($tagline) ? esc_html($tagline) : '';
            $ad = !empty($address) ? esc_html($address) : '';
            $image = (!empty($listing_img[0])) ? "<img src='" . esc_url(wp_get_attachment_image_url($listing_img[0], 'thumbnail')) . "'>" : '';
            /*build the markup for google map info window*/
            $info_content = "<div class='map_info_window'> <h3> {$t} </h3>";
            $info_content .= "<p> {$tg} </p>";
            $info_content .= $image; // add the image if available
            $info_content .= "<p> {$ad}</p></div>";
            // grab social information
            $social_info = !empty($social) ? (array)$social : array();
            $listing_img = !empty($listing_img) ? (array)$listing_img : array();
            $listing_prv_img = !empty($listing_prv_img) ? $listing_prv_img : '';
            // get the category and location lists/array
            $categories = get_terms(ATBDP_CATEGORY, array('hide_empty' => 0));
            $locations = get_terms(ATBDP_LOCATION, array('hide_empty' => 0));
            $listing_tags = get_terms(ATBDP_TAGS, array('hide_empty' => 0));

            // get the map zoom level from the user settings
            $default_latitude = get_directorist_option('default_latitude', '40.7127753');
            $default_longitude = get_directorist_option('default_longitude', '-74.0059728');
            $map_zoom_level = get_directorist_option('map_zoom_level', 4);
            $disable_price = get_directorist_option('disable_list_price');
            $enable_video_url = get_directorist_option('atbd_video_url', 1);
            $disable_contact_info = get_directorist_option('disable_contact_info');
            $disable_contact_owner = get_directorist_option('disable_contact_owner', 1);
            $display_title_for = get_directorist_option('display_title_for', 0);
            $display_desc_for = get_directorist_option('display_desc_for', 0);
            $display_cat_for = get_directorist_option('display_cat_for', 'users');
            $display_loc_for = get_directorist_option('display_loc_for', 0);
            $multiple_loc_for_user = get_directorist_option('multiple_loc_for_user', 1);
            $multiple_cat_for_user = get_directorist_option('multiple_cat_for_user', 1);
            $display_tag_for = get_directorist_option('display_tag_for', 0);
            $display_tagline_field = get_directorist_option('display_tagline_field', 0);
            $tagline_placeholder = get_directorist_option('tagline_placeholder', __('Your Listing\'s motto or tag-line', 'directorist'));
            $display_tagline_for = get_directorist_option('display_tagline_for', 0);
            $guest_listings = get_directorist_option('guest_listings', 0);
            // get the custom terms and conditions
            $listing_terms_condition_text = get_directorist_option('listing_terms_condition_text');
            $display_pricing_field = get_directorist_option('display_pricing_field', 1);
            $price_placeholder = get_directorist_option('price_placeholder', __('Price of this listing. Eg. 100', 'directorist'));
            $display_price_for = get_directorist_option('display_price_for', 'admin_users');
            $display_price_range_field = get_directorist_option('display_price_range_field', 1);
            $price_range_placeholder = get_directorist_option('price_range_placeholder', __('Price Range', 'directorist'));
            $display_price_range_for = get_directorist_option('display_price_range_for', 'admin_users');
            $display_excerpt_field = get_directorist_option('display_excerpt_field', 0);
            $display_views_count = apply_filters('atbdp_listing_form_view_count_field', get_directorist_option('display_views_count', 1));
            $display_views_count_for = get_directorist_option('display_views_count_for', 1);
            $excerpt_placeholder = get_directorist_option('excerpt_placeholder', __('Short Description or Excerpt', 'directorist'));
            $display_short_desc_for = get_directorist_option('display_short_desc_for', 0);
            $display_address_field = get_directorist_option('display_address_field', 1);
            $display_address_for = get_directorist_option('display_address_for', 0);
            $display_phone_field = get_directorist_option('display_phone_field', 1);
            $display_phone_for = get_directorist_option('display_phone_for', 0);
            $display_phone2_field = get_directorist_option('display_phone_field2', 1);
            $display_phone2_for = get_directorist_option('display_phone2_for', 0);
            $display_fax_field = get_directorist_option('display_fax', 1);
            $display_fax_for = get_directorist_option('display_fax_for', 0);
            $display_email_field = get_directorist_option('display_email_field', 1);
            $display_email_for = get_directorist_option('display_email_for', 0);
            $allow_decimal = get_directorist_option('allow_decimal', 1);
            $display_website_field = get_directorist_option('display_website_field', 1);
            $display_website_for = get_directorist_option('display_website_for', 0);
            $display_zip_field = get_directorist_option('display_zip_field', 1);
            $display_zip_for = get_directorist_option('display_zip_for', 0);
            $zip_placeholder = get_directorist_option('zip_placeholder', __('Enter Zip/Post Code', 'directorist'));
            $display_social_info_field = get_directorist_option('display_social_info_field', 1);
            $display_social_info_for = get_directorist_option('display_social_info_for', 0);
            $display_map_field = get_directorist_option('display_map_field', 1);
            $display_map_for = get_directorist_option('display_map_for', 0);
            $address_placeholder = get_directorist_option('address_placeholder', __('Listing address eg. New York, USA', 'directorist'));
            $website_placeholder = get_directorist_option('website_placeholder', __('Listing Website eg. http://example.com', 'directorist'));
            $display_gallery_field = get_directorist_option('display_gallery_field', 1);
            $display_video_field = get_directorist_option('display_video_field', 1);
            $display_glr_img_for = get_directorist_option('display_glr_img_for', 0);
            $display_video_for = get_directorist_option('display_video_for', 0);
            $select_listing_map = get_directorist_option('select_listing_map', 'google');
            $display_contact_hide = get_directorist_option('display_contact_hide', 1);
            $contact_hide_label = get_directorist_option('contact_hide_label', __('Check it to hide Contact Information for this listing', 'directorist'));
            $container_fluid = is_directoria_active() ? 'container' : 'container-fluid';
            $fm_plan = !empty(get_post_meta($p_id, '_fm_plans', true)) ? get_post_meta($p_id, '_fm_plans', true) : '';
            $currency = get_directorist_option('g_currency', 'USD');
            $plan_cat = array();
            if (is_fee_manager_active()) {
                $plan_cat = is_plan_allowed_category($fm_plan);
            }
            $query_args = array(
                'parent' => 0,
                'term_id' => 0,
                'exclude' => $plan_cat,
                'hide_empty' => 0,
                'orderby' => 'name',
                'order' => 'asc',
                'show_count' => 0,
                'single_only' => 0,
                'pad_counts' => true,
                'immediate_category' => 0,
                'active_term_id' => 0,
                'ancestors' => array()
            );

            if (is_fee_manager_active() && !selected_plan_id()) {
                if ((strpos($current_url, '/edit/') !== false) && ($pagenow = 'at_biz_dir')) {
                    ATBDP()->enquirer->add_listing_scripts_styles();
                    // ATBDP()->load_template('front-end/add-listing');

                    $path = atbdp_get_theme_file("/directorist/shortcodes/add-listing.php");
                    if ( $path ) {
                        include $path;
                    } else {
                        include ATBDP_TEMPLATES_DIR . "public-templates/shortcodes/add-listing.php";
                    }
                } else {
                    if (class_exists('ATBDP_Pricing_Plans')) {
                        do_action('atbdp_before_pricing_plan_page_load');
                        ATBDP_Pricing_Plans()->load_template('fee-plans', array('atts' => $atts));
                    } else {
                        do_action('atbdp_before_pricing_plan_page_load');
                        DWPP_Pricing_Plans()->load_template('fee-plans', array('atts' => $atts));
                    }

                }
            } else {
                ATBDP()->enquirer->add_listing_scripts_styles();
                $path = atbdp_get_theme_file("/directorist/shortcodes/add-listing.php");
                if ( $path ) {
                    include $path;
                } else {
                    include ATBDP_TEMPLATES_DIR . "public-templates/shortcodes/add-listing.php";
                }
            }
            return ob_get_clean();
        }

        public function custom_user_login()
        {
            $forms = new Directorist_Listing_Forms();
            return $forms->render_shortcode_user_login();
        }

        public function user_registration()
        {
            $forms = new Directorist_Listing_Forms();
            return $forms->render_shortcode_custom_registration();
        }

        // guard
        public function guard( Array $args = [] ) {
            $type = ( ! empty( $args['type'] ) ) ? $args['type'] : 'auth';
            $login_redirect = ( ! empty( $args['login_redirect'] ) ) ? $args['login_redirect'] : false;

            if ( $type === 'auth' && ! atbdp_logged_in_user() && ! $login_redirect ) {
                ob_start();
                // user not logged in;
                $error_message = sprintf(__('You need to be logged in to view the content of this page. You can login %s. Don\'t have an account? %s', 'directorist'), apply_filters('atbdp_listing_form_login_link', "<a href='" . ATBDP_Permalink::get_login_page_link() . "'> " . __('Here', 'directorist') . "</a>"), apply_filters('atbdp_listing_form_signup_link', "<a href='" . ATBDP_Permalink::get_registration_page_link() . "'> " . __('Sign Up', 'directorist') . "</a>")); 
                ?>
                <section class="directory_wrapper single_area">
                    <?php ATBDP()->helper->show_login_message($error_message); ?>
                </section>
                <?php
                return ob_get_clean();
            }

            return '';
        }

    }
endif;