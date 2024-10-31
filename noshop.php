<?php
/*
Plugin Name: NoShop
Plugin URI: http://jesper.angelo.net/wordpress/noshop/
Description: NoShop. Allows you to put a list of items on a structured list with pictures, replacing the need for a real shopping cart.
Version: 0.8.4
Author: Jesper Angelo
Author URI: http://jesper.angelo.net/
License: GPL2
 */
/*  Copyright 2012  JESPER ANGELO  (email : jesper@angelo.net)

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

global $wpdb;

if ( ! function_exists( 'is_ssl' ) ) {
    function is_ssl() {
        if ( isset($_SERVER['HTTPS']) ) {
            if ( 'on' == strtolower($_SERVER['HTTPS']) )
                return true;
            if ( '1' == $_SERVER['HTTPS'] )
                return true;
        } elseif ( isset($_SERVER['SERVER_PORT']) && ( '443' == $_SERVER['SERVER_PORT'] ) ) {
            return true;
        }
        return false;
    }
}

if ( version_compare( get_bloginfo( 'version' ) , '3.0' , '<' ) && is_ssl() ) {
    $wp_content_url = str_replace( 'http://' , 'https://' , get_option( 'siteurl' ) ) . "HEY";
} else {
    $wp_content_url = get_option( 'siteurl' ) . "DUDE";
}
$wp_content_url = '/wp-content';
$wp_content_dir = ABSPATH . 'wp-content';
$wp_plugin_url = $wp_content_url . '/plugins';
$wp_plugin_dir = $wp_content_dir . '/plugins';
$wpmu_plugin_url = $wp_content_url . '/mu-plugins';
$wpmu_plugin_dir = $wp_content_dir . '/mu-plugins';

$wp_noshop_url = $wp_plugin_url . '/noshop';
$wp_noshop_dir = $wp_plugin_dir . '/noshop';
$wpmu_noshop_url = $wpmu_plugin_url . '/noshop';
$wpmu_noshop_dir = $wpmu_plugin_dir . '/noshop';

$wpdb->show_errors();

global $noshop_version;
global $noshop_db_version;
$noshop_version = "0.8.4";
$noshop_db_version = "0.8.4";

register_activation_hook(__FILE__,'NoShop::noshop_activate');

if (!class_exists("NoShop")) {

    class NoShop {
        //constructor
        function NoShop()
        {
            // Hook up Actions and Filters
            //if (isset($noshop_plugin)) {
            //Actions
            add_action('wp_head', 'NoShop::CSS');
            add_action('wp_footer', 'NoShop::Products');

            //Filters
            add_filter('the_content', 'NoShop::ShowTable');
            add_action('admin_menu', 'NoShop::PluginMenu');
            //}
        }

        // Install part: Create table for products (or check if it's there at least)
        public static function noshop_activate() {
            global $wpdb, $noshop_version, $noshop_db_version;

            // First the Product Table
            $table_name = $wpdb->prefix . "noshop_products";
            //				if($wpdb->get_var("show tables like '$table_name'") != $table_name) {
            $sql = "CREATE TABLE `" . $table_name . "` (
						id mediumint(9) NOT NULL AUTO_INCREMENT,
						time bigint(11) DEFAULT '0' NOT NULL,
						category tinytext NOT NULL,
						title tinytext NOT NULL,
						description text NOT NULL,
						url VARCHAR(250) NOT NULL,
						imgurl VARCHAR(250) NOT NULL,
						imgurlmode VARCHAR(1) NOT NULL,
						ndx mediumint(9) DEFAULT '0' NOT NULL ,
						UNIQUE KEY  id (id),
						PRIMARY KEY  primarykey (id)
					);";
            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
            dbDelta($sql);

            // Second some Product Specs
            $table_name = $wpdb->prefix . "noshop_product_specs";
            //				if($wpdb->get_var("show tables like '$table_name'") != $table_name) {
            $sql = "CREATE TABLE `" . $table_name . "` (
						id mediumint(9) NOT NULL AUTO_INCREMENT,
						product_id mediumint(9) NOT NULL,
						time bigint(11) DEFAULT '0' NOT NULL,
						spectitle tinytext NOT NULL,
						specvalue text NOT NULL,
						UNIQUE KEY  id (id)
					);";
            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
            dbDelta($sql);
            add_option("noshop_db_version", $noshop_db_version);
            update_option("noshop_db_version", $noshop_db_version);
            
        } // End noshop_activate()




        // This just echoes the chosen line, we'll position it later
        public static function Products() {
            global $wpdb;
            //$wpdb->print_error();
        } // End Products()




        // Now we set that function up to execute when the admin_footer action is called

        // We need some CSS to position the paragraph
        public static function CSS() {
            global $wp_noshop_url;
            global $wpmu_noshop_url;

            // This makes sure that the posinioning is also good for right-to-left languages
            //          $x = ( is_rtl() ) ? 'left' : 'right';
            echo "<link rel=\"stylesheet\" href=\"".$wp_noshop_url."/noshop.css\" \>\n";
            echo "<!-- ".$wp_noshop_url." -->\n";
            echo "<!-- ".$wpmu_noshop_url." -->\n";

        } // End CSS()

        public static function ShowTable($content) {
            $cat = "";
            $search = "@\s*\[NoShop ([^\]]+)\]\s*@i";
            if(preg_match_all($search, $content, $matches)) {
                if(is_array($matches)) {
                    //print("\n<br />MATCHES, MATCH\n");
                    //print_r($matches);
                    //print("\n<br />\n");
                    foreach($matches[1] as $key => $cat) {
                        // Get the data from the tag
                        //print("\n<br />MATCH, +\n");
                        //print("\n<pre>\n");
                        //print($cat);
                        //print(" | ");
                        //print($matches[0][$key]);
                        //print("\n</pre>\n");
                        //print("\n<br />\n");
                        $sstr = addslashes($matches[0][$key]);
                        //$cat = $match;
                        //print("\n<br />\n");
                        //print("\n<pre>MATCH, SSTR, CAT\n");
                        //print("<!-- *** SSTR: ".$sstr." -->\n\n");
                        //print("<!-- *** CAT: ".$cat." -->\n\n");
                        //print("\n</pre>\n");
                        //print("\n<br />\n");

                        //$content = str_replace ($search, $replace, $content);
                        $content = str_replace( "$sstr", NoShop::createtable($cat), $content );
                    }
                }
            }

            //$content = str_replace( "[NoShop]", "THIS?", $content );
            return $content;
        } // End ShowTable($content)

        public function createtable($cat) {
            //echo "<p>Category is [ ".$cat." ]</p>";

            // get options
            global $wp_noshop_url;
            global $wp_noshop_dir;
            global $wpmu_noshop_url;
            global $wpmu_noshop_dir;

            $options = get_option('noshop_options');

            if($wptouch)	$width=$options['wptouchwidth'];
            else			$width=$options['width'];
            $widthparam = " width=".$width." style=\"width:".$width."px;\" ";

            global $table_prefix, $wpdb;
            $table_name = $wpdb->prefix . "noshop_products";

            if($cat<>"") {
                $sql = $wpdb->prepare( "SELECT * FROM $table_name WHERE category=%s ORDER BY ndx, title", $cat );
            } else {
                $sql = $wpdb->prepare( "SELECT * FROM $table_name ORDER BY ndx, title" );
            };
            
            $return = '';
            $myrows = $wpdb->get_results( $sql );
            foreach ($myrows as $myrows) {
                $ret = file_get_contents( $wp_noshop_dir . '/table.template.xhtml' );
                $ret = str_replace( '{url}', $myrows->url, $ret );
                if($myrows->url<>"") {
                    $ret = str_replace( '{autourlbegin}', '<a href="'.$myrows->url.'">', $ret );
                    $ret = str_replace( '{autourlend}', '</a>', $ret );
                } else {
                    $ret = str_replace( '{autourlbegin}', '', $ret );
                    $ret = str_replace( '{autourlend}', '', $ret );
                }
                $ret = str_replace( '{imgurl}', ( $myrows->imgurl ? $myrows->imgurl : $options['defimg'] ), $ret );
                $ret = str_replace( '{title}', $myrows->title, $ret );
                $ret = str_replace( '{hashtitle}', urlencode($myrows->title), $ret );
                $ret = str_replace( '{description}', $myrows->description, $ret );
                $ret = str_replace( '{subtable}', NoShop::createsubtable($myrows->id), $ret );
                $ret = str_replace( '{width}', $width, $ret );
                $ret = str_replace( '{widthparam}', $widthparam, $ret );
                $ret = str_replace( '{cat}', $cat, $ret );
                $ret = str_replace( '{ndx}', $myrows->ndx, $ret );
                $return .= "\n\n<!-- -------------------- NoShop Item: ".$myrows->category." / ".$myrows->title." -------------------- -->\n" . $ret . "\n";
            } // End foreach
            return $return;
        } // End createtable()

        public function createsubtable($id) {
            global $table_prefix, $wpdb;
            global $wp_noshop_url;
            global $wp_noshop_dir;
            global $wpmu_noshop_url;
            global $wpmu_noshop_dir;

            $options = get_option('noshop_options');

            $spectitlewidth=$options['spectitlewidth'];
            $spectitlewidthparam = " width=".$spectitlewidth." style=\"width:".$spectitlewidth."px;\" ";

            $table_name = $wpdb->prefix . "noshop_product_specs";

            $return = '';
            $myrows = $wpdb->get_results( "SELECT * FROM " . $table_name . " WHERE product_id=" . intval($id) ."" );
            foreach ($myrows as $myrows) {
                $ret = file_get_contents( $wp_noshop_dir . '/subtable.template.xhtml' );
                $ret = str_replace( '{spectitle}', $myrows->spectitle, $ret );
                $ret = str_replace( '{specvalue}', $myrows->specvalue, $ret );
                $ret = str_replace( '{spectitlewidth}', $spectitlewidth, $ret );
                $ret = str_replace( '{spectitlewidthparam}', $spectitlewidthparam, $ret );
                $return .= "\n\n<!-- -------------------- NoShop Item Specification: ".$myrows->spectitle." -------------------- -->\n" . $ret . "\n";
            } // End foreach
            return $return;
        } // End createsubtable()







        // //////////////////////////////////////////////////////////////////////////////////////////////////////////// //
        //                                                                                                              //
        //   OPTION PAGE                                                                                                //
        //                                                                                                              //
        // //////////////////////////////////////////////////////////////////////////////////////////////////////////// //
        public static function OptionPage() {
            global $wpdb, $noshop_version, $noshop_db_version;

            // get options
            $options = $newoptions = get_option('noshop_options');

            $dir_name = '/wp-content/plugins/noshop';
            $url = get_bloginfo('wpurl');
            $myURL = $url.$dir_name.'/';
            //				printf( __('My URL: %s.', 'noshop'), $myURL);

            // Check for permissions
            if (!current_user_can('manage_options'))  {
                wp_die( __('You do not have sufficient permissions to access this page.') );
            }

            global $table_prefix, $wpdb;
            $table_name = $wpdb->prefix . "noshop_products";
            $subtable_name = $wpdb->prefix . "noshop_product_specs";

            // if submitted, process results
            if ( isset($_POST["noshop_submit"]) && $_POST["noshop_submit"]=="options") {
                //echo "*** Updating options!!! ***";
                $newoptions['width'] = strip_tags(stripslashes($_POST["width"]));
                $newoptions['wptouchwidth'] = strip_tags(stripslashes($_POST["wptouchwidth"]));
                $newoptions['defimg'] = strip_tags(stripslashes($_POST["defimg"]));
                $newoptions['spectitlewidth'] = strip_tags(stripslashes($_POST["spectitlewidth"]));
                $newoptions['noshopcss'] = strip_tags(stripslashes($_POST["noshopcss"]));

                if ($_POST["visibleerrors"]=="on") {
                    $newoptions['visibleerrors'] = "true";
                } else {
                    $newoptions['visibleerrors'] = "";
                }

                // //////////////////////////////////////////////////////////// //
                // check if installed (hook is not called if used as mu-plugin) //
                // //////////////////////////////////////////////////////////// //
                //$wtemp = get_option('noshop_width');
                //if( empty($wtemp) ){
                //	echo "WHAT NOT INST?!? ****";
                //	NoShop::noshop_activate();
                //}
            }

            // if product selected, save selection persistantly as an option
            if ( isset($_POST["noshop_submit"]) && $_POST["noshop_submit"]=="productselect") {
                $newoptions['selectproduct'] = strip_tags(stripslashes($_POST["selectproduct"]));
                // Update selected product option
                update_option('noshop_selectproduct', $_POST['noshop_selectproduct']);
            }

            // any changes? save!
            if ( $options != $newoptions ) {
                $options = $newoptions;
                update_option('noshop_options', $options);
            }

            // If needed, add a product
            if ( isset($_POST["noshop_submit"]) && $_POST["noshop_submit"]=="productadd") {
                $wpdb->query(
                    $wpdb->prepare( "INSERT INTO $table_name ( category, title, description, url, imgurl, imgurlmode ) VALUES ( %s, %s, %s, %s, %s, %s )",
                                    array(
                                            'New Category',
                                            'New Product',
                                            'Please type a description',
                                            'http://jesper.angelo.net/wordpress/noshop/',
                                            'http://s.wordpress.org/about/images/wordpress-logo-notext-bg.png',
                                            ''
                                    )
                    )
                );
            }

            // If needed, delete a product
            if ( isset($_POST["noshop_submit"]) && $_POST["noshop_submit"]=="productdelete") {
                $wpdb->query( $wpdb->prepare( "DELETE FROM $table_name WHERE id=%d", array( $options['selectproduct'] ) ) );
            }

            // If needed, add a specification row
            //				if ($_POST["addspec"]=="on") {
            if ( isset($_POST["noshop_submit"]) && $_POST["noshop_submit"]=="productaddspec") {
                $wpdb->query(
                    $wpdb->prepare( "INSERT INTO $subtable_name ( product_id, spectitle, specvalue ) VALUES ( %d, %s, %s )",
                                    array($options['selectproduct'], 'Specification', 'Value') )
                );
            }


            // ////////////////////////////////////////////////////////// //
            // option handling - db interaction                           //
            // ////////////////////////////////////////////////////////// //

            // if options form was sent, process those...
            if ( isset($_POST["noshop_submit"]) && $_POST["noshop_submit"]=="productupdate") {
                //if( isset($_POST['action']) && $_POST['action'] == "updateoptions" ){

                // Update the product info
                $sql = $wpdb->prepare( "UPDATE $table_name SET category=%s, title=%s, ndx=%s, description=%s, url=%s, imgurl=%s, imgurlmode=%s WHERE id=%d",
                                        array(
                                            mysql_real_escape_string($_POST[prodcat]),
                                            mysql_real_escape_string($_POST[prodtitle]),
                                            intval($_POST[prodndx]),
                                            mysql_real_escape_string($_POST[proddesc]),
                                            mysql_real_escape_string($_POST[produrl]),
                                            mysql_real_escape_string($_POST[prodimgurl]),
                                            mysql_real_escape_string($_POST[prodimgurlmode]),
                                            $options['selectproduct']
                                        )
                );
                $wpdb->query( $sql );
                //echo "SQL: $sql<br />";

                // Update the product spec if there (Thanks Gayan)
                if(is_array($_POST["spec"])){
                    foreach($_POST["spec"] as $spec) {
                        if($spec[title]!="") {
                            $sql = $wpdb->prepare( "UPDATE $subtable_name SET spectitle=%s, specvalue=%s WHERE product_id=%d AND id=%d",
                                                    array($spec[title], $spec[value], $options['selectproduct'], $spec[id] ) );
                        } else {
                            echo "One specification line item being deleted!<br />";
                            $sql = $wpdb->prepare( "DELETE FROM $subtable_name WHERE product_id=%d AND id=%d",
                                                    array($options['selectproduct'], $spec[id] ) );
                        }
                        $wpdb->query( $sql );
                    }
                }
            }




            
            
            
            
            
            
            
            
            
            
            


            // ////////////////////////////////////////////////////////// //
            // Build Product Option Page!
            // ////////////////////////////////////////////////////////// //
            $product_count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM " . $table_name));
            $dbversion = $wpdb->get_var( "SELECT option_value FROM $wpdb->options WHERE option_name LIKE 'noshop_db_version'" );

            echo '<div class="wrap"><p><h2>'.__('NoShop Options', 'noshop').'</h2></p>';
            echo '<p>';
            echo 'Total number of items in database: <b>' . $product_count . '</b> ';
            echo '| Currently selected item ID: ' . $options['selectproduct'] . ' ';
            echo '| Plugin Version: ' . $noshop_version . ' ';
            echo '| Plugin DB Version: ' . $noshop_db_version . ' ';
            echo '| Registered DB Version: ' . $dbversion . ' ';
            echo '</p>';
            echo '</div>';
            
            // ////////////////////////////////////////////////////////// //
            // Product creation
            // ////////////////////////////////////////////////////////// //

            echo'<form method="post" action="'.get_bloginfo('wpurl').'/wp-admin/options-general.php?page=noshop/noshop.php">';
            echo '<div class="wrap">';
            echo '<input type="hidden" name="noshop_submit" value="productadd"></input>';
            echo '<p class="submit"><input type="submit" value="'.__('Create new Product &raquo;', 'noshop').'"></input></p>';
            echo "</div>";
            echo '</form>';

            // ////////////////////////////////////////////////////////// //
            // Product selection
            // ////////////////////////////////////////////////////////// //


            $mycategories = $wpdb->get_results( "SELECT DISTINCT category FROM " . $table_name . " ORDER BY category");
            $mytitles = $wpdb->get_results( "SELECT title FROM " . $table_name . " ORDER BY category");

            // print_r($mycategories);

            $product_count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM " . $table_name));
            echo'<form method="post" action="'.get_bloginfo('wpurl').'/wp-admin/options-general.php?page=noshop/noshop.php">';
            echo '<div class="wrap">';
            $myrows = $wpdb->get_results( "SELECT * FROM " . $table_name . " ORDER BY Category, Title");
            echo '<select name="selectproduct" onchange=\'this.form.submit()\'>';
            foreach ($myrows as $myrows) {
                echo '<option value=' . $myrows->id . ''. ($myrows->id==$options['selectproduct']?' SELECTED':'') . '>' . $myrows->category . ' | ' . $myrows->title . '';
            }
            echo '</select>';

            // SUBMIT
            echo '<input type="hidden" name="noshop_submit" value="productselect"></input>';
            echo '<span class="submit"><input type="submit" value="'.__('Select Product &raquo;', 'noshop').'"></input></span>';
            echo "</div>";
            echo '</form>';


            // ////////////////////////////////////////////////////////// //
            // Products Form
            // ////////////////////////////////////////////////////////// //

            $mycurrentprod = $wpdb->get_results( "SELECT * FROM " . $table_name . " WHERE ID=".intval($options['selectproduct']) );

            // settings
            echo'<form method="post" action="'.get_bloginfo('wpurl').'/wp-admin/options-general.php?page=noshop/noshop.php">';

            // Image thumbnail
            //				echo '<div style="float:right; max-width:160px; max-height:160px; ">';
            //				echo '<img src="'.$mycurrentprod[0]->imgurl.'" style="float:right; max-width:160px; max-height:160px; " />';
            //				echo '</div>';

            echo '<div class="wrap">';

            echo '<table class="form-table">';

            // Category and Title
            echo '<tr valign="top"><th scope="row">'.__('Category, Title and Sort Index', 'noshop').'</th>';
            echo '<td>';
            echo '<input type="text" name="prodcat" value="'.$mycurrentprod[0]->category.'" size="15"></input>';
            echo '<input type="text" name="prodtitle" value="'.$mycurrentprod[0]->title.'" size="35"></input>';
            echo '<input type="text" name="prodndx" value="'.$mycurrentprod[0]->ndx.'" size="5"></input>';
            echo '</td></tr>';

            // Image URL
            echo '<tr valign="top"><th scope="row">'.__('URL to Product Image', 'noshop').'</th>';
            echo '<td>';
            echo '<input type="text" name="prodimgurl" value="'.$mycurrentprod[0]->imgurl.'" size="80"></input>';
            echo '<select name="prodimgurlmode">';
            echo '<option value=\'\''. ($mycurrentprod[0]->imgurlmode==''?' SELECTED':'') . '>Standard Link';
            echo '<option value=\'W\''. ($mycurrentprod[0]->imgurlmode=='W'?' SELECTED':'') . '>Popup in new Window';
            echo '<option value=\'T\''. ($mycurrentprod[0]->imgurlmode=='T'?' SELECTED':'') . '>Popup in new Tab';
            echo '</select>';
            echo '</td></tr>';

            // Description
            echo '<tr valign="top"><th scope="row">'.__('Product Description', 'noshop').'</th>';
            echo '<td>';
            echo '<textarea name="proddesc" rows=8 cols=80>'.$mycurrentprod[0]->description.'</textarea>';
            echo '<img src="'.$mycurrentprod[0]->imgurl.'" style="float:right; max-width:160px; max-height:160px; " />';
            echo '</td></tr>';

            // URL
            echo '<tr valign="top"><th scope="row">'.__('Reference URL', 'noshop').'</th>';
            echo '<td><input type="text" name="produrl" value="'.$mycurrentprod[0]->url.'" size="80"></input>';
            echo '</td></tr>';

            echo '</table>';


            $mycurrentspecs = $wpdb->get_results( "SELECT * FROM " . $subtable_name . " WHERE product_id=".intval($options['selectproduct']) );
            echo '<table class="form-table">';
            foreach ($mycurrentspecs as $mycurrentspecs) {
                // Specifications
                echo "<tr valign=\"top\"><th scope=\"row\">".__('Specification', 'noshop')."</th>";
                echo "<td>";
                echo "<input type=\"text\" name=\"spec[".$mycurrentspecs->id."][title]\" value=\"".$mycurrentspecs->spectitle."\" size=\"15\" />";
                echo "<input type=\"text\" name=\"spec[".$mycurrentspecs->id."][value]\" value=\"".$mycurrentspecs->specvalue."\" size=\"35\" />";
                echo "<input type=\"hidden\" name=\"spec[".$mycurrentspecs->id."][id]\" value=\"".$mycurrentspecs->id."\" />";
                echo "</td></tr>";
            }
            echo "<tr valign=\"top\"><th scope=\"row\"></th>";
            echo "<td>";
            echo "<b>Erase all text from the specification lines you wish to delete, and then press Update Product.</b>";
            echo "</td></tr>";
            echo '</table>';

            // SUBMIT
            echo '<input type="hidden" name="noshop_submit" value="productupdate"></input>';
            echo '<p class="submit"><input type="submit" value="'.__('Update Product &raquo;', 'noshop').'"></input></p>';
            echo "</div>";
            echo '</form>';


            // ////////////////////////////////////////////////////////// //
            // Delete Product
            // ////////////////////////////////////////////////////////// //

            // $product_count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM " . $table_name));
            echo'<form method="post" action="'.get_bloginfo('wpurl').'/wp-admin/options-general.php?page=noshop/noshop.php">';
            //echo '<div class="wrap">';
            echo '<input type="hidden" name="noshop_submit" value="productdelete"></input>';
            echo '<p class="submit"><input type="submit" value="'.__('Delete the above product &raquo;', 'noshop').'"></input></p>';
            //echo "</div>";
            echo '</form>';




            // ////////////////////////////////////////////////////////// //
            // Product specs creation
            // ////////////////////////////////////////////////////////// //

            $product_count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM " . $table_name));
            echo'<form method="post" action="'.get_bloginfo('wpurl').'/wp-admin/options-general.php?page=noshop/noshop.php">';
            //echo '<div class="wrap">';
            echo '<input type="hidden" name="noshop_submit" value="productaddspec"></input>';
            echo '<p class="submit"><input type="submit" value="'.__('Add a new specification line to above product &raquo;', 'noshop').'"></input></p>';
            //echo "</div>";
            echo '</form>';


            echo '<hr />';

            // ////////////////////////////////////////////////////////// //
            // Using Information
            // ////////////////////////////////////////////////////////// //

            echo '<div class="wrap">';
            echo '	<p><h2>'.__('Using noshop', 'noshop').'</h2></p>';
            echo '	<p>Create a Page or Post for your "Shopping Cart".</p>';
            echo '	<p>Add the tag [NoShop &lt;category&gt;] to the page or post to show the list of items in that category.</p>';
            echo '	<p>The currently selected product will show up if you use the tag <b><i>[NoShop '.$mycurrentprod[0]->category.']</i></b> :-)</p>';
            echo '	<p>Adding several tags after each other, like [NoShop Boats] followed by [NoShop MonoCycles] will show the two lists after each other.</p>';
            echo '</div>';

            echo '<div class="wrap">';
            echo '	<p><h2>'.__('Help!', 'noshop').'</h2></p>';
            echo '	<p>If you find errors, have suggestions or simple need more help, feel free to visit the plugin support page at ';
            echo '	<a href="http://wordpress.org/support/plugin/noshop">http://wordpress.org/support/plugin/noshop.</a>';
            echo '</div>';

            
            echo '<hr />';

            // ////////////////////////////////////////////////////////// //
            // Options Form
            // ////////////////////////////////////////////////////////// //

            echo'<form method="post" action="'.get_bloginfo('wpurl').'/wp-admin/options-general.php?page=noshop/noshop.php">';
            echo '<div class="wrap"><p><h2>'.__('Plugin Options', 'noshop').'</h2></p>';
            // settings
            echo '<table class="form-table">';
            // width
            echo '<tr valign="top"><th scope="row">'.__('Product Display Width', 'noshop').'</th>';
            echo '<td><input type="text" name="width" value="'.$options['width'].'" size="10"></input>&nbsp;'.__('Maximum width of pictures in pixels', 'noshop').'</td></tr>';
            // wptouchwidth
            echo '<tr valign="top"><th scope="row">'.__('Product Display Width in WPtouch', 'noshop').'</th>';
            echo '<td><input type="text" name="wptouchwidth" value="'.$options['wptouchwidth'].'" size="10"></input>&nbsp;'.__('Maximum width of pictures in WPtouch mode', 'noshop').'</td></tr>';
            // default picture
            echo '<tr valign="top"><th scope="row">'.__('Default Image URL', 'noshop').'</th>';
            echo '<td><input type="text" name="defimg" value="'.$options['defimg'].'" size="64"></input>&nbsp;'.__('Default image to show if product has no image.', 'noshop').'</td></tr>';
            // spectitlewidth
            echo '<tr valign="top"><th scope="row">'.__('Width of value headers', 'noshop').'</th>';
            echo '<td><input type="text" name="spectitlewidth" value="'.$options['spectitlewidth'].'" size="10"></input>&nbsp;'.__('Maximum width of value headers under product description', 'noshop').'</td></tr>';
            // customcss
            echo '<tr valign="top"><th scope="row">'.__('Custom CSS', 'noshop').'</th>';
            echo '<td><input type="text" name="noshopcss" value="'.$options['noshopcss'].'" size="64"></input>&nbsp;'.__('Path to custom CSS file, blank results in using default noshop.css', 'noshop').'</td></tr>';
            // errors
            echo '<tr valign="top"><th scope="row">'.__('Show DB Errors', 'noshop').'</th>';
            if (isset($options['visibleerrors']) && !empty($options['visibleerrors'])) {
                $checked = " checked=\"checked\"";
            } else {
                $checked = "";
            }
            echo '<td><input type="checkbox" name="visibleerrors" value="on"'.$checked.' />&nbsp;'.__('Attempt to show all database related errors.', 'noshop').'</td></tr>';
            echo '</table>';

            // SUBMIT
            echo '<input type="hidden" name="noshop_submit" value="options"></input>';
            echo '<p class="submit"><input type="submit" value="'.__('Update Options &raquo;', 'noshop').'"></input></p>';
            echo "</div>";
            echo '</form>';

            echo '<hr />';

            echo '

				<p>Please consider a donation, it\'ll all go to food, warmth and toys for my children.</p>

                <!-- I am married to a Princess :-) Her name is Kianna Angelo, and I love her endlessly. -->
                
				<form action="https://www.paypal.com/cgi-bin/webscr" method="post">
					<input type="hidden" name="cmd" value="_s-xclick">
					<input type="hidden" name="hosted_button_id" value="D9VGL9C245FRQ">
					<input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_donate_SM.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
					<img alt="" border="0" src="https://www.paypalobjects.com/en_US/i/scr/pixel.gif" width="1" height="1">
				</form>
				
				';

        } // End OptionPage()

        public static function PluginMenu() {
            add_options_page('NoShop Plugin Options', 'No Shop!', 'manage_options', 'noshop/noshop.php', 'NoShop::OptionPage');
        }


    } //End Class NoShop

} // End if (!class_exists("NoShop"))


// ////////////////////////////////////////////////////////// //
// Kick-off: Load instance if class created correctly
// ////////////////////////////////////////////////////////// //

if (class_exists("NoShop")) {
    $noshop_plugin = new NoShop();
}

?>