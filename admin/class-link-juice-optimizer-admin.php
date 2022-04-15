<?php

use Carbon_Fields\Container;
use Carbon_Fields\Field;

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://www.fedegomez.es/
 * @since      2.0.1
 *
 * @package    Link_Juice_Optimizer
 * @subpackage Link_Juice_Optimizer/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Link_Juice_Optimizer
 * @subpackage Link_Juice_Optimizer/admin
 * @author     Fede Gómez <hola@fedegomez.es>
 */
class Link_Juice_Optimizer_Admin
{
	/**
	 * The ID of this plugin.
	 *
	 * @since    2.0.1
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    2.0.1
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    2.0.1
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct($plugin_name, $version)
	{

		$this->plugin_name = $plugin_name;
		$this->version = $version;
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    2.0.1
	 */
	public function enqueue_styles()
	{
		wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/link-juice-optimizer-admin.css', array(), $this->version, 'all');
	}

	/**
	 * Carga la librería Carbon Fields
	 *
	 * @return void
	 */
	public function ljo_crb_load()
	{
		require_once plugin_dir_path(dirname(__FILE__)) . 'vendor/autoload.php';
		\Carbon_Fields\Carbon_Fields::boot();
	}

	/**
	 * Crea los custom fields para las opciones del plugin
	 *
	 * @return void
	 */
	public function ljo_crb_attach_fields()
	{
		$file_to_block = content_url() . '/plugins/' . plugin_dir_path(plugin_basename(plugin_dir_path(__DIR__) . $this->plugin_name . '.php')) . 'public/js/link-juice-optimizer.js';
		$file_to_block = substr($file_to_block, strlen(get_site_url()));
		// página de opciones
		$container = Container::make('theme_options', 'Link Juice Optimizer')
			->where('current_user_capability', '=', 'manage_options')
			->set_page_parent('options-general.php')
			->add_tab(__('Ajustes generales', 'link-juice-optimizer'), array(
				Field::make('html', 'ljo_cervezas_1')
					->set_html('<h3 style="text-align: center">' . __('Apoya mi trabajo', 'link-juice-optimizer') . '</h3><p style="text-align: center;font-weight: bold;font-size: 1.1em">' . __('Las ideas para desarrollar herramientas gratuitas como este plugin se me ocurren cuando estoy disfrutando de una buena cerveza... Ahí lo dejo ;)', 'link-juice-optimizer') . '</p><p style="text-align: center"><style>.bmc-button img{width: 27px !important;margin-bottom: 1px !important;box-shadow: none !important;border: none !important;vertical-align: middle !important;}.bmc-button{line-height: 36px !important;height:37px !important;text-decoration: none !important;display:inline-flex !important;color:#ffffff !important;background-color:#FF813F !important;border-radius: 3px !important;border: 1px solid transparent !important;padding: 0px 9px !important;font-size: 17px !important;letter-spacing:-0.08px !important;box-shadow: 0px 1px 2px rgba(190, 190, 190, 0.5) !important;-webkit-box-shadow: 0px 1px 2px 2px rgba(190, 190, 190, 0.5) !important;margin: 0 auto !important;font-family:\'Lato\', sans-serif !important;-webkit-box-sizing: border-box !important;box-sizing: border-box !important;-o-transition: 0.3s all linear !important;-webkit-transition: 0.3s all linear !important;-moz-transition: 0.3s all linear !important;-ms-transition: 0.3s all linear !important;transition: 0.3s all linear !important;}.bmc-button:hover, .bmc-button:active, .bmc-button:focus {-webkit-box-shadow: 0px 1px 2px 2px rgba(190, 190, 190, 0.5) !important;text-decoration: none !important;box-shadow: 0px 1px 2px 2px rgba(190, 190, 190, 0.5) !important;opacity: 0.85 !important;color:#ffffff !important;}</style><link href="https://fonts.googleapis.com/css?family=Lato&subset=latin,latin-ext" rel="stylesheet"><a class="bmc-button" target="_blank" href="https://www.buymeacoffee.com/fedegomez"><img src="https://bmc-cdn.nyc3.digitaloceanspaces.com/BMC-button-images/BMC-btn-logo.svg" alt="' . __('Invítame a unas cervezas', 'link-juice-optimizer') . '"><span style="margin-left:5px">' . __('Invítame a unas cervezas', 'link-juice-optimizer') . '</span></a></p>'),
				Field::make('html', 'ljo_aviso_robots_1')
					->set_html('<p style="text-align:center;color:red">' . __('Recuerda que es recomendable añadir esta línea al archivo robots.txt', 'link-juice-optimizer') . ':</p><p style="text-align:center">Disallow: ' . $file_to_block . '</p>'),
				Field::make('text', 'ljo_clase', __('Clase CSS personalizada que utilizará el plugin', 'link-juice-optimizer'))
					->set_default_value('ljoptimizer')
					->set_required(true)
					->set_help_text(__('¡Cuidado! Los enlaces que utilizan esta clase dejarán de ofuscarse si la modificas (aunque puedes reemplazar en la base de datos)', 'link-juice-optimizer')),
				Field::make('select', 'ljo_tipo_enlace', __('Tipo de enlace generado', 'link-juice-optimizer'))
					->set_options(array(
						'nofollow' => __('Añadir atributo rel="nofollow"', 'link-juice-optimizer'),
						'href' => __('Enlace normal (<a>) sin atributo href', 'link-juice-optimizer'),
						'onclick' => __('Con atributo onclick visible', 'link-juice-optimizer'),
						'base64' => __('Ofuscado en base64', 'link-juice-optimizer')
					)),
				Field::make('textarea', 'ljo_elementos_extra', __('Elementos extra', 'link-juice-optimizer'))
					->set_rows(8)
					->set_help_text(__("Incluye los casos que quieras ofuscar:<br><ul><li>ID o clases CSS de enlaces o de sus elementos padre como &lt;div&gt;, &lt;p&gt;, &lt;ul&gt; (#mi-id, .mi-clase,...)</li><li>URL de destino individuales (https://dominio.com/destino/)</li><li>Todos los enlaces pertenecientes a un directorio determinado mediante un <strong>*</strong> (https://dominio.com/directorio/<strong>*</strong>)</li><li>Todos los enlaces de destino que coincidan con una raíz, también mediante <strong>*</strong> (https://dominio.com/palabra<strong>*</strong>)</li><li>Parámetros incluidos en URL (?parametro)</li></ul>Inserta un elemento por línea.", 'link-juice-optimizer'))
					->set_attribute('placeholder', __("#mi-id\n.mi-clase\nhttps://dominio.com/destino/\nhttps://dominio.com/directorio/*\nhttps://dominio.com/palabra*\n?parametro", 'link-juice-optimizer')),
				Field::make('textarea', 'ljo_css', __('Código CSS', 'link-juice-optimizer'))
					->set_rows(8)
					->set_default_value(__("a.ljoptimizer,\nspan.ljoptimizer {\n\tcursor: pointer; /* añade el cursor que simula un enlace */\n}", 'link-juice-optimizer')),
				Field::make('text', 'ljo_hook_priority', __('Prioridad de carga del hook', 'link-juice-optimizer'))
					->set_default_value(PHP_INT_MAX)
					->set_help_text(__('¡Cuidado! No es necesario que modifiques este valor, deja el valor por defecto ', 'link-juice-optimizer') . PHP_INT_MAX)
					->set_attribute('max', PHP_INT_MAX)
					->set_attribute('min', '0')
					->set_attribute('step', '1')
					->set_attribute('type', 'number')
			));

		if (class_exists('woocommerce')) {
			$container->add_tab('WooCommerce', array(
				Field::make('html', 'ljo_cervezas_2')
					->set_html('<h3 style="text-align: center">' . __('Apoya mi trabajo', 'link-juice-optimizer') . '</h3><p style="text-align: center;font-weight: bold;font-size: 1.1em">' . __('Las ideas para desarrollar herramientas gratuitas como este plugin se me ocurren cuando estoy disfrutando de una buena cerveza... Ahí lo dejo ;)', 'link-juice-optimizer') . '</p><p style="text-align: center"><style>.bmc-button img{width: 27px !important;margin-bottom: 1px !important;box-shadow: none !important;border: none !important;vertical-align: middle !important;}.bmc-button{line-height: 36px !important;height:37px !important;text-decoration: none !important;display:inline-flex !important;color:#ffffff !important;background-color:#FF813F !important;border-radius: 3px !important;border: 1px solid transparent !important;padding: 0px 9px !important;font-size: 17px !important;letter-spacing:-0.08px !important;box-shadow: 0px 1px 2px rgba(190, 190, 190, 0.5) !important;-webkit-box-shadow: 0px 1px 2px 2px rgba(190, 190, 190, 0.5) !important;margin: 0 auto !important;font-family:\'Lato\', sans-serif !important;-webkit-box-sizing: border-box !important;box-sizing: border-box !important;-o-transition: 0.3s all linear !important;-webkit-transition: 0.3s all linear !important;-moz-transition: 0.3s all linear !important;-ms-transition: 0.3s all linear !important;transition: 0.3s all linear !important;}.bmc-button:hover, .bmc-button:active, .bmc-button:focus {-webkit-box-shadow: 0px 1px 2px 2px rgba(190, 190, 190, 0.5) !important;text-decoration: none !important;box-shadow: 0px 1px 2px 2px rgba(190, 190, 190, 0.5) !important;opacity: 0.85 !important;color:#ffffff !important;}</style><link href="https://fonts.googleapis.com/css?family=Lato&subset=latin,latin-ext" rel="stylesheet"><a class="bmc-button" target="_blank" href="https://www.buymeacoffee.com/fedegomez"><img src="https://bmc-cdn.nyc3.digitaloceanspaces.com/BMC-button-images/BMC-btn-logo.svg" alt="' . __('Invítame a unas cervezas', 'link-juice-optimizer') . '"><span style="margin-left:5px">' . __('Invítame a unas cervezas', 'link-juice-optimizer') . '</span></a></p>'),
				Field::make('html', 'ljo_aviso_robots_2')
					->set_html('<p style="text-align:center;color:red">' . __('Recuerda que es recomendable añadir esta línea al archivo robots.txt', 'link-juice-optimizer') . ':</p><p style="text-align:center">Disallow: ' . $file_to_block . '</p>'),
				Field::make('checkbox', 'ljo_subcategorias_default', __('Ofuscar enlaces de categorías', 'link-juice-optimizer'))
					->set_help_text(__('Si marcas esta casilla todos los enlaces de categorías serán ofuscados de manera predeterminada (solo funciona en el loop).', 'link-juice-optimizer')),
				Field::make('checkbox', 'ljo_productos_default', __('Ofuscar enlaces de productos', 'link-juice-optimizer'))
					->set_help_text(__('Si marcas esta casilla todos los enlaces de productos serán ofuscados de manera predeterminada (solo funciona en el loop).', 'link-juice-optimizer')),
				Field::make('checkbox', 'ljo_addtocart_default', __('Ofuscar enlaces (botones) "Añadir al carrito"', 'link-juice-optimizer'))
					->set_help_text(__('Si marcas esta casilla todos los enlaces (botones) para añadir un producto al carrito serán ofuscados (ajuste global).', 'link-juice-optimizer')),
			));
		}

		// ofuscar enlaces en menús
		Container::make('nav_menu_item', __('Ajustes de menú', 'link-juice-optimizer'))
			->add_fields(array(
				Field::make('checkbox', 'ljo_item_menu', __('Ofuscar este enlace', 'link-juice-optimizer')),
			));

		// ofuscar enlaces en categorías de WooCommerce
		Container::make('term_meta', __('Propiedades de categoría', 'link-juice-optimizer'))
			->where('term_taxonomy', '=', 'product_cat')
			->add_fields(array(
				Field::make('select', 'ljo_categoria_single', __('Ofuscar el enlace a esta categoría', 'link-juice-optimizer'))
					->set_options(array(
						'0' => __('Predeterminado', 'link-juice-optimizer'),
						'1' => __('Sí', 'link-juice-optimizer'),
						'2' => __('No', 'link-juice-optimizer')
					)),
			));

		// ofuscar enlace de producto
		Container::make('post_meta', __('Enlace de producto', 'link-juice-optimizer'))
			->where('post_type', '=', 'product')
			->set_context('side')
			->add_fields(array(
				Field::make('select', 'ljo_producto_single', __('Ofuscar el enlace a este producto', 'link-juice-optimizer'))
					->set_options(array(
						'0' => __('Predeterminado', 'link-juice-optimizer'),
						'1' => __('Sí', 'link-juice-optimizer'),
						'2' => __('No', 'link-juice-optimizer')
					))
			));
	}

	/**
	 * Añade un checkbox a los widgets para poder ofuscar los enlaces que contienen
	 *
	 * @param WP_Widget $widget
	 * @param null $return
	 * @param array $instance
	 * @return void
	 */
	public function ljo_show_option_in_widget($widget, $return, $instance)
	{
		$ljo_widget_checkbox = isset($instance['ljo_widget_checkbox']) ? $instance['ljo_widget_checkbox'] : ''; ?>
		<p>
			<input class="checkbox" type="checkbox" id="<?php echo esc_attr($widget->get_field_id('ljo_widget_checkbox')); ?>" name="<?php echo esc_attr($widget->get_field_name('ljo_widget_checkbox')); ?>" <?php checked(true, $ljo_widget_checkbox); ?> />
			<label for="<?php echo esc_attr($widget->get_field_id('ljo_widget_checkbox')); ?>"><?php echo __('Ofuscar enlaces en este widget', 'link-juice-optimizer') ?></label>
		</p>
	<?php

	}

	/**
	 * Guarda el estado del checkbox del widget
	 *
	 * @param array $instance
	 * @param array $new_instance
	 * @return array
	 */
	public function ljo_save_option_in_widget($instance, $new_instance)
	{
		if (!empty($new_instance['ljo_widget_checkbox'])) {
			$new_instance['ljo_widget_checkbox'] = 1;
		}

		return $new_instance;
	}

	/**
	 * Añade un checkbox en el botón Insertar/Editar link pque permite ofuscar el enlace
	 */
	public function ljo_add_checkbox_in_insert_link()
	{
		$ljo_clase = $this->get_ljo_clase();
	?>
		<script>
			var originalWpLink;
			// Ensure both TinyMCE, underscores and wpLink are initialized
			if (typeof tinymce !== 'undefined' && typeof _ !== 'undefined' && typeof wpLink !== 'undefined') {
				// Ensure the #link-options div is present, because it's where we're appending our checkbox.
				if (tinymce.$('#link-options').length) {
					// Append our checkbox HTML to the #link-options div, which is already present in the DOM.
					tinymce.$('#link-options').append(<?php echo json_encode('<div class="add-ljo"><label><span></span><input type="checkbox" id="wp-add-ljo" /> ' . __('Ofuscar este enlace', 'link-juice-optimizer') . '</label></div>'); ?>);
					// Clone the original wpLink object so we retain access to some functions.
					originalWpLink = _.clone(wpLink);
					wpLink.addLjo = tinymce.$('#wp-add-ljo');
					// Override the original wpLink object to include our custom functions.
					wpLink = _.extend(wpLink, {
						/**
						 * Fetch attributes for the generated link based on
						 * the link editor form properties.
						 *
						 * In this case, we're calling the original getAttrs()
						 * function, and then including our own behavior.
						 */
						getAttrs: function() {
							var attrs = originalWpLink.getAttrs();
							attrs.class = wpLink.addLjo.prop('checked') ? '<?php echo esc_attr($ljo_clase); ?>' : null;
							return attrs;
						},
						/**
						 * Build the link's HTML based on attrs when inserting
						 * into the text editor.
						 *
						 * In this case, we're completely overriding the existing
						 * function.
						 */
						buildHtml: function(attrs) {
							var html = '<a href="' + attrs.href + '"';
							if (attrs.target) {
								html += ' target="' + attrs.target + '"';
							}
							if (attrs.class) {
								html += ' class="' + attrs.class + '"';
							}
							return html + '>';
						},
						/**
						 * Set the value of our checkbox based on the presence
						 * of the class='ljoptimizer' (or custom class) link attribute.
						 *
						 * In this case, we're calling the original mceRefresh()
						 * function, then including our own behavior
						 */
						mceRefresh: function(searchStr, text) {
							chkbox = document.getElementById("wp-add-ljo");
							if (chkbox !== null) {
								chkbox.checked = false;
							}
							originalWpLink.mceRefresh(searchStr, text);
							var editor = window.tinymce.get(window.wpActiveEditor)
							if (typeof editor !== 'undefined' && !editor.isHidden()) {
								var linkNode = editor.dom.getParent(editor.selection.getNode(), 'a[href]');
								if (linkNode) {
									wpLink.addLjo.prop('checked', '<?php echo esc_attr($ljo_clase); ?>' === editor.dom.getAttrib(linkNode, 'class'));
								}
							}
						}
					});
				}
			}
		</script>
<?php
	}

	/**
	 * Devuelve las clase CSS a utilizar para ofuscar los enlaces
	 *
	 * @return string
	 */
	private function get_ljo_clase()
	{
		return (trim(carbon_get_theme_option('ljo_clase')) != '' ? trim(carbon_get_theme_option('ljo_clase')) : 'ljoptimizer');
	}

	/**
	 * Añade un enlace a la configuración del plugin en la página de plugins instalados
	 *
	 * @param array $links
	 * @return array
	 */
	public function ljo_add_plugin_page_settings_link($links)
	{
		$links[] = '<a href="' .
			admin_url('options-general.php?page=crb_carbon_fields_container_link_juice_optimizer.php') .
			'">' . __('Ajustes', 'link-juice-optimizer') . '</a>';
		return $links;
	}
}
