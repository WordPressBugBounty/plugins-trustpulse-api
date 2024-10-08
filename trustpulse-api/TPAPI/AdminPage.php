<?php
/**
 * Admin Page class.
 *
 * @since 1.0.0
 *
 * @package TPAPI
 * @author  Erik Jonasson
 */
class TPAPI_AdminPage {


	/**
	 * Holds the class object.
	 *
	 * @since 1.0.0
	 *
	 * @var object
	 */
	public static $instance;

	/**
	 * Path to the file.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	public $file = __FILE__;

	/**
	 * Holds any action notices.
	 *
	 * @since 1.0.0
	 *
	 * @var array
	 */
	public $notices = array();

	/**
	 * Holds the base class object.
	 *
	 * @since 1.0.0
	 *
	 * @var object
	 */
	public $base;

	/**
	 * Holds the base class object.
	 *
	 * @since 1.1.0
	 *
	 * @var string
	 */
	public $page_slug = null;

	/**
	 * Primary class constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		$this->params = $_GET;
		$this->page_slug = isset( $_GET['page'] ) ? sanitize_key( wp_unslash( $_GET['page'] ) ) : null;
		if ( is_admin() ) {
			add_action( 'admin_menu', array( $this, 'add_menu_page' ) );
			add_action( 'admin_menu', array( $this, 'add_submenu_pages' ) );
			// Only perform these actions on the actual admin page
			if ( $this->is_trustpulse_page() ) {
				add_action( 'in_admin_header', array( $this, 'render_trustpulse_banner' ) );
				add_filter( 'admin_body_class', array( $this, 'add_trustpulse_body_class' ) );
				add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts' ) );
				add_action( 'admin_head', array( $this, 'fonts' ) );
			}
		}
	}

	/**
	 * Add Scripts and Styles to the admin area
	 *
	 * @since 1.0.0
	 */
	public function fonts() {
		$url = trustpulse_dir_uri();
		?>
		<style type="text/css">
		@font-face {
			font-family: 'Averta';
			src: url( '<?php echo $url; ?>assets/fonts/Averta-Regular.eot');
			src: url( '<?php echo $url; ?>assets/fonts/Averta-Regular.eot?#iefix') format('embedded-opentype'),
				url( '<?php echo $url; ?>assets/fonts/Averta-Regular.woff') format('woff'),
				url( '<?php echo $url; ?>assets/fonts/Averta-Regular.ttf') format('truetype');
			font-weight: normal;
			font-style: normal;
		}
		@font-face {
			font-family: 'Averta';
			src: url( '<?php echo $url; ?>assets/fonts/Averta-Light.eot');
			src: url( '<?php echo $url; ?>assets/fonts/Averta-Light.eot?#iefix') format('embedded-opentype'),
				url( '<?php echo $url; ?>assets/fonts/Averta-Light.woff') format('woff'),
				url( '<?php echo $url; ?>assets/fonts/Averta-Light.ttf') format('truetype');
			font-weight: 300;
			font-style: normal;
		}

		@font-face {
			font-family: 'Averta';
			src: url( '<?php echo $url; ?>assets/fonts/Averta-Semibold.eot');
			src: url( '<?php echo $url; ?>assets/fonts/Averta-Semibold.eot?#iefix') format('embedded-opentype'),
				url( '<?php echo $url; ?>assets/fonts/Averta-Semibold.woff') format('woff'),
				url( '<?php echo $url; ?>assets/fonts/Averta-Semibold.ttf') format('truetype');
			font-weight: 500;
			font-style: normal;
		}

		@font-face {
			font-family: 'Averta';
			src: url( '<?php echo $url; ?>assets/fonts/Averta-Semibold.eot');
			src: url( '<?php echo $url; ?>assets/fonts/Averta-Semibold.eot?#iefix') format('embedded-opentype'),
				url( '<?php echo $url; ?>assets/fonts/Averta-Semibold.woff') format('woff'),
				url( '<?php echo $url; ?>assets/fonts/Averta-Semibold.ttf') format('truetype');
			font-weight: 600;
			font-style: normal;
		}

		@font-face {
			font-family: 'Averta';
			src: url( '<?php echo $url; ?>assets/fonts/Averta-Bold.eot');
			src: url( '<?php echo $url; ?>assets/fonts/Averta-Bold.eot?#iefix') format('embedded-opentype'),
				url( '<?php echo $url; ?>assets/fonts/Averta-Bold.woff') format('woff'),
				url( '<?php echo $url; ?>assets/fonts/Averta-Bold.ttf') format('truetype');
			font-weight: bold;
			font-style: normal;
		}

		@font-face {
			font-family: 'Averta';
			src: url( '<?php echo $url; ?>assets/fonts/Averta-ExtraBoldItalic.eot');
			src: url( '<?php echo $url; ?>assets/fonts/Averta-ExtraBoldItalic.eot?#iefix') format('embedded-opentype'),
				url( '<?php echo $url; ?>assets/fonts/Averta-ExtraBoldItalic.woff') format('woff'),
				url( '<?php echo $url; ?>assets/fonts/Averta-ExtraBoldItalic.ttf') format('truetype');
			font-weight: bold;
			font-style: italic, oblique;
		}
		</style>
		<?php

	}

	/**
	 * Add Scripts and Styles to the admin area
	 *
	 * @since 1.0.0
	 */
	public function admin_scripts() {
		wp_enqueue_script( 'tp-admin-js', trustpulse_dir_uri() . 'assets/dist/js/trustpulse-admin.min.js', array( 'jquery' ), TRUSTPULSE_PLUGIN_VERSION, true );
		wp_register_style( 'tp-admin-css', trustpulse_dir_uri() . 'assets/dist/css/trustpulse-admin.min.css', false, TRUSTPULSE_PLUGIN_VERSION );
		wp_enqueue_style( 'tp-admin-css' );
	}


	/**
	 * Register the TrustPulse Options Page
	 *
	 * @since 1.0.0
	 */
	public function add_menu_page() {
		add_menu_page(
			'TrustPulse Settings',
			'TrustPulse',
			'manage_options',
			TRUSTPULSE_ADMIN_PAGE_NAME,
			array(
				$this,
				'render_settings_page',
			),
			'data:image/svg+xml;base64,' . base64_encode( '<svg width="18" height="20" viewBox="0 0 151 169" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" xml:space="preserve" xmlns:serif="http://www.serif.com/" style="fill: black;, fill-rule:evenodd;clip-rule:evenodd;stroke-linejoin:round;stroke-miterlimit:1.41421;"><g id="mark" transform="matrix(0.461143,0,0,0.461143,-106.676,-1411.44)"><g transform="matrix(2.16852,0,0,2.16852,230.412,3060.73)">    <path d="M103.188,26.561C96.662,24.799 90.29,22.455 84.167,19.45C78.972,16.901 73.961,13.997 69.051,10.94C68.956,10.999 68.861,11.058 68.765,11.117C55.841,19.137 42.1,25.615 26.835,28.481C21.327,29.515 15.756,30.164 10.174,30.64L9.996,30.655C8.621,61.384 10.671,93.057 26.99,119.407C36.937,135.468 52.327,147.495 68.626,157.492L69.051,157.751C75.721,153.675 82.272,149.328 88.368,144.473L102.206,144.473C92.398,153.704 80.957,161.44 69.326,168.346C68.781,167.973 68.776,168.346 68.776,168.346C54.105,159.635 39.822,150.006 28.706,137.221C21.155,128.537 15.176,118.558 10.867,107.757C0.039,80.614 -0.647,50.397 1.241,22.026C13.241,21.311 25.428,20.158 36.915,16.454C48.244,12.801 58.678,6.735 68.756,0.192L69.051,0C69.247,0.128 69.443,0.255 69.64,0.383C82.351,8.617 95.916,15.884 110.854,18.998C111.709,19.176 112.566,19.344 113.425,19.502L103.188,26.561ZM100.152,132.347L81.156,132.347C77.981,132.212 77.767,130.604 77.7,129.035C77.621,127.169 78.831,125.529 81.156,125.43L97.514,125.43L102.685,106.579C102.685,106.579 103.683,104.245 105.689,104.052C107.293,103.897 108.672,104.574 109.337,106.514L117.15,132.951L121.936,117.91C121.936,117.91 123.484,115.168 125.942,115.574C127.282,115.917 127.336,115.979 128.113,117.047L133.676,125.43L146.996,125.43C147.012,125.432 149.468,125.764 150.205,127.599C151.058,129.721 149.708,132.232 146.996,132.347L131.82,132.347C130.17,132.279 129.699,131.85 128.939,130.801L126.339,126.884L120.324,145.791C120.324,145.791 119.264,148.043 117.286,148.191C115.705,148.309 114.365,147.633 113.711,145.722L106.144,120.117L103.487,129.804C103.487,129.804 102.28,132.254 100.152,132.347ZM137.379,31.586C138.051,47.952 137.548,64.343 134.744,80.522C133.466,87.9 130.68,98.081 128.24,105.175L119.46,102.321C126.914,82.657 128.717,61.266 128.394,40.33L137.379,31.586Z" style="fill:#a0a5aa;fill-rule:nonzero;"/></g><g transform="matrix(0.746317,0,0,0.746317,244.486,3011.77)">    <path d="M171.699,279.729C173.221,277.994 173.616,276.855 175.885,273.555C188.97,254.516 227.729,203.188 276.979,163.708C298.41,146.527 321.573,130.908 342.063,120.67C376.211,103.607 387.207,101.527 403.066,97.832C406.078,97.131 416.986,95.184 418.796,99.482C420.542,103.631 413.738,108.003 411.185,110.009C408.315,112.264 342.682,161.344 287.465,234.232C239.418,297.651 207.976,380.862 207.976,380.862C207.01,382.71 196.083,402.237 181.423,403.188C167.874,404.069 150.473,386.445 150.473,386.445C147.128,383.324 130.815,363.964 117.241,340.019C99.929,309.489 85.022,273.299 85.022,273.299C85.022,273.299 69.547,242.857 98.721,226.115C127.486,209.606 140.299,226.367 146.542,236.262C151.877,244.719 162.626,265.262 167.821,275.31C169.631,278.81 170.801,280.751 171.699,279.729Z" style="fill:#a0a5aa;fill-rule:nonzero;"/></g></g></svg>' ),
			90
		);
	}

	/**
	 * Register the TrustPulse Options Page
	 *
	 * @since 1.1.0
	 */
	public function add_submenu_pages() {
		// Let's add a Home submenu page just for clarity
		add_submenu_page( TRUSTPULSE_ADMIN_PAGE_NAME, 'Home', 'Home', 'manage_options', TRUSTPULSE_ADMIN_PAGE_NAME, array(
			$this,
			'render_settings_page',
		) );
		add_submenu_page( TRUSTPULSE_ADMIN_PAGE_NAME, 'About Us', 'About Us', 'manage_options', TRUSTPULSE_ABOUT_PAGE_NAME, array(
			$this,
			'render_about_page',
		) );

		if ( get_option( 'trustpulse_script_id', null ) && TPAPI_WooCommerce::is_active() ) {
			add_submenu_page( TRUSTPULSE_ADMIN_PAGE_NAME, 'WooCommerce Settings', 'WooCommerce Settings', 'manage_options', TRUSTPULSE_WC_PAGE_NAME, array(
				$this,
				'render_woocommerce_page',
			) );
		}
	}

	/**
	 * Check if the current page is a TrustPulse page
	 *
	 * @since 1.1.0
	 *
	 * @return boolean Whether or not it is a registered page for TrustPulse
	 */
	public function is_trustpulse_page() {
		$tpPages = array(
			TRUSTPULSE_ADMIN_PAGE_NAME,
			TRUSTPULSE_ABOUT_PAGE_NAME,
			TRUSTPULSE_WC_PAGE_NAME,
		);
		return in_array( $this->page_slug, $tpPages );
	}

	/**
	 * Add the TrustPulse Body class on the TrustPulse admin page
	 *
	 * @param array $classes
	 * @return array $classes
	 */
	public function add_trustpulse_body_class( $classes ) {
		// Only add this class on the TrustPulse Settings Page
		if ( $this->is_trustpulse_page() ) {
			$classes = "$classes tp-settings-page";
		}

		return $classes;
	}

	/**
	 * Render the TrustPulse Options Page
	 *
	 * @since 1.0.0
	 */
	public function render_settings_page() {
		require dirname( __FILE__ ) . '/../views/trustpulse-settings-page.php';
	}

	/**
	 * Render the TrustPulse WooCommerce Page
	 *
	 * @since 1.1.0
	 */
	public function render_woocommerce_page() {
		require dirname( __FILE__ ) . '/../views/trustpulse-woocommerce-verification-page.php';
	}

	/**
	 * Render the TrustPulse About Us Page
	 *
	 * @since 1.2.3
	 */
	public function render_about_page() {
		require dirname( __FILE__ ) . '/../views/trustpulse-about-us-page.php';
	}

	/**
	 * Renders the TrustPulse banner at the top of the page
	 *
	 * @return void
	 */
	public function render_trustpulse_banner() {
		// Don't render if we're not on the TrustPulse settings page
		if ( ! $this->is_trustpulse_page() ) {
			return;
		}
		?>
<header id="masthead" class="navbar-header">
	<nav class="navbar" role="navigation" aria-label="main navigation">
		<div class="navbar-brand">
			<a class="navbar-item" href="<?php echo TRUSTPULSE_URL; ?>">
				<img src="<?php echo TRUSTPULSE_URL; ?>wp-content/themes/trustpulse/assets/images/logo@2x.png" width="170">
			</a>
			<a role="button" class="navbar-burger burger" aria-label="menu" aria-expanded="false" data-target="siteNavMenu">
				<span aria-hidden="true"></span>
				<span aria-hidden="true"></span>
				<span aria-hidden="true"></span>
			</a>
		</div>
		<div id="siteNavPageTitle" class="navbar-page-title">
			<span class="navbar-item"><?php echo esc_html( get_admin_page_title() ); ?></span>
		</div>
		<div id="siteNavMenu" class="navbar-menu">
			<div class="navbar-end">
				<div class="menu-top-navbar-menu-container">
					<ul id="primary-menu" class="navbar">
						<a class="navbar-item" href="<?php echo TRUSTPULSE_APP_URL; ?>account/support/" target="_blank"><span class="hover-underline"><?php esc_html_e( 'Support', 'trustpulse-api' ); ?></span></a>
						<a class="navbar-item" href="<?php echo TRUSTPULSE_URL; ?>docs/" target="_blank"><span class="hover-underline"><?php esc_html_e( 'Documentation', 'trustpulse-api' ); ?></span></a>
					</ul>
				</div>
			</div>
		</div>
	</nav>
</header>
		<?php
	}
}
