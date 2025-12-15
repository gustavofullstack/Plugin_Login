<?php
/**
 * Settings controller.
 *
 * @package UDI_Custom_Login
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class UDI_Login_Settings {

	const OPTION = 'udi_login_settings';

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'admin_init', array( $this, 'register_settings' ) );
		add_action( 'admin_menu', array( $this, 'register_menu' ) );
		add_filter( 'plugin_action_links_' . plugin_basename( UDI_LOGIN_PLUGIN_FILE ), array( $this, 'action_links' ) );
	}

	/**
	 * Register menu entry.
	 *
	 * @return void
	 */
	public function register_menu() {
		add_options_page(
			__( 'UDI Login', 'udi-custom-login' ),
			__( 'UDI Login', 'udi-custom-login' ),
			'manage_options',
			'udi-login-settings',
			array( $this, 'render_page' )
		);
	}

	/**
	 * Register settings sections/fields.
	 *
	 * @return void
	 */
	public function register_settings() {
		register_setting(
			'udi-login-settings',
			self::OPTION,
			array( $this, 'sanitize' )
		);

		add_settings_section(
			'udi_login_general',
			__( 'Configurações Gerais', 'udi-custom-login' ),
			'__return_null',
			'udi-login-settings'
		);

		add_settings_field(
			'enable_custom_login',
			__( 'Substituir wp-login.php', 'udi-custom-login' ),
			array( $this, 'render_toggle_field' ),
			'udi-login-settings',
			'udi_login_general',
			array(
				'label_for'   => 'enable_custom_login',
				'description' => __( 'Redireciona todas as tentativas de acesso ao wp-login.php para a página personalizada.', 'udi-custom-login' ),
			)
		);

		add_settings_field(
			'login_page_id',
			__( 'Página de Login', 'udi-custom-login' ),
			array( $this, 'render_page_selector' ),
			'udi-login-settings',
			'udi_login_general',
			array(
				'label_for'   => 'login_page_id',
				'description' => __( 'Escolha a página que exibe o shortcode [udi_custom_login].', 'udi-custom-login' ),
			)
		);

		add_settings_field(
			'logo_id',
			__( 'Logo', 'udi-custom-login' ),
			array( $this, 'render_media_field' ),
			'udi-login-settings',
			'udi_login_general',
			array(
				'label_for'   => 'logo_id',
				'description' => __( 'Informe o ID da mídia do logo (use a biblioteca de mídia).', 'udi-custom-login' ),
			)
		);

		add_settings_field(
			'background_id',
			__( 'Imagem de Fundo', 'udi-custom-login' ),
			array( $this, 'render_media_field' ),
			'udi-login-settings',
			'udi_login_general',
			array(
				'label_for'   => 'background_id',
				'description' => __( 'Opcional: ID da mídia usada como fundo.', 'udi-custom-login' ),
			)
		);

		add_settings_field(
			'woocommerce_styling',
			__( 'Estilizar WooCommerce', 'udi-custom-login' ),
			array( $this, 'render_toggle_field' ),
			'udi-login-settings',
			'udi_login_general',
			array(
				'label_for'   => 'woocommerce_styling',
				'description' => __( 'Aplica o layout aos formulários da página Minha Conta.', 'udi-custom-login' ),
			)
		);

		add_settings_section(
			'udi_login_messaging',
			__( 'Textos', 'udi-custom-login' ),
			'__return_null',
			'udi-login-settings'
		);

		add_settings_field(
			'headline',
			__( 'Headline', 'udi-custom-login' ),
			array( $this, 'render_text_field' ),
			'udi-login-settings',
			'udi_login_messaging',
			array(
				'label_for' => 'headline',
			)
		);

		add_settings_field(
			'subheadline',
			__( 'Mensagem Secundária', 'udi-custom-login' ),
			array( $this, 'render_textarea_field' ),
			'udi-login-settings',
			'udi_login_messaging',
			array(
				'label_for' => 'subheadline',
			)
		);

		add_settings_section(
			'udi_login_security',
			__( 'Segurança', 'udi-custom-login' ),
			'__return_null',
			'udi-login-settings'
		);

		add_settings_field(
			'enable_recaptcha',
			__( 'Google reCAPTCHA', 'udi-custom-login' ),
			array( $this, 'render_toggle_field' ),
			'udi-login-settings',
			'udi_login_security',
			array(
				'label_for' => 'enable_recaptcha',
			)
		);

		add_settings_field(
			'enable_honeypot',
			__( 'Honeypot Anti-Spam', 'udi-custom-login' ),
			array( $this, 'render_toggle_field' ),
			'udi-login-settings',
			'udi_login_security',
			array(
				'label_for'   => 'enable_honeypot',
				'description' => __( 'Adiciona um campo invisível para capturar bots.', 'udi-custom-login' ),
			)
		);

		add_settings_field(
			'generic_error_messages',
			__( 'Mensagens de Erro Genéricas', 'udi-custom-login' ),
			array( $this, 'render_toggle_field' ),
			'udi-login-settings',
			'udi_login_security',
			array(
				'label_for'   => 'generic_error_messages',
				'description' => __( 'Exibe "Credenciais inválidas" em vez de especificar se o erro foi no usuário ou senha.', 'udi-custom-login' ),
			)
		);

		add_settings_field(
			'recaptcha_site_key',
			__( 'Site Key', 'udi-custom-login' ),
			array( $this, 'render_text_field' ),
			'udi-login-settings',
			'udi_login_security',
			array(
				'label_for' => 'recaptcha_site_key',
			)
		);

		add_settings_field(
			'recaptcha_secret_key',
			__( 'Secret Key', 'udi-custom-login' ),
			array( $this, 'render_text_field' ),
			'udi-login-settings',
			'udi_login_security',
			array(
				'label_for' => 'recaptcha_secret_key',
			)
		);

		add_settings_field(
			'limit_login_enabled',
			__( 'Limitar tentativas', 'udi-custom-login' ),
			array( $this, 'render_toggle_field' ),
			'udi-login-settings',
			'udi_login_security',
			array(
				'label_for'   => 'limit_login_enabled',
				'description' => __( 'Bloqueia IPs após múltiplas falhas.', 'udi-custom-login' ),
			)
		);

		add_settings_field(
			'limit_login_attempts',
			__( 'Tentativas Permitidas', 'udi-custom-login' ),
			array( $this, 'render_number_field' ),
			'udi-login-settings',
			'udi_login_security',
			array(
				'label_for' => 'limit_login_attempts',
				'min'       => 1,
			)
		);

		add_settings_field(
			'limit_login_lockout',
			__( 'Tempo de Bloqueio (min)', 'udi-custom-login' ),
			array( $this, 'render_number_field' ),
			'udi-login-settings',
			'udi_login_security',
			array(
				'label_for' => 'limit_login_lockout',
				'min'       => 1,
			)
		);

		add_settings_field(
			'enable_security_logging',
			__( 'Registro de Eventos de Segurança', 'udi-custom-login' ),
			array( $this, 'render_toggle_field' ),
			'udi-login-settings',
			'udi_login_security',
			array(
				'label_for'   => 'enable_security_logging',
				'description' => __( 'Registra eventos de login, bloqueios e atividades suspeitas. Útil para auditoria.', 'udi-custom-login' ),
			)
		);

		add_settings_field(
			'enable_password_strength',
			__( 'Validação de Senha Forte', 'udi-custom-login' ),
			array( $this, 'render_toggle_field' ),
			'udi-login-settings',
			'udi_login_security',
			array(
				'label_for'   => 'enable_password_strength',
				'description' => __( 'Requer senhas fortes com letras, números e caracteres especiais. Bloqueia senhas comuns.', 'udi-custom-login' ),
			)
		);

		add_settings_field(
			'password_min_length',
			__( 'Tamanho Mínimo da Senha', 'udi-custom-login' ),
			array( $this, 'render_number_field' ),
			'udi-login-settings',
			'udi_login_security',
			array(
				'label_for'   => 'password_min_length',
				'min'         => 6,
				'description' => __( 'Aplica-se apenas quando validação de senha forte está ativada.', 'udi-custom-login' ),
			)
		);

		add_settings_field(
			'password_min_score',
			__( 'Score Mínimo de Senha (0-4)', 'udi-custom-login' ),
			array( $this, 'render_number_field' ),
			'udi-login-settings',
			'udi_login_security',
			array(
				'label_for'   => 'password_min_score',
				'min'         => 0,
				'max'         => 4,
				'description' => __( '0=Muito fraca, 1=Fraca, 2=Média, 3=Forte, 4=Muito forte', 'udi-custom-login' ),
			)
		);

		// Google Sign-In Section
		add_settings_section(
			'udi_login_google',
			__( 'Login com Google', 'udi-custom-login' ),
			array( $this, 'render_google_section_description' ),
			'udi-login-settings'
		);

		add_settings_field(
			'enable_google_signin',
			__( 'Ativar Login com Google', 'udi-custom-login' ),
			array( $this, 'render_toggle_field' ),
			'udi-login-settings',
			'udi_login_google',
			array(
				'label_for'   => 'enable_google_signin',
				'description' => __( 'Permite login/registro com Conta do Google usando FedCM (API moderna).', 'udi-custom-login' ),
			)
		);

		add_settings_field(
			'google_client_id',
			__( 'Client ID do Google', 'udi-custom-login' ),
			array( $this, 'render_text_field' ),
			'udi-login-settings',
			'udi_login_google',
			array(
				'label_for'   => 'google_client_id',
				'class'       => 'large-text',
				'description' => sprintf(
					__( 'Obtenha em: %s', 'udi-custom-login' ),
					'<a href="https://console.cloud.google.com/apis/credentials" target="_blank">Google Cloud Console</a>'
				),
			)
		);

		add_settings_field(
			'google_enable_fedcm',
			__( 'Ativar FedCM', 'udi-custom-login' ),
			array( $this, 'render_toggle_field' ),
			'udi-login-settings',
			'udi_login_google',
			array(
				'label_for'   => 'google_enable_fedcm',
				'description' => __( 'API moderna sem cookies de terceiros (Chrome 117+). <strong>Recomendado!</strong>', 'udi-custom-login' ),
			)
		);

		add_settings_field(
			'google_enable_onetap',
			__( 'Ativar One Tap', 'udi-custom-login' ),
			array( $this, 'render_toggle_field' ),
			'udi-login-settings',
			'udi_login_google',
			array(
				'label_for'   => 'google_enable_onetap',
				'description' => __( 'Mostra prompt de login rápido no canto da tela.', 'udi-custom-login' ),
			)
		);

		add_settings_field(
			'google_enable_auto_select',
			__( 'Login Automático', 'udi-custom-login' ),
			array( $this, 'render_toggle_field' ),
			'udi-login-settings',
			'udi_login_google',
			array(
				'label_for'   => 'google_enable_auto_select',
				'description' => __( 'Login silencioso para usuários recorrentes (requer One Tap ativado).', 'udi-custom-login' ),
			)
		);

		add_settings_field(
			'google_button_theme',
			__( 'Tema do Botão', 'udi-custom-login' ),
			array( $this, 'render_select_field' ),
			'udi-login-settings',
			'udi_login_google',
			array(
				'label_for' => 'google_button_theme',
				'options'   => array(
					'outline'      => __( 'Outline (branco com borda)', 'udi-custom-login' ),
					'filled_blue'  => __( 'Blue (azul padrão Google)', 'udi-custom-login' ),
					'filled_black' => __( 'Black (preto)', 'udi-custom-login' ),
				),
			)
		);

		add_settings_field(
			'google_button_size',
			__( 'Tamanho do Botão', 'udi-custom-login' ),
			array( $this, 'render_select_field' ),
			'udi-login-settings',
			'udi_login_google',
			array(
				'label_for' => 'google_button_size',
				'options'   => array(
					'large'  => __( 'Grande', 'udi-custom-login' ),
					'medium' => __( 'Médio', 'udi-custom-login' ),
					'small'  => __( 'Pequeno', 'udi-custom-login' ),
				),
			)
		);

		// Redirects Section
		add_settings_section(
			'udi_login_redirects',
			__( 'Redirecionamentos', 'udi-custom-login' ),
			'__return_null',
			'udi-login-settings'
		);

		foreach ( array(
			'redirect_after_login'    => __( 'Após login', 'udi-custom-login' ),
			'redirect_after_register' => __( 'Após registro', 'udi-custom-login' ),
			'redirect_after_logout'   => __( 'Após logout', 'udi-custom-login' ),
		) as $field => $label ) {
			add_settings_field(
				$field,
				$label,
				array( $this, 'render_url_field' ),
				'udi-login-settings',
				'udi_login_redirects',
				array(
					'label_for' => $field,
				)
			);
		}

		add_settings_section(
			'udi_login_account',
			__( 'WooCommerce Minha Conta', 'udi-custom-login' ),
			'__return_null',
			'udi-login-settings'
		);

		add_settings_field(
			'my_account_customization',
			__( 'Ativar customização', 'udi-custom-login' ),
			array( $this, 'render_toggle_field' ),
			'udi-login-settings',
			'udi_login_account',
			array(
				'label_for'   => 'my_account_customization',
				'description' => __( 'Aplica o estilo neon e ajustes visuais na página Minha Conta.', 'udi-custom-login' ),
			)
		);

		add_settings_field(
			'my_account_history_endpoint',
			__( 'Adicionar aba Histórico', 'udi-custom-login' ),
			array( $this, 'render_toggle_field' ),
			'udi-login-settings',
			'udi_login_account',
			array(
				'label_for'   => 'my_account_history_endpoint',
				'description' => __( 'Mostra uma aba com produtos vistos recentemente.', 'udi-custom-login' ),
			)
		);

		add_settings_field(
			'my_account_history_label',
			__( 'Título da aba Histórico', 'udi-custom-login' ),
			array( $this, 'render_text_field' ),
			'udi-login-settings',
			'udi_login_account',
			array(
				'label_for' => 'my_account_history_label',
			)
		);

		add_settings_field(
			'my_account_remove_downloads',
			__( 'Ocultar Downloads', 'udi-custom-login' ),
			array( $this, 'render_toggle_field' ),
			'udi-login-settings',
			'udi_login_account',
			array(
				'label_for'   => 'my_account_remove_downloads',
				'description' => __( 'Remove a aba de downloads quando você não vende produtos digitais.', 'udi-custom-login' ),
			)
		);
	}

	/**
	 * Sanitize settings.
	 *
	 * @param array $input Raw input.
	 *
	 * @return array
	 */
	public function sanitize( $input ) {
		$output = udi_login_get_settings();

		$bools = array(
			'enable_custom_login',
			'enable_recaptcha',
			'limit_login_enabled',
			'woocommerce_styling',
			'my_account_customization',
			'my_account_history_endpoint',
			'my_account_remove_downloads',
			'enable_honeypot',
			'generic_error_messages',
			'enable_security_logging',
			'enable_password_strength',
			'enable_google_signin',
			'google_enable_fedcm',
			'google_enable_onetap',
			'google_enable_auto_select',
		);

		foreach ( $bools as $bool ) {
			$output[ $bool ] = isset( $input[ $bool ] ) && (bool) $input[ $bool ];
		}

		$ints = array(
			'login_page_id',
			'logo_id',
			'background_id',
			'limit_login_attempts',
			'limit_login_lockout',
			'password_min_length',
			'password_min_score',
		);

		foreach ( $ints as $int_field ) {
			$output[ $int_field ] = isset( $input[ $int_field ] ) ? absint( $input[ $int_field ] ) : 0;
		}

		$text_fields = array(
			'headline',
			'subheadline',
			'recaptcha_site_key',
			'recaptcha_secret_key',
			'redirect_after_login',
			'redirect_after_logout',
			'redirect_after_register',
			'social_login_note',
			'my_account_history_label',
			'google_client_id',
			'google_button_type',
			'google_button_theme',
			'google_button_size',
			'google_button_text',
		);

		foreach ( $text_fields as $field ) {
			$output[ $field ] = isset( $input[ $field ] ) ? wp_kses_post( $input[ $field ] ) : '';
		}

		return $output;
	}

	/**
	 * Render Google section description.
	 *
	 * @return void
	 */
	public function render_google_section_description() {
		?>
		<p>
			<?php esc_html_e( 'Configure o login social com Conta do Google usando Google Identity Services com suporte a FedCM.', 'udi-custom-login' ); ?>
			<br>
			<a href="https://console.cloud.google.com/apis/credentials" target="_blank"><?php esc_html_e( 'Obter Client ID no Google Cloud Console', 'udi-custom-login' ); ?></a>
		</p>
		<?php
	}

	/**
	 * Render settings page.
	 *
	 * @return void
	 */
	public function render_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		?>
		<div class="wrap udi-login-settings">
			<h1><?php esc_html_e( 'UDI Custom Login', 'udi-custom-login' ); ?></h1>
			<form action="options.php" method="post">
				<?php
				settings_fields( 'udi-login-settings' );
				do_settings_sections( 'udi-login-settings' );
				submit_button();
				?>
			</form>
		</div>
		<?php
	}

	/**
	 * Render toggle checkbox.
	 *
	 * @param array $args Field args.
	 *
	 * @return void
	 */
	public function render_toggle_field( $args ) {
		$value = (bool) udi_login_get_option( $args['label_for'], false );
		?>
		<label>
			<input type="checkbox" name="<?php echo esc_attr( self::OPTION ); ?>[<?php echo esc_attr( $args['label_for'] ); ?>]" value="1" <?php checked( $value ); ?> />
			<?php if ( ! empty( $args['description'] ) ) : ?>
				<span class="description"><?php echo esc_html( $args['description'] ); ?></span>
			<?php endif; ?>
		</label>
		<?php
	}

	/**
	 * Render text field.
	 *
	 * @param array $args Arguments.
	 *
	 * @return void
	 */
	public function render_text_field( $args ) {
		$value = udi_login_get_option( $args['label_for'], '' );
		?>
		<input type="text" class="regular-text" id="<?php echo esc_attr( $args['label_for'] ); ?>" name="<?php echo esc_attr( self::OPTION ); ?>[<?php echo esc_attr( $args['label_for'] ); ?>]" value="<?php echo esc_attr( $value ); ?>" />
		<?php
	}

	/**
	 * Render URL field.
	 *
	 * @param array $args Arguments.
	 *
	 * @return void
	 */
	public function render_url_field( $args ) {
		$value = esc_url( udi_login_get_option( $args['label_for'], '' ) );
		?>
		<input type="url" class="regular-text" id="<?php echo esc_attr( $args['label_for'] ); ?>" name="<?php echo esc_attr( self::OPTION ); ?>[<?php echo esc_attr( $args['label_for'] ); ?>]" value="<?php echo esc_attr( $value ); ?>" placeholder="https://example.com/minha-conta" />
		<?php
	}

	/**
	 * Render textarea field.
	 *
	 * @param array $args Arguments.
	 *
	 * @return void
	 */
	public function render_textarea_field( $args ) {
		$value = udi_login_get_option( $args['label_for'], '' );
		?>
		<textarea class="large-text" rows="3" id="<?php echo esc_attr( $args['label_for'] ); ?>" name="<?php echo esc_attr( self::OPTION ); ?>[<?php echo esc_attr( $args['label_for'] ); ?>]"><?php echo esc_textarea( $value ); ?></textarea>
		<?php
	}

	/**
	 * Render number field.
	 *
	 * @param array $args Arguments.
	 *
	 * @return void
	 */
	public function render_number_field( $args ) {
		$value = (int) udi_login_get_option( $args['label_for'], 0 );
		$min   = isset( $args['min'] ) ? (int) $args['min'] : 0;
		?>
		<input type="number" id="<?php echo esc_attr( $args['label_for'] ); ?>" min="<?php echo esc_attr( $min ); ?>" name="<?php echo esc_attr( self::OPTION ); ?>[<?php echo esc_attr( $args['label_for'] ); ?>]" value="<?php echo esc_attr( $value ); ?>" />
		<?php
	}

    /**
     * Render select field.
     *
     * @param array $args Field arguments, must include 'options' => array( value => label ).
     * @return void
     */
    public function render_select_field( $args ) {
        $current = udi_login_get_option( $args['label_for'], '' );
        $options = isset( $args['options'] ) ? $args['options'] : array();
        ?>
        <label>
            <select name="<?php echo esc_attr( self::OPTION ); ?>[<?php echo esc_attr( $args['label_for'] ); ?>]">
                <?php foreach ( $options as $value => $label ) : ?>
                    <option value="<?php echo esc_attr( $value ); ?>" <?php selected( $current, $value ); ?>><?php echo esc_html( $label ); ?></option>
                <?php endforeach; ?>
            </select>
            <?php if ( ! empty( $args['description'] ) ) : ?>
                <span class="description"><?php echo esc_html( $args['description'] ); ?></span>
            <?php endif; ?>
        </label>
        <?php
    }

	/**
	 * Render page selector.
	 *
	 * @param array $args Arguments.
	 *
	 * @return void
	 */
	public function render_page_selector( $args ) {
		$value = (int) udi_login_get_option( $args['label_for'], 0 );

		wp_dropdown_pages(
			array(
				'name'             => self::OPTION . '[' . $args['label_for'] . ']',
				'selected'         => $value,
				'show_option_none' => __( 'Selecione uma página', 'udi-custom-login' ),
			)
		);
		if ( ! empty( $args['description'] ) ) {
			echo '<p class="description">' . esc_html( $args['description'] ) . '</p>';
		}
	}

	/**
	 * Render media field (ID).
	 *
	 * @param array $args Arguments.
	 *
	 * @return void
	 */
	public function render_media_field( $args ) {
		$value = (int) udi_login_get_option( $args['label_for'], 0 );
		?>
		<input type="number" id="<?php echo esc_attr( $args['label_for'] ); ?>" name="<?php echo esc_attr( self::OPTION ); ?>[<?php echo esc_attr( $args['label_for'] ); ?>]" value="<?php echo esc_attr( $value ); ?>" />
		<?php if ( ! empty( $args['description'] ) ) : ?>
			<p class="description"><?php echo esc_html( $args['description'] ); ?></p>
		<?php endif; ?>
		<?php
	}

	/**
	 * Add quick action link.
	 *
	 * @param array $links Existing links.
	 *
	 * @return array
	 */
	public function action_links( $links ) {
		$links[] = '<a href="' . esc_url( admin_url( 'options-general.php?page=udi-login-settings' ) ) . '">' . esc_html__( 'Configurações', 'udi-custom-login' ) . '</a>';

		return $links;
	}
}
