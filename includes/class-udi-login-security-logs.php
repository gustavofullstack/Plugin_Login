<?php
/**
 * Admin page for viewing security logs.
 *
 * @package UDI_Custom_Login
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class UDI_Login_Security_Logs {

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'register_submenu' ) );
		add_action( 'admin_post_udi_clear_security_logs', array( $this, 'handle_clear_logs' ) );
	}

	/**
	 * Register submenu page.
	 *
	 * @return void
	 */
	public function register_submenu() {
		if ( ! udi_login_get_option( 'enable_security_logging', false ) ) {
			return;
		}

		add_submenu_page(
			'options-general.php',
			__( 'Logs de Segurança - UDI Login', 'udi-custom-login' ),
			__( 'Logs de Segurança', 'udi-custom-login' ),
			'manage_options',
			'udi-login-security-logs',
			array( $this, 'render_page' )
		);
	}

	/**
	 * Render the logs page.
	 *
	 * @return void
	 */
	public function render_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$logs = udi_login_get_security_logs( 100 );
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Logs de Segurança - UDI Login', 'udi-custom-login' ); ?></h1>
			
			<p class="description">
				<?php esc_html_e( 'Eventos de segurança registrados pelo plugin. Mostrando os últimos 100 eventos.', 'udi-custom-login' ); ?>
			</p>

			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="margin-bottom: 20px;">
				<?php wp_nonce_field( 'udi_clear_logs', 'udi_logs_nonce' ); ?>
				<input type="hidden" name="action" value="udi_clear_security_logs" />
				<button type="submit" class="button button-secondary" onclick="return confirm('<?php esc_attr_e( 'Tem certeza que deseja limpar todos os logs?', 'udi-custom-login' ); ?>');">
					<?php esc_html_e( 'Limpar Todos os Logs', 'udi-custom-login' ); ?>
				</button>
			</form>

			<?php if ( empty( $logs ) ) : ?>
				<div class="notice notice-info">
					<p><?php esc_html_e( 'Nenhum evento de segurança registrado ainda.', 'udi-custom-login' ); ?></p>
				</div>
			<?php else : ?>
				<table class="wp-list-table widefat fixed striped">
					<thead>
						<tr>
							<th style="width: 160px;"><?php esc_html_e( 'Data/Hora', 'udi-custom-login' ); ?></th>
							<th style="width: 140px;"><?php esc_html_e( 'Tipo de Evento', 'udi-custom-login' ); ?></th>
							<th><?php esc_html_e( 'Mensagem', 'udi-custom-login' ); ?></th>
							<th style="width: 120px;"><?php esc_html_e( 'IP', 'udi-custom-login' ); ?></th>
							<th style="width: 100px;"><?php esc_html_e( 'Detalhes', 'udi-custom-login' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( $logs as $log ) : ?>
							<tr>
								<td><?php echo esc_html( $log['timestamp'] ); ?></td>
								<td>
									<span class="udi-event-badge udi-event-<?php echo esc_attr( $log['event_type'] ); ?>">
										<?php echo esc_html( ucfirst( str_replace( '_', ' ', $log['event_type'] ) ) ); ?>
									</span>
								</td>
								<td><?php echo esc_html( $log['message'] ); ?></td>
								<td><code><?php echo esc_html( $log['ip'] ); ?></code></td>
								<td>
									<?php if ( ! empty( $log['context'] ) ) : ?>
										<button type="button" class="button button-small" onclick="alert('<?php echo esc_js( wp_json_encode( $log['context'], JSON_PRETTY_PRINT ) ); ?>')">
											<?php esc_html_e( 'Ver', 'udi-custom-login' ); ?>
										</button>
									<?php endif; ?>
								</td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			<?php endif; ?>

			<style>
				.udi-event-badge {
					display: inline-block;
					padding: 3px 8px;
					border-radius: 3px;
					font-size: 11px;
					font-weight: 600;
					text-transform: uppercase;
				}
				.udi-event-login_success {
					background: #d4edda;
					color: #155724;
				}
				.udi-event-login_failed {
					background: #f8d7da;
					color: #721c24;
				}
				.udi-event-login_blocked,
				.udi-event-account_locked {
					background: #fff3cd;
					color: #856404;
				}
				.udi-event-user_registered {
					background: #cce5ff;
					color: #004085;
				}
				.udi-event-suspicious_activity {
					background: #f5c6cb;
					color: #721c24;
				}
			</style>
		</div>
		<?php
	}

	/**
	 * Handle clearing logs.
	 *
	 * @return void
	 */
	public function handle_clear_logs() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Você não tem permissão para executar esta ação.', 'udi-custom-login' ) );
		}

		if ( ! isset( $_POST['udi_logs_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['udi_logs_nonce'] ) ), 'udi_clear_logs' ) ) {
			wp_die( esc_html__( 'Sessão inválida.', 'udi-custom-login' ) );
		}

		udi_login_clear_security_logs();

		wp_safe_redirect( add_query_arg( 'cleared', '1', wp_get_referer() ) );
		exit;
	}
}

new UDI_Login_Security_Logs();
