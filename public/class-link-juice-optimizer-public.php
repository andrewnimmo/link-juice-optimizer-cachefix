<?php

include_once(plugin_dir_path(dirname(__FILE__)) . 'vendor/simplehtmldom_1_9/ljo_simple_html_dom.php');

/*
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
*/

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://www.fedegomez.es/
 * @since      2.0.1
 *
 * @package    Link_Juice_Optimizer
 * @subpackage Link_Juice_Optimizer/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Link_Juice_Optimizer
 * @subpackage Link_Juice_Optimizer/public
 * @author     Fede Gómez <hola@fedegomez.es>
 */
class Link_Juice_Optimizer_Public
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
	 * Tipo de ofuscación: base64, onclick, nofollow
	 *
	 * @var string $tipo_enlace
	 */
	private $tipo_enlace;

	/**
	 * Clase CSS a utilizar en los enlaces
	 *
	 * @var string $ljo_clase
	 */
	private $ljo_clase;

	/**
	 * Ofuscar subcategorias de WooCommerce por defecto
	 *
	 * @var bool $subcategorias_default
	 */
	private $subcategorias_default;

	/**
	 * Ofuscar productos de WooCommerce por defecto
	 *
	 * @var bool $productos_default
	 */
	private $productos_default;

	/**
	 * Ofuscar enlace "añadir al carrito" por defecto
	 *
	 * @var bool $addtocart_default
	 */
	private $addtocart_default;

	/**
	 * Elementos extra a ofuscar antes de devolver el output
	 *
	 * @var array $elementos_extra
	 */
	private $elementos_extra;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    2.0.1
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct($plugin_name, $version)
	{
		$this->plugin_name = $plugin_name;
		$this->version = $version;
		$this->tipo_enlace = '';
		$this->ljo_clase = '';
		$this->subcategorias_default = '';
		$this->productos_default = '';
		$this->addtocart_default = '';
		$this->elementos_extra = '';
	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    2.0.1
	 */
	public function enqueue_scripts()
	{
		wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/link-juice-optimizer.js', array('jquery'), $this->version, false);
		$data = array(
			'ljo_clase' => $this->get_ljo_clase()
		);
		wp_localize_script($this->plugin_name, 'php_vars', $data);
	}

	/**
	 * Reemplaza los links en contenido y widgets
	 *
	 * @param string $content
	 * @param boolean $is_widget
	 * @return void
	 */
	public function ljo_content($content, $is_controlled_by_class = false)
	{
		$enlacesOriginales = [];
		$enlacesReemplazo = [];

		if ($is_controlled_by_class) {
			preg_match_all('~(?:\<a.*\>(.|\n)*\<\/a\>)~iU', $content, $matches);
		} else {
			preg_match_all('~(?:\<a.*' . $this->get_ljo_clase() . '.*\>(.|\n)*\<\/a\>)~iU', $content, $matches);
		}

		foreach ($matches[0] as $clave => $valor) {
			$enlace = $this->ljo_ofuscar_enlace($valor);
			$enlacesOriginales[] = $enlace['original'];
			$enlacesReemplazo[] = $enlace['ofuscado'];
		}

		foreach ($enlacesOriginales as $clave => $valor) {
			$content = str_replace($valor, $enlacesReemplazo[$clave], $content);
		}
		return $content;
	}

	/**
	 * codifica una cadena de caracteres en base64
	 *
	 * @param string $data
	 * @return string
	 */
	private function base64url_encode($data)
	{
		//return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
		return urlencode(base64_encode($data));
	}

	/**
	 * Captura la salida del sidebar y comprueba si los widgets tienen marcada la opción de ofuscar enlaces
	 *
	 * @param array $params
	 * @return array
	 */
	public function ljo_capture_sidebar($params)
	{
		global $wp_registered_widgets;
		$current_widget_id = $params[0]['widget_id'];
		$widget_obj = $wp_registered_widgets[$current_widget_id];
		$widget_opt = get_option($widget_obj['callback'][0]->option_name);
		$widget_num = $widget_obj['params'][0]['number'];
		if (isset($widget_opt[$widget_num]['ljo_widget_checkbox'])) {
			$wp_registered_widgets[$current_widget_id]['original_callback'] = $wp_registered_widgets[$current_widget_id]['callback'];
			$wp_registered_widgets[$current_widget_id]['callback'] = array($this, 'ljo_capture_widget');
		}
		return $params;
	}

	/**
	 * Callback del widget en el que se aplica la ofuscación en la salida
	 *
	 * @return void
	 */
	public function ljo_capture_widget()
	{
		global $wp_registered_widgets;
		$original_callback_params = func_get_args();
		$widget_id = $original_callback_params[0]['widget_id'];
		$original_callback = $wp_registered_widgets[$widget_id]['original_callback'];
		$wp_registered_widgets[$widget_id]['callback'] = $original_callback;
		$widget_id_base = $original_callback[0]->id_base;
		$sidebar_id = $original_callback_params[0]['id'];
		if (is_callable($original_callback)) {
			ob_start();
			call_user_func_array($original_callback, $original_callback_params);
			$widget_output = ob_get_clean();
			echo apply_filters('widget_output', $widget_output, $widget_id_base, $widget_id);
		}
	}

	/**
	 * filtro en el que se aplica la ofuscación
	 *
	 * @param [type] $widget_output
	 * @param [type] $widget_id_base
	 * @param [type] $widget_id
	 * @return void
	 */
	public function ljo_widget_output_filter($widget_output, $widget_id_base, $widget_id)
	{
		return $this->ljo_content($widget_output, true);
	}

	/**
	 * ofusca los enlaces del core de wp insertados en elementor
	 *
	 * @param [type] $content
	 * @param [type] $widget
	 * @return void
	 */
	public function ljo_elementor_widget($content, $widget)
	{
		$settings = $widget->get_settings();
		if (isset($settings['wp']['ljo_widget_checkbox']) && $settings['wp']['ljo_widget_checkbox'] == 1) {
			return $this->ljo_content($content, true);
		}
		return $content;
	}

	/**
	 * ofusca los enlaces en items de menú
	 *
	 * @param [type] $item_output
	 * @param [type] $item
	 * @param [type] $depth
	 * @param [type] $args
	 * @return void
	 */
	public function ljo_menu_item($item_output, $item, $depth, $args)
	{
		if (carbon_get_nav_menu_item_meta($item->ID, 'ljo_item_menu') === true || in_array($this->get_ljo_clase(), $item->classes)) {
			return $this->ljo_content($item_output, true);
		}
		return $item_output;
	}

	/**
	 * devuelve la clase css utilizada para los enlaces ofuscados
	 *
	 * @return string
	 */
	private function get_ljo_clase()
	{
		if ($this->ljo_clase == '') {
			$this->ljo_clase = (trim(carbon_get_theme_option('ljo_clase')) != '' ? trim(carbon_get_theme_option('ljo_clase')) : 'ljoptimizer');
		}
		return $this->ljo_clase;
	}

	/**
	 * devuelve como debe ser ofuscado el enlace (base64, onclick, nofollow)
	 *
	 * @return string
	 */
	private function get_ljo_tipo_enlace()
	{
		if ($this->tipo_enlace == '') {
			$this->tipo_enlace = (trim(carbon_get_theme_option('ljo_tipo_enlace')) != '' ? trim(carbon_get_theme_option('ljo_tipo_enlace')) : 'nofollow');
		}
		return $this->tipo_enlace;
	}

	/**
	 * devuelve si se ofuscan las subcategorias de WooCommerce por defecto
	 *
	 * @return bool
	 */
	private function get_ljo_subcategorias_default()
	{
		if ($this->subcategorias_default == '') {
			$this->subcategorias_default = carbon_get_theme_option('ljo_subcategorias_default');
		}
		return $this->subcategorias_default;
	}

	/**
	 * devuelve si se ofuscan los productos de WooCommerce por defecto
	 *
	 * @return bool
	 */
	private function get_ljo_productos_default()
	{
		if ($this->productos_default == '') {
			$this->productos_default = carbon_get_theme_option('ljo_productos_default');
		}
		return $this->productos_default;
	}

	/**
	 * devuelve si se ofuscan los enlaces 'añadir al carrito' de WooCommerce por defecto
	 *
	 * @return bool
	 */
	private function get_ljo_addtocart_default()
	{
		if ($this->addtocart_default == '') {
			$this->addtocart_default = carbon_get_theme_option('ljo_addtocart_default');
		}
		return $this->addtocart_default;
	}

	/**
	 * Función que recibe el enlace original y devuelve un array éste junto con el ofuscado
	 *
	 * @param string $valor
	 * @return array
	 */
	private function ljo_ofuscar_enlace($valor)
	{
		$ljo_clase = $this->get_ljo_clase();
		$tipo_enlace = $this->get_ljo_tipo_enlace();
		$inicio = strrpos($valor, "<a");
		$final = strrpos($valor, "</a>");
		$a = substr($valor, $inicio, ($final - $inicio) + 4);
		// si el enlace se construye en un script (elementor) se devuelve el original
		if (strpos($a, 'href="{{')) {
			$enlace['original'] = $a;
			$enlace['ofuscado'] = $a;
			return $enlace;
		}

		$html = ljo_str_get_html($a);
		$link = $html->find('a');
		$anchor_text = $link[0]->innertext;

		$atts = '';
		$class = '';
		$target = '';
		$href = $link[0]->href;
		if (isset($link[0]->class)) {
			$class = $link[0]->class;
		}
		if (isset($link[0]->target) && $link[0]->target == '_blank') {
			$target = 'new';
		}
		$atributos = $link[0]->getAllAttributes();

		foreach ($atributos as $k => $v) {

			if ($k != 'href' && $k != 'rel' && $k != 'class' && $k != 'target') {
				$atts .= $k . '="' . $v . '"';
			}
		}

		$enlace['original'] = $a;

		if (strpos($class, $ljo_clase) !== false) {
			$ljo_clase = '';
		}
		switch ($tipo_enlace) {
			case 'base64':
				$str = '<span ' . $atts . ' class="' . esc_attr($class) . ' ' . esc_attr($ljo_clase) . '" data-loc="' . $this->base64url_encode($href) . '" data-window="' . esc_attr($target) . '">' . $anchor_text . '</span>';
				break;
			case 'onclick':
				$str = '<span ' . $atts . ' class="' . esc_attr($class) . ' ' . esc_attr($ljo_clase) . '" onclick="ljo_open(\'' . $href . '\', \'' . esc_attr($target) . '\');">' . $anchor_text . '</span>';
				break;
			case 'href':
				$str = '<a ' . $atts . ' class="' . esc_attr($class) . ' ' . esc_attr($ljo_clase) . '" data-loc="' . $this->base64url_encode($href) . '" data-window="' . esc_attr($target) . '">' . $anchor_text . '</a>';
				break;
			case 'nofollow':
			default:
				if (strpos($a, 'rel="') === false) {
					$str = str_replace('<a', '<a rel="nofollow"', $a);
				} else {
					$str = str_replace('rel="', 'rel="nofollow ', $a);
				}
				break;
		}
		$enlace['ofuscado'] = $str;
		return $enlace;
	}

	/**
	 * Inserta en el <head> el código CSS especificado en las opciones del plugin
	 *
	 * @return string
	 */
	public function ljo_estilos_css()
	{
		$ljo_clase = $this->get_ljo_clase();
		echo "\n<style>\na." . esc_attr($ljo_clase) . ",\nspan." . esc_attr($ljo_clase) . " {\n\tcursor: pointer;\n}\n";
		if (trim(carbon_get_theme_option('ljo_css')) != '') {
			echo esc_attr(trim(carbon_get_theme_option('ljo_css'))) . "\n";
		}
		echo "</style>\n";
	}

	/**
	 * Devuelve la etiqueta de apertura del enlace de producto en WooCommerce según si debe ser ofuscado y cómo
	 *
	 * @return string
	 */
	public function ljo_woocommerce_template_loop_product_link_open()
	{
		global $product;

		$link = apply_filters('woocommerce_loop_product_link', get_the_permalink(), $product);

		if ($this->get_ljo_ofuscar_enlace_producto($product->get_id())) {

			$ljo_clase = $this->get_ljo_clase();
			$tipo_enlace = $this->get_ljo_tipo_enlace();

			switch ($tipo_enlace) {
				case 'base64':
					echo '<span class="woocommerce-LoopProduct-link woocommerce-loop-product__link ' . esc_attr($ljo_clase) . '" data-loc="' . $this->base64url_encode(esc_url($link)) . '">';
					break;
				case 'onclick':
					echo '<span class="woocommerce-LoopProduct-link woocommerce-loop-product__link ' . esc_attr($ljo_clase) . '" onclick="ljo_open(\'' . esc_url($link) . '\', \'\');">';
					break;
				case 'href':
					echo '<a class="woocommerce-LoopProduct-link woocommerce-loop-product__link ' . esc_attr($ljo_clase) . '" data-loc="' . $this->base64url_encode(esc_url($link)) . '">';
					break;
				case 'nofollow':
				default:
					echo '<a rel="nofollow" href="' . esc_url($link) . '" class="woocommerce-LoopProduct-link woocommerce-loop-product__link">';
					break;
			}
		} else {
			echo '<a href="' . esc_url($link) . '" class="woocommerce-LoopProduct-link woocommerce-loop-product__link">';
		}
	}

	/**
	 * Devuelve la etiqueta de cierre del enlace de producto en WooCommerce según si debe ser ofuscado y cómo
	 *
	 * @return string
	 */
	public function ljo_woocommerce_template_loop_product_link_close()
	{
		global $product;

		if ($this->get_ljo_ofuscar_enlace_producto($product->get_id())) {
			$tipo_enlace = $this->get_ljo_tipo_enlace();
			switch ($tipo_enlace) {
				case 'base64':
				case 'onclick':
					echo '</span>';
					break;
				case 'href':
				case 'nofollow':
				default:
					echo '</a>';
					break;
			}
		} else {
			echo '</a>';
		}
	}

	/**
	 * Devuelve la etiqueta de apertura del enlace de categoría en WooCommerce según si debe ser ofuscado y cómo
	 *
	 * @return string
	 */
	public function ljo_woocommerce_template_loop_category_link_open($category)
	{
		if ($this->get_ljo_ofuscar_enlace_categoria($category->term_id)) {
			$ljo_clase = $this->get_ljo_clase();
			$tipo_enlace = $this->get_ljo_tipo_enlace();

			switch ($tipo_enlace) {
				case 'base64':
					echo '<span class="' . esc_attr($ljo_clase) . '" data-loc="' . $this->base64url_encode(esc_url(get_term_link($category, 'product_cat'))) . '">';
					break;
				case 'onclick':
					echo '<span class="' . esc_attr($ljo_clase) . '" onclick="ljo_open(\'' . esc_url(get_term_link($category, 'product_cat')) . '\', \'\');">';
					break;
				case 'href':
					echo '<a class="' . esc_attr($ljo_clase) . '" data-loc="' . $this->base64url_encode(esc_url(get_term_link($category, 'product_cat'))) . '">';
					break;
				case 'nofollow':
				default:
					echo '<a rel="nofollow" href="' . esc_url(get_term_link($category, 'product_cat')) . '">';
					break;
			}
		} else {
			echo '<a href="' . esc_url(get_term_link($category, 'product_cat')) . '">';
		}
	}

	/**
	 * Devuelve la etiqueta de cierre del enlace de categoría en WooCommerce según si debe ser ofuscado y cómo
	 *
	 * @return string
	 */
	public function ljo_woocommerce_template_loop_category_link_close($category)
	{
		if ($this->get_ljo_ofuscar_enlace_categoria($category->term_id)) {
			$tipo_enlace = $this->get_ljo_tipo_enlace();
			switch ($tipo_enlace) {
				case 'base64':
				case 'onclick':
					echo '</span>';
					break;
				case 'href':
				case 'nofollow':
				default:
					echo '</a>';
					break;
			}
		} else {
			echo '</a>';
		}
	}

	/**
	 * Devuelve si un enlace de categoría en WooCommerce debe ser ofuscado
	 *
	 * @param int $id
	 * @return bool
	 */
	private function get_ljo_ofuscar_enlace_categoria($id)
	{
		global $wp_query;
		$ofuscar_single = carbon_get_term_meta($id, 'ljo_categoria_single');
		if ($ofuscar_single == 1) {
			return true;
		} elseif ($ofuscar_single == 0) {
			return $this->get_ljo_subcategorias_default();
		} else {
			return false;
		}
	}

	/**
	 * Devuelve si un enlace de producto en WooCommerce debe ser ofuscado
	 *
	 * @param int $id
	 * @return bool
	 */
	private function get_ljo_ofuscar_enlace_producto($id)
	{
		global $wp_query;
		$ofuscar_single = carbon_get_post_meta($id, 'ljo_producto_single');
		if ($ofuscar_single == 1) {
			return true;
		} elseif ($ofuscar_single == 0) {
			return $this->get_ljo_productos_default();
		} else {
			return false;
		}
	}

	/**
	 * Desengancha las funciones de WooCommerce y engancha las sustitutas para la generación de enlaces de productos y categorías
	 *
	 * @return void
	 */
	public function ljo_hooks_woocommerce()
	{
		if (class_exists('woocommerce')) {
			// enlaces de productos
			remove_action('woocommerce_before_shop_loop_item', 'woocommerce_template_loop_product_link_open', 10);
			add_action('woocommerce_before_shop_loop_item', array($this, 'ljo_woocommerce_template_loop_product_link_open'), 20);
			remove_action('woocommerce_after_shop_loop_item', 'woocommerce_template_loop_product_link_close', 10);
			add_action('woocommerce_after_shop_loop_item', array($this,  'ljo_woocommerce_template_loop_product_link_close'), 20);

			// enlaces de categorías
			remove_action('woocommerce_before_subcategory', 'woocommerce_template_loop_category_link_open', 10);
			add_action('woocommerce_before_subcategory', array($this, 'ljo_woocommerce_template_loop_category_link_open'), 20);
			remove_action('woocommerce_after_subcategory', 'woocommerce_template_loop_category_link_close', 10);
			add_action('woocommerce_after_subcategory', array($this, 'ljo_woocommerce_template_loop_category_link_close'), 20);
		}
	}

	/**
	 * Ofusca el enlace 'añadir al carrito' de los productos de WooCommerce
	 *
	 * @param string $link
	 * @return string
	 */
	public function ljo_add_to_cart($link)
	{
		if ($this->get_ljo_addtocart_default() && $this->get_ljo_tipo_enlace() != 'nofollow') {
			$link = str_replace('&ldquo;', '“', $link);
			$link = str_replace('&rdquo;', '”', $link);
			return $this->ljo_content($link, true);
		} else {
			return $link;
		}
	}

	/**
	 * devuelve un array de elementos extra a ofuscar
	 *
	 * @return array
	 */
	private function get_ljo_elementos_extra()
	{
		if ($this->elementos_extra == '') {
			$elementos_extra = trim(carbon_get_theme_option('ljo_elementos_extra'));
			if (strlen($elementos_extra) > 0) {
				$elementos_extra = explode(PHP_EOL, $elementos_extra);
				array_walk($elementos_extra, function (&$value, &$key) {
					$value = trim($value);
				});
				$this->elementos_extra = $elementos_extra;
			}
		}
		return $this->elementos_extra;
	}

	/**
	 * Captura la salida del buffer
	 *
	 * @return void
	 */
	public function ljo_buffer_start()
	{
		$elementos_extra = $this->get_ljo_elementos_extra();
		if (!is_admin() && gettype($elementos_extra) == 'array') {
			ob_start(array($this, 'ljo_buffer_capture'));
		}
	}


	/**
	 * Devuelve el buffer con las modificaciones en los enlaces
	 *
	 * @param string $output
	 * @return string
	 */
	public function ljo_buffer_capture($output)
	{
		$elementos_extra = $this->get_ljo_elementos_extra();
		if (!is_admin() && gettype($elementos_extra) == 'array') {
			$elementos_por_tipo = '';
			foreach ($elementos_extra as $elemento) {
				switch ($elemento[0]) {
					case '#':
					case '.':
						$elementos_por_tipo .= $elemento . ',';
						break;
					default:
						if (strpos($elemento, '*') !== false) {
							$elemento = str_replace('*', '', $elemento);
							$elementos_por_tipo .= 'a[href^=' . $elemento . '],';
						} else {
							$elementos_por_tipo .= 'a[href=' . $elemento . '],';
						}
						if (strpos($elemento, '?') !== false) {
							$elemento = str_replace('?', '', $elemento);
							$elementos_por_tipo .= 'a[href*=' . $elemento . '],';
						}
						break;
				}
			}
			$elementos_por_tipo = trim($elementos_por_tipo, ',');

			// eliminamos comentarios del tipo // texto
			// $output = preg_replace('~([^:]|^)\/\/.*[^>]$~m', '', $output); // eliminado en versión 2.2.1 por problemas con Revolution Slider

			// Create DOM from string
			//$output = html_entity_decode($output);
			$html = ljo_str_get_html($output, false);

			if ($html) {
				foreach ($html->find($elementos_por_tipo) as $element) {
					$link = $this->ljo_content($element->outertext, true);
					$element->outertext = $link;
				}
			}

			return $html;
		}

		return $output;
	}

	/**
	 * Finaliza la captura del buffer
	 *
	 * @return void
	 */
	public function ljo_buffer_end()
	{
		$elementos_extra = $this->get_ljo_elementos_extra();
		if (!is_admin() && gettype($elementos_extra) == 'array') {
			ob_end_flush();
		}
	}
}
