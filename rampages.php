<?php
/**
 * Plugin Name: ALT LAB CUSTOM RAMPAGES FORCED ITEMS
 * Plugin URI: https://github.com/
 * Description: various things to make rampages behave details in plugin comments
 * Version: .7
 * Author: Tom Woodward
 * Author URI: http://bionicteaching.com
 * License: GPL2
 */

 /*   2015 Tom Woodward   (email : bionicteaching@gmail.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );


/*-------------------------------------------FIX COMMENT NAMES TO REFLECT DISPLAY NAMES-------------------------------------------*/
//make sure comments reflect display name from https://wordpress.stackexchange.com/questions/31694/comments-do-not-respect-display-name-setting-how-to-make-plugin-to-overcome-thi
add_filter('get_comment_author', 'wpse31694_comment_author_display_name');
function wpse31694_comment_author_display_name($author) {
    global $comment;
    if (!empty($comment->user_id)){
        $user=get_userdata($comment->user_id);
        $author=$user->display_name;
    }

    return $author;
}

/*-------------------------------------------NEW FILE TYPES ALLOWED HERE-------------------------------------------*/
//allow some additional file types for upload
function my_custom_mime_types( $mimes ) {

        // New allowed mime types.
        $mimes['svg'] = 'image/svg+xml';
        $mimes['svgz'] = 'image/svg+xml';
        $mimes['studio3'] = 'application/octet-stream';

        // Optional. Remove a mime type.
        unset( $mimes['exe'] );

    return $mimes;
}
add_filter( 'upload_mimes', 'my_custom_mime_types' );


/*------------------------------------shortcodes in widgets ---------------------------------------------------*/
// Enable shortcodes in text widgets
add_filter('widget_text','do_shortcode');


/**
 *  Remove the h1 tag from the WordPress editor.
 *
 *  @param   array  $settings  The array of editor settings
 *  @return  array             The modified edit settings
 */

function my_format_TinyMCE( $in ) {
        $in['block_formats'] = "Paragraph=p; Heading 2=h2; Heading 3=h3; Heading 4=h4; Heading 5=h5; Heading 6=h6;Preformatted=pre";
    return $in;
}
add_filter( 'tiny_mce_before_init', 'my_format_TinyMCE' );


/*------------------------------------ENABLE CSS FOR ADMINS-------------------------------------------------*/
//from https://wordpress.org/plugins/multisite-custom-css/ just didn't want another plugin

add_filter( 'map_meta_cap', 'multisite_custom_css_map_meta_cap', 20, 2 );
function multisite_custom_css_map_meta_cap( $caps, $cap ) {
    if ( 'edit_css' === $cap && is_multisite() ) {
        $caps = array( 'edit_theme_options' );
    }
    return $caps;
}



/*------------------------------------H5P  ---------------------------------------------------*/
// Make H5P embeds flexible
include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

if ( is_plugin_active(  'h5p/h5p.php' ) ) {
  //plugin is activated
     add_action('wp_enqueue_scripts', 'h5pflex_widget_enqueue_script');
}


function h5pflex_widget_enqueue_script() {
    $h5p_script = plugins_url( 'h5p/h5p-php-library/js/h5p-resizer.js', __DIR__);
    wp_enqueue_script( 'h5p_flex', $h5p_script, true );

    }


/*------------------------------------PRIVACY FOOTER  ---------------------------------------------------*/
// Make footer element for all rampages sits
function vcu_privacy_function() {
    $avoid = [29429, 29719];
    $id = get_current_blog_id();
    if (!in_array($id, $avoid)) {
        echo '<style>.privacy-policy { display: block; background-color:#fff; margin: 2em 0; padding: 2em; z-index: 1000; overflow: hidden;} .privacy-policy a {color: #424242}</style>';
        echo '<div class="privacy-policy" id="private"><a href="https://rampages.us/privacy-policy/">Privacy Statement</a></div>';
    }
}
add_action( 'wp_footer', 'vcu_privacy_function', 100 );


/*------------------------------------REMOVE EMAILS FOR NON-SUPERADMINS------------------------------------*/

add_filter('manage_users_columns','remove_users_columns');
function remove_users_columns($column_headers) {
            global $current_user;
            $super_admins = get_super_admins();
            if ( is_array( $super_admins ) && in_array( $current_user->user_login, $super_admins ) ){
                return $column_headers;
            }
            else {
             unset($column_headers['email']);
             return $column_headers;
        }

}


/*------------------------------------DEAL WITH OUR EMAIL FILTERING STUFF------------------------------------*/
//deals with filtering application drama from VCU/Cisco for password resets

add_filter( 'retrieve_password_title',
    function( $title )
    {
        $title = __( 'VCU Ram Pages Reset Request' );
        return $title;
    }
);



//attempts to deal with activation email filtering issues
add_filter( 'wpmu_signup_blog_notification_subject', 'rampages_activation_subject', 10, 4 );
add_filter( 'wpmu_signup_user_notification_subject', 'rampages_activation_subject', 10, 4 );

function rampages_activation_subject( $text ) {
    return 'Please activate your new Ram Pages account';
}


/*---------------------------------JSON MOD FOR ADDITIONAL SITE INFO----------------------------------*/

function extraJsonData($response){
    $blog_id = get_current_blog_id();
    $blog_details = get_blog_details($blog_id);
    $data = $response->data;
    $data['created'] =$blog_details->registered;
    $data['last_updated'] =$blog_details->last_updated;
    $data['post_count'] =$blog_details->post_count;
    $data['page_count'] = wp_count_posts('page','publish');
    $response->set_data($data);
    return $response;
}

add_filter('rest_index', 'extraJsonData');

/*---------------------------------GDPR NONSENSE------------------------------*/

function hook_gdpr_assets() {

    wp_register_style('cookie_consent','//cdnjs.cloudflare.com/ajax/libs/cookieconsent2/3.1.0/cookieconsent.min.css');
    wp_enqueue_style('cookie_consent');

    wp_register_script('cookie_consent_js','//cdnjs.cloudflare.com/ajax/libs/cookieconsent2/3.1.0/cookieconsent.min.js', null, null, false);
    wp_enqueue_script('cookie_consent_js');

    wp_register_script('gdpr_popup_js', plugins_url('assets/gdpr-popup.js', __FILE__), null, null, false);
    wp_enqueue_script('gdpr_popup_js');
}

add_action('wp_enqueue_scripts', 'hook_gdpr_assets');


/*---------------------------NEW SITE DEFAULT COMMENTS OFF ---------------------------------------*/


//OLD VERSION - wasn't changeable by individual site admin
// function require_comment_login_wpmu_new_blog_example( $blog_id ) {
//     update_blog_option($blog_id,'comment_registration',1);
// }
// add_action( 'wpmu_new_blog', 'require_comment_login_wpmu_new_blog_example', 10, 1 );



add_action( 'wp_loaded','rampages_require_comment_login' );


function rampages_require_comment_login() {

    add_action(
        'wpmu_new_blog',
        function( $blog_id, $user_id ) {

            switch_to_blog( $blog_id );

             update_blog_option($blog_id,'comment_registration',1); //require comment moderation

            restore_current_blog();
        },
        10,
        2
    );
}




/*------------------------ FILTERING GRAVITY FORMS CONFIRMATION MESSAGE TO ALLOW VARIABLE BUT REMOVE SCIPTS ETC--------------------------------------*/

add_filter( 'gform_sanitize_confirmation_message', '__return_true' );




/*------------------------ ADD REMOVE SELF FROM MY SITES LIST --------------------------------------*/



function hidden_blogs($user_id){
    //THIS SHOULD GO TO THE USER PROFILE MAYBE AND GET THE LIST -- DOES FILTER MY SITES PAGE AND DROP DOWN
    $hidden_blogs = get_user_meta($user_id, 'my_hidden_sites', true); 
    return $hidden_blogs;
}

function remove_selected_blogs_from_get_blogs($blogs) {
    global $pagenow; //mostly works to allow full list on profile page but filter elsewhere
    $newblogs = array();
    $user_id = wp_get_current_user()->ID;
    $hidden_blogs = explode(",",hidden_blogs($user_id));
    if ( !is_super_admin() &&  $pagenow != 'profile.php') {
        foreach ($blogs as $key => $value) {
            if (!in_array($value->userblog_id, $hidden_blogs) )
                $newblogs[$key] = $value;
        }
        return $newblogs;
    } else {
        return $blogs;
    }
}
add_filter( 'get_blogs_of_user', 'remove_selected_blogs_from_get_blogs' );


//add sites interface to user profile field
add_action( 'show_user_profile', 'hidden_site_user_profile_fields' );
add_action( 'edit_user_profile', 'hidden_site_user_profile_fields' );

function hidden_site_user_profile_fields( $user ) { ?>
    <span class='dashicons dashicons-hidden big-eye'></span>
    <h3><?php _e("Hide a Site?", "blank"); ?></h3>
    <?php rampages_get_user_sites($user->ID);?>
    <table class="form-table">
    <tr>
        <th><label for="my_hidden_sites"><?php _e(""); ?></label></th>
        <td>
            <input type="hidden" name="my_hidden_sites" id="my_hidden_sites" value="<?php echo esc_attr( get_the_author_meta( 'my_hidden_sites', $user->ID ) ); ?>" class="regular-text" /><br />
            <span class="description"><?php _e(""); ?></span>
        </td>
    </tr>
    </table>
<?php }

add_action( 'personal_options_update', 'save_hidden_site_user_profile_fields' );
add_action( 'edit_user_profile_update', 'save_hidden_site_user_profile_fields' );

function save_hidden_site_user_profile_fields( $user_id ) {
    if ( !current_user_can( 'edit_user', $user_id ) ) { 
        return false; 
    }
    update_user_meta( $user_id, 'my_hidden_sites', $_POST['my_hidden_sites'] );
}


function rampages_get_user_sites($user_id){
    $user_blogs = get_blogs_of_user( $user_id );
    //var_dump($user_blogs);
    echo 'Check the sites you would like to hide. Do not forget to update your profile at the bottom of this page.<ul>';
    foreach ($user_blogs AS $user_blog) {
        echo '<li class="hidden-list"><input type="checkbox" name="blog-' . $user_blog->userblog_id .'" id="blog-' . $user_blog->userblog_id .'" value="' . $user_blog->userblog_id . '"/> <label for="blog-' . $user_blog->userblog_id .'">'.$user_blog->blogname.'</label></li>';
    }
    echo '</ul>';
}



function hidden_sites_js_enqueue($hook) {
    if ( 'profile.php' != $hook ) {
        return;
    }
    wp_enqueue_style( 'hidden_sites_css', plugins_url('assets/hidden-sites.css', __FILE__), null, null, false);
    wp_enqueue_script( 'hidden_sites_js', plugins_url('assets/hidden-sites.js', __FILE__), null, null, false);
}
add_action( 'admin_enqueue_scripts', 'hidden_sites_js_enqueue' );



//write to user profile if user is faculty


function user_status ($user){
    //attempt to differentiate between new user ID and edit profile user object
    if (is_numeric($user)){
        $user_id = $user;
        $user_info = get_userdata($user_id);
        $email = $user_info->user_email;
    } else {
        $email = $user->user_email;
        $user_id = $user->ID;
    }
    $url_email = urlencode($email);
    $url = 'https://phonebook.vcu.edu/?Qname=' . $url_email . '&Qdepartment=*';
    
    $ch = curl_init(); 
    curl_setopt($ch, CURLOPT_URL, $url); 

    //return the transfer as a string 
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 

    // $output contains the output string 
    $output = curl_exec($ch);
    $fail = preg_match('/No matches/', $output, $matches);
    $user = get_user_meta($user_id);
    $exists = get_user_meta($user_id, 'user_vcu_status');
    if ($fail == 0) {
        $status = 'faculty';
        if ($exists){
            update_user_meta( $user_id, 'user_vcu_status',  $status );
        } else {
            add_user_meta( $user_id, 'user_vcu_status',  $status, true );
        }
    } else {
        $status = 'student';
       if ($exists){
            update_user_meta( $user_id, 'user_vcu_status',  $status );
        } else {
            add_user_meta( $user_id, 'user_vcu_status',  $status, true );
        }      
    }
    curl_close($ch);      
}


add_action ('wpmu_new_user', 'user_status', 10, 1);

add_action( 'edit_user_profile', 'user_status', 10, 1 );

/**
 * Show custom user profile fields
 * 
 * @param  object $profileuser A WP_User object
 * @return void
 */
function custom_user_profile_fields( $profileuser ) {
?>
    <table class="form-table">
        <tr>
            <th>
                <label for="user_status"><?php esc_html_e( 'Status' ); ?></label>
            </th>
            <td>
                <?php echo esc_attr( get_the_author_meta( 'user_vcu_status', $profileuser->ID ) ); ?>                
            </td>
        </tr>
    </table>
<?php
}
add_action( 'edit_user_profile', 'custom_user_profile_fields', 10, 1 );



//TWITTER TIMELINE WIDGET
// [twitter name=""]
function altlab_twitter_func( $atts ) {
    extract(shortcode_atts( array(
         'name' => '', //name of account
    ), $atts));

    return '<a class="twitter-timeline" href="https://twitter.com/' . $name . '?ref_src=twsrc%5Etfw">Tweets by' . $name . '</a> <script async src="https://platform.twitter.com/widgets.js" charset="utf-8"></script>';
}
add_shortcode( 'twitter', 'altlab_twitter_func' );



//iframe shortcode 

function alt_iframe_shortcode( $atts) {    
    extract(shortcode_atts( array(
         'src' => '', //site URL
         'width' => '',
         'height' => '',
    ), $atts));
    if (!$height){
        $height ="800px";
    }
    if (!$width){
        $width = "100%";
    }
  
    $html = '<iframe src="' . $src .  '" height="' . $height . '" width="' . $width . '"></iframe>';    

    return  $html;
}

add_shortcode( 'iframe', 'alt_iframe_shortcode' );


//search shortcode from https://www.wpbeginner.com/wp-tutorials/how-to-add-search-form-in-your-post-with-a-wordpress-search-shortcode/
function search_form_builder( $form ) {
 
    $form = '<form role="search" method="get" id="searchform" action="' . home_url( '/' ) . '" >
    <div><label class="screen-reader-text" for="s">' . __('Search for:') . '</label>
    <input type="text" value="' . get_search_query() . '" name="s" id="s" />
    <input type="submit" id="searchsubmit" value="'. esc_attr__('Search') .'" />
    </div>
    </form>';
 
    return $form;
}
 
add_shortcode('search-it', 'search_form_builder');