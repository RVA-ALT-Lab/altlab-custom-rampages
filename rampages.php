<?php
/**
 * Plugin Name: RAMPAGES FORCED ITEMS
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
