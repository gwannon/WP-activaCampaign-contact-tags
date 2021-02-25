<?php

//ADMIN -----------------------------------------

add_action( 'admin_menu', 'tagsActiveCampaignPluginMenu' );
function tagsActiveCampaignPluginMenu() {
	add_options_page( __('Administración Tags AC', 'tags-activecampaign'), __('Tags AC', 'tags-activecampaign'), 'manage_options', 'tags-activecampaign', 'tagsActiveCampaignPageSettings');
}

function tagsActiveCampaignPageSettings() { 
	//echo "<pre>"; print_r($_REQUEST); echo "</pre>";
	if(isset($_REQUEST['send']) && $_REQUEST['send'] != '') { 
		update_option('_tags_activecampaign_api_token', $_POST['_tags_activecampaign_api_token']);
		update_option('_tags_activecampaign_api_domain', $_POST['_tags_activecampaign_api_domain']);
		update_option('_tags_activecampaign_waiting_time', $_POST['_tags_activecampaign_waiting_time']);
		update_option('_tags_activecampaign_cookie_days', $_POST['_tags_activecampaign_cookie_days']);
		update_option('_tags_activecampaign_tags', $_POST['_tags_activecampaign_tags']);
		?><p style="border: 1px solid green; color: green; text-align: center;"><?php _e("Datos guardados correctamente.", 'tags-activecampaign'); ?></p><?php
	} ?>
	<form method="post">
		<?php $settings = array( 'media_buttons' => true, 'quicktags' => true, 'textarea_rows' => 15 ); ?>
		<h1><?php _e("Configuración de la conexión con la API de AC", 'tags-activecampaign'); ?></h1>
		<h2><?php _e("Dominio de la API", 'tags-activecampaign'); ?>:</h2>
		<input type="text" name="_tags_activecampaign_api_domain" value="<?php echo get_option("_tags_activecampaign_api_domain"); ?>" style="width: 100%; max-width: 500px;"/><br/><br/>
		<h2><?php _e("Token de la API", 'tags-activecampaign'); ?>:</h2>
		<input type="text" name="_tags_activecampaign_api_token" value="<?php echo get_option("_tags_activecampaign_api_token"); ?>" style="width: 100%; max-width: 500px;"/><br/><br/>

		
		<h1><?php _e("Configuración de las etiquetas", 'tags-activecampaign'); ?></h1>
		<h2><?php _e("Tiempo de espera (en segundos)", 'tags-activecampaign'); ?>:</h2>
		<input type="number" name="_tags_activecampaign_waiting_time" value="<?php echo get_option("_tags_activecampaign_waiting_time"); ?>" /><br/>

		<h2><?php _e("Duración de cookie (en días)", 'tags-activecampaign'); ?>:</h2>
		<input type="number" name="_tags_activecampaign_cookie_days" value="<?php echo get_option("_tags_activecampaign_cookie_days"); ?>"><br/><br/>
		<p><?php _e("0 desactiva la gneración de la cookie .", 'tags-activecampaign'); ?></p><br/>

		<h2><?php _e("Etiquetas", 'tags-activecampaign'); ?>:</h2>
		<textarea name="_tags_activecampaign_tags" rows="10" style="width: 100%;"><?php echo get_option("_tags_activecampaign_tags"); ?></textarea><br/>
		<p><?php _e("Una linea por cada página con el formato <b>'page_id,tag-1,tag-2,tag-3'</b>. Si queremos que en la página 127 meta los tags 'interes-ayudas' y 'interes-ayudas-comercio', debemos meter la linea <b>'127,interes-ayudas,interes-ayudas-comercio'</b>.", 'tags-activecampaign'); ?></p><br/><br/>
		<input type="submit" name="send" class="button button-primary" value="<?php _e("Guardar"); ?>" />
	</form>
	<h2><?php _e("Modo de uso", 'tags-activecampaign'); ?>:</h2>
	<p><?php _e("Los enalces que queramos rastrear deberán tener en Active Campaign el parametro contact_id con el valor de la ID de contacto del usuario.</p><p>Por ejemplo:<br/>https://midominio.com/?contact_id=%CONTACTID%", 'tags-activecampaign'); ?></p>

	<?php
}