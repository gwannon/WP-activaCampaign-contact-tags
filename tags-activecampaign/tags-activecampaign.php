<?php

/**
 * Plugin Name: tagsActiveCampaigns
 * Plugin URI:  https://www.enutt.net/
 * Description: Sistema para meter tags a los usuarios de AC que esten más de 30 segundos en una página
 * Version:     1.0
 * Author:      Enutt S.L.
 * Author URI:  https://www.enutt.net/
 * License:     GNU General Public License v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: tags-activecampaign
 *
 * PHP 7.3
 * WordPress 5.5.3
 */

//Variables globales
define('AC_WAITING_TIME', get_option("_tags_activecampaign_waiting_time")); 
define('AC_API_DOMAIN', get_option("_tags_activecampaign_api_domain")); 
define('AC_API_TOKEN', get_option("_tags_activecampaign_api_token"));
define('AC_COOKIE_DAYS', get_option("_tags_activecampaign_cookie_days"));
define('AC_DEBUG', false);

//Cargamos las funciones que crean las páginas en el WP-ADMIN
require_once(dirname(__FILE__)."/admin.php");


//Cookies -------------------
add_action('init', 'tagsActiveCampaignAddCookies');
function tagsActiveCampaignAddCookies(){
 if(isset($_REQUEST['contact_id']) && AC_COOKIE_DAYS > 0) {
  setcookie("contact_id", $_REQUEST['contact_id'], time()+(3600 * 24 * AC_COOKIE_DAYS), "/");
 }
}

//Footer ------------------
add_action('wp_footer', 'tagsActiveCampaignAddFooter', 5); 
function tagsActiveCampaignAddFooter() { 
    global $post;

    $tags = array();
    $pages = array();
    foreach(explode("\n", chop(get_option("_tags_activecampaign_tags"))) as $line) {
      $temp = explode(",", $line);
      $key = $temp[0];
      $pages[] = $temp[0];
      unset($temp[0]);
      $tags[$key] = array_values(array_map('trim', $temp));
    }

    ob_start();    
    if (is_page($pages)) { ?>
    <script>
      var params = new window.URLSearchParams(window.location.search);
      setTimeout(function () {
        var tags = Array("<?php $page_id = get_the_id(); echo implode('","', $tags[$page_id]); ?>");
        waitEvent(tags);
      }, <?php echo (AC_WAITING_TIME * 1000); ?>);
      function waitEvent(tag) {
        //Hacemos una llamada con los datos
        jQuery.ajax({
          type: "GET",
          url: "<?php echo admin_url('admin-ajax.php'); ?>",
          dataType: 'json',
          data: (
            { 
              action: 'tags_activecampaign', 
              contact_id: params.get('contact_id'),
              tag: tag
            }
          ),
          success: function(data){
            console.log(data);
          },
          error: function(data) {
            console.log("Error!"); //TODO: mensaje de error
            console.log(data);
            return false;
          }
        });
      }
    </script>    
  <?php }
  $html = ob_get_clean(); 
  echo  $html;
}

//AJAX ----------------------
function tagsActiveCampaignAjax() {

  $my_tags = $_REQUEST['tag'];

  if(AC_DEBUG) $response['request'] = $_REQUEST;
  if(AC_DEBUG) $response['cookie'] = $_COOKIE;

  /*print_r ($_REQUEST);
  print_r ($_COOKIE);*/
  $my_contact_id = (isset($_REQUEST['contact_id']) && $_REQUEST['contact_id'] > 0 ? $_REQUEST['contact_id'] : $_COOKIE['contact_id']);
  if(AC_DEBUG) $response['contact_id'] = $my_contact_id;

  if ($my_contact_id > 0) {
    foreach ($my_tags as $my_tag) {
      $my_tag_id = 0;
      
      //Buscamos nuestra etiqueta ----------------------------------------------------
      //------------------------------------------------------------------------------
      $curl = curl_init();
      curl_setopt($curl, CURLOPT_URL, AC_API_DOMAIN."/api/3/tags?search=".$my_tag);
      curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($curl, CURLOPT_HTTPHEADER, array('Api-Token: '.AC_API_TOKEN));
      $result = curl_exec($curl);
      $tags  = json_decode($result)->tags;
      curl_close($curl);
      if(AC_DEBUG) $response['search_tags'] = json_decode($result);
      
      //Sacamos el id de la tag   
      foreach($tags as $tag) {
        if($tag->tag == $my_tag) {
          $my_tag_id = $tag->id;
          break;
        }
      }
      
      //Si no existe la etiqueta la creamos
      if ($my_tag_id == 0) {
        $post = '{
          "tag":{
            "tag": "'.$my_tag.'",
            "tagType": "contact",
            "description": "Tag creada desde API"
          }
        }';
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, AC_API_DOMAIN."/api/3/tags");
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array('Api-Token: '.AC_API_TOKEN));
        curl_setopt($curl, CURLOPT_POSTFIELDS, $post);
        $result = curl_exec($curl);
        if(AC_DEBUG) $response['create_tags'] = json_decode($result);
        $my_tag_id = json_decode($result)->tag->id;
        curl_close($curl);
        $response['message'][] = array("tag" => $my_tag, "status" => "CREATE");
      }
      
      //Asignamos el tag al contacto -------------------------------------------------
      //------------------------------------------------------------------------------
      $post = '{
        "contactTag": {
          "contact": "'.$my_contact_id.'",
          "tag": "'.$my_tag_id.'"
        }
      }';
      $curl = curl_init();
      curl_setopt($curl, CURLOPT_URL, AC_API_DOMAIN."/api/3/contactTags");
      curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($curl, CURLOPT_POSTFIELDS, $post);
      curl_setopt($curl, CURLOPT_HTTPHEADER, array('Api-Token: '.AC_API_TOKEN));
      $result = curl_exec($curl);
      if(AC_DEBUG) $response['contact_tags'] = json_decode($result);
      //echo json_encode($result);
      $response['message'][] = array("tag" => $my_tag, "status" => "OK");
    }
   } else {
    header("HTTP/1.1 404 Not Found");
    $response['message'][] = array("text" => "NO contact_id", "status" => "ERROR");
   }
  echo json_encode($response);
  wp_die();
}

add_action('wp_ajax_tags_activecampaign', 'tagsActiveCampaignAjax');
add_action('wp_ajax_nopriv_tags_activecampaign', 'tagsActiveCampaignAjax');
