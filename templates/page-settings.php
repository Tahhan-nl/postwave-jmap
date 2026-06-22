<?php
/**
 * Postwave admin page template — v3 Professional
 * Variables: $options, $stats, $entries, $tab, $is_setup, $retry_count
 *
 * @package Postwave
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

$o   = fn( $k, $d = '' ) => $options[ $k ] ?? $d;
$url = fn( array $args ) => esc_url( add_query_arg( $args, admin_url( 'admin.php' ) ) );
$act = esc_url( admin_url( 'admin-post.php' ) );

$configured = ! empty( $options['server_url'] ) && ! empty( $options['username'] ) && ! empty( $options['password'] );
$enabled    = ! empty( $options['enabled'] );

/* ── Inline SVGs ── */
$logo = '<svg width="32" height="32" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
  <defs><linearGradient id="pwG" x1="0" y1="0" x2="32" y2="32" gradientUnits="userSpaceOnUse">
    <stop offset="0%" stop-color="#818cf8"/><stop offset="100%" stop-color="#4338ca"/>
  </linearGradient></defs>
  <rect width="32" height="32" rx="8" fill="url(#pwG)"/>
  <rect x="7" y="17" width="18" height="9" rx="2" fill="white" opacity=".95"/>
  <path d="M7 19L16 23.5L25 19" stroke="#6366f1" stroke-width="1.3" stroke-linejoin="round" fill="none"/>
  <path d="M10 14.5Q13 10.5 16 14.5Q19 18.5 22 14.5" stroke="white" stroke-width="1.7" stroke-linecap="round" fill="none" opacity=".9"/>
  <path d="M12 11.5Q14 9 16 11.5Q18 14 20 11.5" stroke="white" stroke-width="1.3" stroke-linecap="round" fill="none" opacity=".55"/>
</svg>';

/* icon helpers */
$icon_general    = '<svg viewBox="0 0 20 20" fill="currentColor" width="16" height="16"><path fill-rule="evenodd" d="M11.49 3.17c-.38-1.56-2.6-1.56-2.98 0a1.532 1.532 0 01-2.286.948c-1.372-.836-2.942.734-2.106 2.106.54.886.061 2.042-.947 2.287-1.561.379-1.561 2.6 0 2.978a1.532 1.532 0 01.947 2.287c-.836 1.372.734 2.942 2.106 2.106a1.532 1.532 0 012.287.947c.379 1.561 2.6 1.561 2.978 0a1.533 1.533 0 012.287-.947c1.372.836 2.942-.734 2.106-2.106a1.533 1.533 0 01.947-2.287c1.561-.379 1.561-2.6 0-2.978a1.532 1.532 0 01-.947-2.287c.836-1.372-.734-2.942-2.106-2.106a1.532 1.532 0 01-2.287-.947zM10 13a3 3 0 100-6 3 3 0 000 6z" clip-rule="evenodd"/></svg>';
$icon_connection = '<svg viewBox="0 0 20 20" fill="currentColor" width="16" height="16"><path fill-rule="evenodd" d="M2 5a2 2 0 012-2h12a2 2 0 012 2v2a2 2 0 01-2 2H4a2 2 0 01-2-2V5zm14 1a1 1 0 11-2 0 1 1 0 012 0zM2 13a2 2 0 012-2h12a2 2 0 012 2v2a2 2 0 01-2 2H4a2 2 0 01-2-2v-2zm14 1a1 1 0 11-2 0 1 1 0 012 0z" clip-rule="evenodd"/></svg>';
$icon_log        = '<svg viewBox="0 0 20 20" fill="currentColor" width="16" height="16"><path fill-rule="evenodd" d="M3 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" clip-rule="evenodd"/></svg>';
$icon_check      = '<svg viewBox="0 0 20 20" fill="currentColor" width="14" height="14"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>';
?>

<?php if ( $is_setup ) : /* ══════════════ WIZARD ══════════════ */ ?>

<div id="pw-page" class="pw-mode-wizard">
<div class="pw-wizard-outer">
  <div class="pw-wizard-card" id="pw-wizard">

    <!-- Step 0: Welcome -->
    <div id="pw-ws0">
      <div class="pw-hero">
        <?php echo $logo; ?>
        <h1>Welcome to Postwave JMAP</h1>
        <p>Modern JMAP mail for WordPress — no SMTP, no ports, no hassle.</p>
      </div>
      <ul class="pw-feats">
        <li>
          <div class="pw-feat-icon">
            <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M11.3 1.046A1 1 0 0112 2v5h4a1 1 0 01.82 1.573l-7 10A1 1 0 018 18v-5H4a1 1 0 01-.82-1.573l7-10a1 1 0 011.12-.38z" clip-rule="evenodd"/></svg>
          </div>
          <div>
            <strong><?php esc_html_e( 'JMAP Protocol', 'postwave' ); ?></strong>
            <span><?php esc_html_e( 'RFC 8620/8621 — works with Stalwart, Fastmail &amp; more', 'postwave' ); ?></span>
          </div>
        </li>
        <li>
          <div class="pw-feat-icon">
            <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/></svg>
          </div>
          <div>
            <strong><?php esc_html_e( 'Secure delivery', 'postwave' ); ?></strong>
            <span><?php esc_html_e( 'Auth via JMAP credentials, no plaintext secrets', 'postwave' ); ?></span>
          </div>
        </li>
        <li>
          <div class="pw-feat-icon">
            <svg viewBox="0 0 20 20" fill="currentColor"><path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"/><path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 4a1 1 0 100 2h.01a1 1 0 100-2H7zm3 0a1 1 0 100 2h3a1 1 0 100-2h-3z" clip-rule="evenodd"/></svg>
          </div>
          <div>
            <strong><?php esc_html_e( 'Mail log', 'postwave' ); ?></strong>
            <span><?php esc_html_e( 'Every send attempt tracked — subject, recipient, status', 'postwave' ); ?></span>
          </div>
        </li>
      </ul>
      <div class="pw-hero-footer">
        <button type="button" class="pw-btn pw-btn-primary pw-btn-lg" id="pw-wz-start">
          <?php esc_html_e( 'Start setup', 'postwave' ); ?>
          <svg viewBox="0 0 20 20" fill="currentColor" width="16" height="16"><path fill-rule="evenodd" d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
        </button>
        <a href="<?php echo $url( [ 'page' => 'postwave', 'skip' => '1' ] ); ?>" class="pw-link-subtle">
          <?php esc_html_e( 'Skip — configure manually later', 'postwave' ); ?>
        </a>
      </div>
    </div>

    <!-- Steps 1–3 (hidden until start) -->
    <div id="pw-wz-body" style="display:none">

      <div class="pw-progress" id="pw-progress">
        <div class="pw-pdot pw-pdot-active" data-n="1"><span>1</span><?php esc_html_e( 'Server', 'postwave' ); ?></div>
        <div class="pw-pline"></div>
        <div class="pw-pdot" data-n="2"><span>2</span><?php esc_html_e( 'Sender', 'postwave' ); ?></div>
        <div class="pw-pline"></div>
        <div class="pw-pdot" data-n="3"><span>3</span><?php esc_html_e( 'Activate', 'postwave' ); ?></div>
      </div>

      <form method="post" action="<?php echo $act; ?>" id="pw-wz-form">
        <?php wp_nonce_field( 'postwave_save' ); ?>
        <input type="hidden" name="action"          value="postwave_save">
        <input type="hidden" name="postwave[_tab]"  value="general">

        <!-- Step 1: Server -->
        <div id="pw-ws1" class="pw-wstep" style="display:none">
          <div class="pw-wstep-head">
            <h2><?php esc_html_e( 'Connect your JMAP server', 'postwave' ); ?></h2>
            <p><?php esc_html_e( 'Enter the URL and credentials for your JMAP mail server.', 'postwave' ); ?></p>
          </div>
          <div class="pw-field">
            <label for="pw-w-url"><?php esc_html_e( 'Server URL', 'postwave' ); ?></label>
            <input type="url" id="pw-w-url" class="pw-input" name="postwave[server_url]"
              value="<?php echo esc_attr( $o( 'server_url' ) ); ?>"
              placeholder="https://mail.example.com" required>
            <span class="pw-field-hint"><?php esc_html_e( 'JMAP session is auto-discovered at /.well-known/jmap', 'postwave' ); ?></span>
          </div>
          <div class="pw-row-2">
            <div class="pw-field">
              <label for="pw-w-user"><?php esc_html_e( 'Username', 'postwave' ); ?></label>
              <input type="text" id="pw-w-user" class="pw-input" name="postwave[username]"
                value="<?php echo esc_attr( $o( 'username' ) ); ?>" autocomplete="username" required>
            </div>
            <div class="pw-field">
              <label for="pw-w-pass"><?php esc_html_e( 'Password', 'postwave' ); ?></label>
              <div class="pw-pass-wrap">
                <input type="password" id="pw-w-pass" class="pw-input" name="postwave[password]" autocomplete="new-password">
                <button type="button" class="pw-eye-btn" data-for="pw-w-pass" tabindex="-1">
                  <svg class="pw-eye-on"  viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                  <svg class="pw-eye-off" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" style="display:none"><path d="M17.94 17.94A10.07 10.07 0 0112 20c-7 0-11-8-11-8a18.45 18.45 0 015.06-5.94"/><path d="M9.9 4.24A9.12 9.12 0 0112 4c7 0 11 8 11 8a18.5 18.5 0 01-2.16 3.19"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                </button>
              </div>
            </div>
          </div>
          <div class="pw-wstep-nav">
            <span></span>
            <button type="button" class="pw-btn pw-btn-primary" data-next="2"><?php esc_html_e( 'Continue', 'postwave' ); ?> →</button>
          </div>
        </div>

        <!-- Step 2: Sender -->
        <div id="pw-ws2" class="pw-wstep" style="display:none">
          <div class="pw-wstep-head">
            <h2><?php esc_html_e( 'Sender information', 'postwave' ); ?></h2>
            <p><?php esc_html_e( 'How should outgoing emails appear to recipients?', 'postwave' ); ?></p>
          </div>
          <div class="pw-row-2">
            <div class="pw-field">
              <label for="pw-w-fname"><?php esc_html_e( 'From Name', 'postwave' ); ?></label>
              <input type="text" id="pw-w-fname" class="pw-input" name="postwave[from_name]"
                value="<?php echo esc_attr( $o( 'from_name', get_bloginfo( 'name' ) ) ); ?>">
            </div>
            <div class="pw-field">
              <label for="pw-w-femail"><?php esc_html_e( 'From Email', 'postwave' ); ?></label>
              <input type="email" id="pw-w-femail" class="pw-input" name="postwave[from_email]"
                value="<?php echo esc_attr( $o( 'from_email', get_bloginfo( 'admin_email' ) ) ); ?>" required>
            </div>
          </div>
          <div class="pw-field">
            <label for="pw-w-recip"><?php esc_html_e( 'Test recipient', 'postwave' ); ?> <em class="pw-label-opt"><?php esc_html_e( 'optional', 'postwave' ); ?></em></label>
            <input type="email" id="pw-w-recip" class="pw-input pw-input-half" name="postwave[test_recipient]"
              value="<?php echo esc_attr( $o( 'test_recipient' ) ); ?>"
              placeholder="<?php esc_attr_e( 'Falls back to From Email', 'postwave' ); ?>">
          </div>
          <div class="pw-wstep-nav">
            <button type="button" class="pw-btn pw-btn-outline" data-prev="1">← <?php esc_html_e( 'Back', 'postwave' ); ?></button>
            <button type="button" class="pw-btn pw-btn-primary" data-next="3"><?php esc_html_e( 'Continue', 'postwave' ); ?> →</button>
          </div>
        </div>

        <!-- Step 3: Activate -->
        <div id="pw-ws3" class="pw-wstep" style="display:none">
          <div class="pw-wstep-head">
            <h2><?php esc_html_e( 'Activate Postwave JMAP', 'postwave' ); ?></h2>
            <p><?php esc_html_e( 'Enable the plugin to route all WordPress emails through JMAP.', 'postwave' ); ?></p>
          </div>
          <input type="hidden" name="postwave[enabled]" value="0">
          <div class="pw-toggle-row">
            <div class="pw-toggle-info">
              <strong><?php esc_html_e( 'Enable Postwave JMAP', 'postwave' ); ?></strong>
              <span><?php esc_html_e( 'Intercept all wp_mail() calls and send via JMAP', 'postwave' ); ?></span>
            </div>
            <label class="pw-toggle">
              <input type="checkbox" name="postwave[enabled]" value="1" class="pw-toggle-cb" <?php checked( 1, $o( 'enabled' ) ); ?>>
              <span class="pw-toggle-track"><span class="pw-toggle-thumb"></span></span>
            </label>
          </div>
          <div class="pw-wstep-nav">
            <button type="button" class="pw-btn pw-btn-outline" data-prev="2">← <?php esc_html_e( 'Back', 'postwave' ); ?></button>
            <button type="submit" class="pw-btn pw-btn-primary pw-btn-lg">
              <svg viewBox="0 0 20 20" fill="currentColor" width="16" height="16"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
              <?php esc_html_e( 'Save &amp; finish', 'postwave' ); ?>
            </button>
          </div>
        </div>

      </form>
    </div><!-- /#pw-wz-body -->

  </div><!-- /.pw-wizard-card -->
</div><!-- /.pw-wizard-outer -->
</div><!-- #pw-page -->

<?php else : /* ══════════════ DASHBOARD ══════════════ */ ?>

<div id="pw-page" class="pw-mode-dash">

  <?php
  /* ── Tab meta ── */
  $tab_meta = [
    'general'    => [
      'label' => __( 'General', 'postwave' ),
      'desc'  => __( 'Enable Postwave JMAP and configure sender information.', 'postwave' ),
      'icon'  => $icon_general,
    ],
    'connection' => [
      'label' => __( 'Connection', 'postwave' ),
      'desc'  => __( 'JMAP server credentials and connection testing.', 'postwave' ),
      'icon'  => $icon_connection,
    ],
    'log'        => [
      'label' => __( 'Mail Log', 'postwave' ),
      'desc'  => __( 'Last 100 send attempts. Message bodies are never stored.', 'postwave' ),
      'icon'  => $icon_log,
    ],
  ];
  $current_meta = $tab_meta[ $tab ] ?? $tab_meta['general'];
  ?>

  <!-- ── App Header ── -->
  <header class="pw-app-header">
    <div class="pw-app-brand">
      <?php echo $logo; ?>
      <div class="pw-app-brand-text">
        <strong>Postwave</strong>
        <span>JMAP Mail for WordPress</span>
      </div>
    </div>
    <div class="pw-app-header-right">
      <?php
      if ( $enabled && $configured ) {
        echo '<span class="pw-status pw-status-active"><i></i>' . esc_html__( 'Active', 'postwave' ) . '</span>';
      } elseif ( $configured ) {
        echo '<span class="pw-status pw-status-inactive"><i></i>' . esc_html__( 'Disabled', 'postwave' ) . '</span>';
      } else {
        echo '<span class="pw-status pw-status-unconfigured"><i></i>' . esc_html__( 'Not configured', 'postwave' ) . '</span>';
      }
      ?>
    </div>
  </header>

  <!-- ── Top Navigation ── -->
  <nav class="pw-app-nav">
    <div class="pw-app-nav-inner">
      <?php foreach ( $tab_meta as $key => $meta ) :
        $is_active = $tab === $key;
        $count = ( $key === 'log' && $stats['total'] > 0 ) ? '<span class="pw-nav-count">' . intval( $stats['total'] ) . '</span>' : '';
      ?>
      <a href="<?php echo $url( [ 'page' => 'postwave', 'tab' => $key ] ); ?>"
         class="pw-nav-item<?php echo $is_active ? ' pw-nav-item-active' : ''; ?>">
        <?php echo $meta['icon']; ?>
        <span><?php echo esc_html( $meta['label'] ); ?></span>
        <?php echo $count; ?>
      </a>
      <?php endforeach; ?>
    </div>
    <span class="pw-app-version">v<?php echo esc_html( POSTWAVE_VERSION ); ?></span>
  </nav>

  <!-- ── Main Content ── -->
  <div class="pw-app-body">
    <main class="pw-main">

      <!-- Page title + saved notice -->
      <div class="pw-page-header">
        <div>
          <h2><?php echo esc_html( $current_meta['label'] ); ?></h2>
          <p><?php echo esc_html( $current_meta['desc'] ); ?></p>
        </div>
      </div>

      <?php if ( isset( $_GET['saved'] ) ) : ?>
      <div class="pw-notice pw-notice-success">
        <?php echo $icon_check; ?>
        <?php esc_html_e( 'Settings saved successfully.', 'postwave' ); ?>
      </div>
      <?php endif; ?>

      <?php if ( isset( $_GET['cleared'] ) ) : ?>
      <div class="pw-notice pw-notice-success">
        <?php echo $icon_check; ?>
        <?php esc_html_e( 'Mail log cleared.', 'postwave' ); ?>
      </div>
      <?php endif; ?>

      <!-- Stats row (always visible) -->
      <div class="pw-stats-row">
        <?php
        $stats_cfg = [
          [ 'value' => $stats['sent_today'],   'label' => __( 'Sent today', 'postwave' ),       'color' => 'blue' ],
          [ 'value' => $stats['failed_today'], 'label' => __( 'Failed today', 'postwave' ),     'color' => 'red'  ],
          [ 'value' => $stats['sent_week'],    'label' => __( 'Sent this week', 'postwave' ),   'color' => 'blue' ],
          [ 'value' => $stats['failed_week'],  'label' => __( 'Failed this week', 'postwave' ), 'color' => 'red'  ],
          [ 'value' => $stats['total'],        'label' => __( 'Total logged', 'postwave' ),     'color' => 'gray' ],
        ];
        foreach ( $stats_cfg as $s ) : ?>
        <div class="pw-stat pw-stat-<?php echo esc_attr( $s['color'] ); ?>">
          <strong><?php echo esc_html( $s['value'] ); ?></strong>
          <span><?php echo esc_html( $s['label'] ); ?></span>
        </div>
        <?php endforeach; ?>
      </div>

      <!-- ══ TAB: GENERAL ══ -->
      <?php if ( $tab === 'general' ) : ?>

      <form method="post" action="<?php echo $act; ?>">
        <?php wp_nonce_field( 'postwave_save' ); ?>
        <input type="hidden" name="action"          value="postwave_save">
        <input type="hidden" name="postwave[_tab]"  value="general">
        <!-- Preserve connection fields -->
        <input type="hidden" name="postwave[server_url]"       value="<?php echo esc_attr( $o( 'server_url' ) ); ?>">
        <input type="hidden" name="postwave[username]"         value="<?php echo esc_attr( $o( 'username' ) ); ?>">
        <!-- Preserve v1.1 connection-tab fields -->
        <input type="hidden" name="postwave[identity_id]"      value="<?php echo esc_attr( $o( 'identity_id' ) ); ?>">
        <input type="hidden" name="postwave[identity_name]"    value="<?php echo esc_attr( $o( 'identity_name' ) ); ?>">
        <input type="hidden" name="postwave[identity_email]"   value="<?php echo esc_attr( $o( 'identity_email' ) ); ?>">

        <!-- Enable card -->
        <div class="pw-panel">
          <div class="pw-panel-body">
            <input type="hidden" name="postwave[enabled]" value="0">
            <div class="pw-toggle-row">
              <div class="pw-toggle-info">
                <strong><?php esc_html_e( 'Enable Postwave JMAP', 'postwave' ); ?></strong>
                <span><?php esc_html_e( 'Route all WordPress emails through your JMAP server', 'postwave' ); ?></span>
              </div>
              <label class="pw-toggle">
                <input type="checkbox" name="postwave[enabled]" value="1" class="pw-toggle-cb" <?php checked( 1, $o( 'enabled' ) ); ?>>
                <span class="pw-toggle-track"><span class="pw-toggle-thumb"></span></span>
              </label>
            </div>
          </div>
        </div>

        <!-- Sender info card -->
        <div class="pw-panel">
          <div class="pw-panel-header">
            <h3><?php esc_html_e( 'Sender Information', 'postwave' ); ?></h3>
            <p><?php esc_html_e( 'The name and email address shown to recipients of outgoing mail.', 'postwave' ); ?></p>
          </div>
          <div class="pw-panel-body">
            <div class="pw-row-2">
              <div class="pw-field">
                <label for="pw-from-name"><?php esc_html_e( 'From Name', 'postwave' ); ?></label>
                <input type="text" id="pw-from-name" class="pw-input" name="postwave[from_name]"
                  value="<?php echo esc_attr( $o( 'from_name', get_bloginfo( 'name' ) ) ); ?>">
              </div>
              <div class="pw-field">
                <label for="pw-from-email"><?php esc_html_e( 'From Email', 'postwave' ); ?></label>
                <input type="email" id="pw-from-email" class="pw-input" name="postwave[from_email]"
                  value="<?php echo esc_attr( $o( 'from_email', get_bloginfo( 'admin_email' ) ) ); ?>">
              </div>
            </div>
            <div class="pw-field pw-field-half">
              <label for="pw-test-recip">
                <?php esc_html_e( 'Test recipient', 'postwave' ); ?>
                <em class="pw-label-opt"><?php esc_html_e( 'for Send Test Email button', 'postwave' ); ?></em>
              </label>
              <input type="email" id="pw-test-recip" class="pw-input" name="postwave[test_recipient]"
                value="<?php echo esc_attr( $o( 'test_recipient' ) ); ?>"
                placeholder="<?php esc_attr_e( 'Falls back to From Email', 'postwave' ); ?>">
            </div>
          </div>
          <div class="pw-panel-footer">
            <button type="submit" class="pw-btn pw-btn-primary"><?php esc_html_e( 'Save settings', 'postwave' ); ?></button>
          </div>
        </div>

        <!-- Automatic Retry panel -->
        <div class="pw-panel">
          <div class="pw-panel-header">
            <h3><?php esc_html_e( 'Automatic Retry', 'postwave' ); ?></h3>
            <p><?php esc_html_e( 'Automatically re-send failed emails using WP-Cron with exponential backoff.', 'postwave' ); ?></p>
          </div>
          <div class="pw-panel-body">
            <input type="hidden" name="postwave[retry_enabled]" value="0">
            <div class="pw-field-row pw-field-row--flex">
              <div class="pw-field-info">
                <label class="pw-label"><strong><?php esc_html_e( 'Enable retry queue', 'postwave' ); ?></strong></label>
                <p class="pw-desc"><?php esc_html_e( 'Failed sends will be retried automatically in the background.', 'postwave' ); ?></p>
              </div>
              <label class="pw-toggle">
                <input type="checkbox" name="postwave[retry_enabled]" class="pw-toggle-cb" value="1" <?php checked( $options['retry_enabled'] ?? 0 ); ?>>
                <span class="pw-toggle-track"><span class="pw-toggle-thumb"></span></span>
              </label>
            </div>

            <div class="pw-field-row pw-retry-options <?php echo empty( $options['retry_enabled'] ) ? 'pw-hidden' : ''; ?>">
              <div class="pw-col-2">
                <label class="pw-label" for="pw-retry-max"><?php esc_html_e( 'Max retry attempts', 'postwave' ); ?></label>
                <select id="pw-retry-max" name="postwave[retry_max]" class="pw-select">
                  <?php foreach ( array( 1, 2, 3, 4, 5 ) as $n ) : ?>
                    <option value="<?php echo esc_attr( $n ); ?>" <?php selected( (int) ( $options['retry_max'] ?? 3 ), $n ); ?>><?php echo esc_html( $n ); ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="pw-col-2">
                <label class="pw-label" for="pw-retry-delay"><?php esc_html_e( 'Initial retry delay', 'postwave' ); ?></label>
                <select id="pw-retry-delay" name="postwave[retry_delay]" class="pw-select">
                  <option value="300"  <?php selected( (int) ( $options['retry_delay'] ?? 300 ), 300 );  ?>><?php esc_html_e( '5 minutes', 'postwave' ); ?></option>
                  <option value="900"  <?php selected( (int) ( $options['retry_delay'] ?? 300 ), 900 );  ?>><?php esc_html_e( '15 minutes', 'postwave' ); ?></option>
                  <option value="1800" <?php selected( (int) ( $options['retry_delay'] ?? 300 ), 1800 ); ?>><?php esc_html_e( '30 minutes', 'postwave' ); ?></option>
                  <option value="3600" <?php selected( (int) ( $options['retry_delay'] ?? 300 ), 3600 ); ?>><?php esc_html_e( '1 hour', 'postwave' ); ?></option>
                </select>
                <p class="pw-desc"><?php esc_html_e( 'Delay doubles on each retry attempt (exponential backoff).', 'postwave' ); ?></p>
              </div>
            </div>

            <?php if ( $retry_count > 0 ) : ?>
            <div class="pw-notice pw-notice--info" style="margin-top:16px;">
              <?php printf(
                /* translators: %d: number of emails pending retry */
                esc_html( _n( '%d email is pending retry.', '%d emails are pending retry.', $retry_count, 'postwave' ) ),
                (int) $retry_count
              ); ?>
            </div>
            <?php endif; ?>
          </div>
        </div>

        <!-- Open Tracking panel -->
        <div class="pw-panel">
          <div class="pw-panel-header">
            <h3><?php esc_html_e( 'Open Tracking', 'postwave' ); ?></h3>
            <p><?php esc_html_e( 'Track when recipients open emails sent by your site. Opt-in only — read the privacy note below.', 'postwave' ); ?></p>
          </div>
          <div class="pw-panel-body">
            <input type="hidden" name="postwave[tracking_enabled]" value="0">
            <div class="pw-field-row pw-field-row--flex">
              <div class="pw-field-info">
                <label class="pw-label"><strong><?php esc_html_e( 'Enable open tracking', 'postwave' ); ?></strong></label>
                <p class="pw-desc"><?php esc_html_e( 'Embeds a 1×1 tracking pixel in outgoing HTML emails. Plain-text emails are never tracked.', 'postwave' ); ?></p>
              </div>
              <label class="pw-toggle">
                <input type="checkbox" name="postwave[tracking_enabled]" class="pw-toggle-cb" value="1" <?php checked( $options['tracking_enabled'] ?? 0 ); ?>>
                <span class="pw-toggle-track"><span class="pw-toggle-thumb"></span></span>
              </label>
            </div>
            <div class="pw-notice pw-notice--warning" style="margin-top:16px;">
              <strong><?php esc_html_e( 'Privacy:', 'postwave' ); ?></strong>
              <?php esc_html_e( 'Open tracking records when an email is opened. You may need to disclose this in your privacy policy. No personal data is sent to external servers — tracking is handled entirely on your own WordPress installation.', 'postwave' ); ?>
            </div>
          </div>
          <div class="pw-panel-footer">
            <button type="submit" class="pw-btn pw-btn-primary"><?php esc_html_e( 'Save settings', 'postwave' ); ?></button>
          </div>
        </div>

      </form>

      <!-- ══ TAB: CONNECTION ══ -->
      <?php elseif ( $tab === 'connection' ) : ?>

      <form method="post" action="<?php echo $act; ?>">
        <?php wp_nonce_field( 'postwave_save' ); ?>
        <input type="hidden" name="action"          value="postwave_save">
        <input type="hidden" name="postwave[_tab]"  value="connection">
        <!-- Preserve general fields -->
        <input type="hidden" name="postwave[enabled]"          value="<?php echo esc_attr( $o( 'enabled', 0 ) ); ?>">
        <input type="hidden" name="postwave[from_name]"        value="<?php echo esc_attr( $o( 'from_name' ) ); ?>">
        <input type="hidden" name="postwave[from_email]"       value="<?php echo esc_attr( $o( 'from_email' ) ); ?>">
        <input type="hidden" name="postwave[test_recipient]"   value="<?php echo esc_attr( $o( 'test_recipient' ) ); ?>">
        <!-- Preserve v1.1 general-tab fields -->
        <input type="hidden" name="postwave[retry_enabled]"    value="<?php echo esc_attr( $o( 'retry_enabled', 0 ) ); ?>">
        <input type="hidden" name="postwave[retry_max]"        value="<?php echo esc_attr( $o( 'retry_max', 3 ) ); ?>">
        <input type="hidden" name="postwave[retry_delay]"      value="<?php echo esc_attr( $o( 'retry_delay', 300 ) ); ?>">
        <input type="hidden" name="postwave[tracking_enabled]" value="<?php echo esc_attr( $o( 'tracking_enabled', 0 ) ); ?>">

        <div class="pw-panel">
          <div class="pw-panel-header">
            <h3><?php esc_html_e( 'Server Configuration', 'postwave' ); ?></h3>
            <p><?php esc_html_e( 'Your JMAP server URL, username, and password.', 'postwave' ); ?></p>
          </div>
          <div class="pw-panel-body">
            <div class="pw-field">
              <label for="pw-server-url"><?php esc_html_e( 'JMAP Server URL', 'postwave' ); ?></label>
              <input type="url" id="pw-server-url" class="pw-input" name="postwave[server_url]"
                value="<?php echo esc_attr( $o( 'server_url' ) ); ?>"
                placeholder="https://mail.example.com">
              <span class="pw-field-hint"><?php esc_html_e( 'Session is auto-discovered at /.well-known/jmap', 'postwave' ); ?></span>
            </div>
            <div class="pw-row-2">
              <div class="pw-field">
                <label for="pw-username"><?php esc_html_e( 'Username', 'postwave' ); ?></label>
                <input type="text" id="pw-username" class="pw-input" name="postwave[username]"
                  value="<?php echo esc_attr( $o( 'username' ) ); ?>" autocomplete="off">
              </div>
              <div class="pw-field">
                <label for="pw-password"><?php esc_html_e( 'Password', 'postwave' ); ?></label>
                <div class="pw-pass-wrap">
                  <input type="password" id="pw-password" class="pw-input" name="postwave[password]"
                    placeholder="<?php esc_attr_e( 'Leave blank to keep current', 'postwave' ); ?>"
                    autocomplete="new-password">
                  <button type="button" class="pw-eye-btn" data-for="pw-password" tabindex="-1"
                    aria-label="<?php esc_attr_e( 'Toggle password visibility', 'postwave' ); ?>">
                    <svg class="pw-eye-on"  viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                    <svg class="pw-eye-off" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" style="display:none"><path d="M17.94 17.94A10.07 10.07 0 0112 20c-7 0-11-8-11-8a18.45 18.45 0 015.06-5.94"/><path d="M9.9 4.24A9.12 9.12 0 0112 4c7 0 11 8 11 8a18.5 18.5 0 01-2.16 3.19"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                  </button>
                </div>
                <?php if ( ! empty( $options['password'] ) ) : ?>
                <span class="pw-field-hint pw-field-hint-ok">
                  <svg viewBox="0 0 20 20" fill="currentColor" width="12" height="12"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                  <?php esc_html_e( 'Password saved — leave blank to keep current', 'postwave' ); ?>
                </span>
                <?php endif; ?>
              </div>
            </div>
          </div>
          <div class="pw-panel-footer">
            <button type="submit" class="pw-btn pw-btn-primary"><?php esc_html_e( 'Save credentials', 'postwave' ); ?></button>
          </div>
        </div>

        <!-- Sender Identity panel -->
        <div class="pw-panel">
          <div class="pw-panel-header">
            <h3><?php esc_html_e( 'Sender Identity', 'postwave' ); ?></h3>
            <p><?php esc_html_e( 'Choose which JMAP sending identity to use. Auto-resolve matches the From email to an identity on your server.', 'postwave' ); ?></p>
          </div>
          <div class="pw-panel-body">
            <div class="pw-field">
              <label class="pw-label" for="pw-identity-select"><?php esc_html_e( 'Identity', 'postwave' ); ?></label>
              <div class="pw-identity-wrap">
                <div class="pw-identity-controls">
                  <select id="pw-identity-select" name="postwave[identity_id]" class="pw-select pw-identity-dropdown">
                    <option value=""><?php esc_html_e( '— Auto-resolve (recommended) —', 'postwave' ); ?></option>
                    <?php
                    $saved_id    = $options['identity_id'] ?? '';
                    if ( ! empty( $saved_id ) ) :
                      $saved_name  = sanitize_text_field( $options['identity_name'] ?? $saved_id );
                      $saved_email = sanitize_email( $options['identity_email'] ?? '' );
                      echo '<option value="' . esc_attr( $saved_id ) . '" selected>' . esc_html( $saved_name . ( $saved_email ? ' <' . $saved_email . '>' : '' ) ) . '</option>';
                    endif;
                    ?>
                  </select>
                  <!-- Hidden fields so the selected identity name/email survive the save round-trip -->
                  <input type="hidden" id="pw-identity-name"  name="postwave[identity_name]"  value="<?php echo esc_attr( $options['identity_name'] ?? '' ); ?>">
                  <input type="hidden" id="pw-identity-email" name="postwave[identity_email]" value="<?php echo esc_attr( $options['identity_email'] ?? '' ); ?>">
                  <button type="button" id="pw-load-identities" class="pw-btn pw-btn--secondary pw-btn-secondary">
                    <?php esc_html_e( 'Load identities', 'postwave' ); ?>
                  </button>
                </div>
                <p class="pw-desc" style="margin-top:6px;"><?php esc_html_e( 'Click "Load identities" to fetch the list from your JMAP server. Save credentials on the Connection tab first.', 'postwave' ); ?></p>
                <p id="pw-identity-status" class="pw-desc pw-hidden" style="margin-top:4px;"></p>
              </div>
            </div>
          </div>
          <div class="pw-panel-footer">
            <button type="submit" class="pw-btn pw-btn-primary"><?php esc_html_e( 'Save identity', 'postwave' ); ?></button>
          </div>
        </div>
      </form>

      <!-- Test connection panel (AJAX — outside form) -->
      <div class="pw-panel pw-panel-test">
        <div class="pw-panel-header">
          <h3><?php esc_html_e( 'Test Connection', 'postwave' ); ?></h3>
          <p><?php esc_html_e( 'Save your credentials first, then verify the server responds correctly.', 'postwave' ); ?></p>
        </div>
        <div class="pw-panel-body">
          <div class="pw-test-actions">
            <button type="button" class="pw-btn pw-btn-secondary" id="pw-test-conn">
              <svg viewBox="0 0 20 20" fill="currentColor" width="15" height="15"><path fill-rule="evenodd" d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
              <?php esc_html_e( 'Test connection', 'postwave' ); ?>
            </button>
            <button type="button" class="pw-btn pw-btn-secondary" id="pw-test-email">
              <svg viewBox="0 0 20 20" fill="currentColor" width="15" height="15"><path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z"/><path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z"/></svg>
              <?php esc_html_e( 'Send test email', 'postwave' ); ?>
            </button>
          </div>

          <div id="pw-steps" style="display:none">
            <div class="pw-step-row" id="pw-step-discover">
              <span class="pw-step-dot">○</span>
              <span><?php esc_html_e( 'Discovering JMAP session…', 'postwave' ); ?></span>
            </div>
            <div class="pw-step-row pw-step-row--pending" id="pw-step-identity">
              <span class="pw-step-dot">○</span>
              <span><?php esc_html_e( 'Resolving sender identity…', 'postwave' ); ?></span>
            </div>
            <div class="pw-step-row pw-step-row--pending" id="pw-step-done">
              <span class="pw-step-dot">○</span>
              <span><?php esc_html_e( 'Verifying capabilities…', 'postwave' ); ?></span>
            </div>
          </div>

          <div id="pw-test-result" style="display:none"></div>
        </div>
      </div>

      <!-- ══ TAB: LOG ══ -->
      <?php else : ?>

      <div class="pw-panel">
        <div class="pw-panel-header pw-panel-header-row">
          <div>
            <h3><?php esc_html_e( 'Mail Log', 'postwave' ); ?></h3>
            <p><?php esc_html_e( 'Last 100 send attempts. Bodies are never stored.', 'postwave' ); ?></p>
          </div>
          <?php if ( ! empty( $entries ) ) : ?>
          <div style="display:flex;gap:8px;align-items:center;flex-shrink:0;">
            <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="display:inline;">
              <input type="hidden" name="action" value="postwave_export_log">
              <?php wp_nonce_field( 'postwave_export_log' ); ?>
              <button type="submit" class="pw-btn pw-btn-secondary pw-btn--sm">
                &#8595; <?php esc_html_e( 'Export CSV', 'postwave' ); ?>
              </button>
            </form>
            <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="display:inline;">
              <input type="hidden" name="action" value="postwave_clear_log">
              <?php wp_nonce_field( 'postwave_clear_log' ); ?>
              <button type="submit" class="pw-btn pw-btn-danger pw-btn--sm"
                onclick="return confirm('<?php echo esc_js( __( 'Clear all log entries? This cannot be undone.', 'postwave' ) ); ?>')">
                <svg viewBox="0 0 20 20" fill="currentColor" width="13" height="13"><path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                <?php esc_html_e( 'Clear log', 'postwave' ); ?>
              </button>
            </form>
          </div>
          <?php endif; ?>
        </div>
        <div class="pw-panel-body pw-panel-body-flush">

          <?php if ( empty( $entries ) ) : ?>
          <div class="pw-empty-state">
            <div class="pw-empty-icon">
              <svg viewBox="0 0 48 48" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="6" y="10" width="36" height="28" rx="3"/><polyline points="6,16 24,26 42,16"/></svg>
            </div>
            <h4><?php esc_html_e( 'No emails logged yet', 'postwave' ); ?></h4>
            <p><?php esc_html_e( 'Sent and failed emails will appear here once Postwave is active.', 'postwave' ); ?></p>
          </div>
          <?php else : ?>
          <div class="pw-table-scroll">
            <table class="pw-table">
              <thead>
                <tr>
                  <th><?php esc_html_e( 'Status', 'postwave' ); ?></th>
                  <th><?php esc_html_e( 'Date / Time', 'postwave' ); ?></th>
                  <th><?php esc_html_e( 'To', 'postwave' ); ?></th>
                  <th><?php esc_html_e( 'Subject', 'postwave' ); ?></th>
                  <th><?php esc_html_e( 'Details', 'postwave' ); ?></th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ( $entries as $e ) :
                  $sent         = ( $e['status'] ?? '' ) === 'sent';
                  $ts           = (int) ( $e['timestamp'] ?? 0 );
                  $retry_status = $e['retry_status'] ?? '';
                  $opened_at    = $e['opened_at'] ?? null;
                ?>
                <tr>
                  <td>
                    <span class="pw-badge-pill pw-badge-pill-<?php echo $sent ? 'success' : 'danger'; ?>">
                      <?php echo $sent ? esc_html__( 'Sent', 'postwave' ) : esc_html__( 'Failed', 'postwave' ); ?>
                    </span>
                    <?php if ( 'retried' === $retry_status ) : ?>
                    <span class="pw-badge pw-badge--retried" title="<?php esc_attr_e( 'Sent via retry queue', 'postwave' ); ?>">
                      <?php echo esc_html( sprintf(
                        /* translators: %d: number of retries */
                        _n( 'retry %d', 'retry %d', (int) ( $e['retry_count'] ?? 1 ), 'postwave' ),
                        (int) ( $e['retry_count'] ?? 1 )
                      ) ); ?>
                    </span>
                    <?php elseif ( 'exhausted' === $retry_status ) : ?>
                    <span class="pw-badge pw-badge--exhausted" title="<?php esc_attr_e( 'All retry attempts exhausted', 'postwave' ); ?>">
                      <?php esc_html_e( 'exhausted', 'postwave' ); ?>
                    </span>
                    <?php endif; ?>
                    <?php if ( ! empty( $opened_at ) ) : ?>
                    <span class="pw-badge pw-badge--opened" title="<?php echo esc_attr( sprintf(
                      /* translators: %s: date/time */
                      __( 'Opened at %s', 'postwave' ),
                      wp_date( 'Y-m-d H:i', (int) $opened_at )
                    ) ); ?>">
                      <?php esc_html_e( 'opened', 'postwave' ); ?>
                    </span>
                    <?php endif; ?>
                  </td>
                  <td class="pw-td-mono"><?php echo esc_html( $ts ? wp_date( 'Y-m-d H:i', $ts ) : '—' ); ?></td>
                  <td class="pw-td-truncate"><?php echo esc_html( $e['to'] ?? '' ); ?></td>
                  <td class="pw-td-truncate"><?php echo esc_html( $e['subject'] ?? '' ); ?></td>
                  <td>
                    <button type="button" class="pw-detail-btn"><?php esc_html_e( 'Details', 'postwave' ); ?> ▾</button>
                    <dl class="pw-detail-panel" style="display:none">
                      <?php if ( ! empty( $e['from'] ) ) : ?>
                      <div><dt><?php esc_html_e( 'From', 'postwave' ); ?></dt><dd><code><?php echo esc_html( $e['from'] ); ?></code></dd></div>
                      <?php endif; ?>
                      <?php if ( ! empty( $e['account_id'] ) ) : ?>
                      <div><dt><?php esc_html_e( 'Account', 'postwave' ); ?></dt><dd><code><?php echo esc_html( $e['account_id'] ); ?></code></dd></div>
                      <?php endif; ?>
                      <?php if ( ! empty( $e['identity_id'] ) ) : ?>
                      <div><dt><?php esc_html_e( 'Identity', 'postwave' ); ?></dt><dd><code><?php echo esc_html( $e['identity_id'] ); ?></code></dd></div>
                      <?php endif; ?>
                      <?php if ( ! empty( $e['email_id'] ) ) : ?>
                      <div><dt><?php esc_html_e( 'Email ID', 'postwave' ); ?></dt><dd><code><?php echo esc_html( $e['email_id'] ); ?></code></dd></div>
                      <?php endif; ?>
                      <?php if ( ! empty( $opened_at ) ) : ?>
                      <div><dt><?php esc_html_e( 'Opened', 'postwave' ); ?></dt><dd><?php echo esc_html( wp_date( 'Y-m-d H:i:s', (int) $opened_at ) ); ?></dd></div>
                      <?php endif; ?>
                      <?php if ( ! empty( $e['error'] ) ) : ?>
                      <div class="pw-detail-error"><dt><?php esc_html_e( 'Error', 'postwave' ); ?></dt><dd><?php echo esc_html( $e['error'] ); ?></dd></div>
                      <?php endif; ?>
                    </dl>
                  </td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
          <?php endif; ?>

        </div>
      </div>

      <?php endif; /* tab switch */ ?>

    </main><!-- /.pw-main -->
  </div><!-- /.pw-app-body -->
</div><!-- #pw-page -->

<?php endif; /* wizard / dashboard */ ?>
