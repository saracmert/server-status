<?php
/*
 * Plugin Name: Server Status
 * Plugin URI: http://www.extendwings.com/
 * Description: Show server information widget in Dashboard and Network Admin Dashboard.(Currently, only RHEL is tested)
 * Version: 0.1.3b1
 * Author: Daisuke Takahashi(Extend Wings)
 * Author URI: http://www.extendwings.com
 * License: AGPLv3 or later
 * Text Domain: server-status
 * Domain Path: /languages/
*/

if(!function_exists('add_action')) {
	echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
	exit;
}

// Setup
add_action('wp_dashboard_setup', array('dashboard_widget', 'init'));
add_action('plugins_loaded', 'server_status_load_text_domain');
if(is_multisite())
	add_action('wp_network_dashboard_setup', array('dashboard_widget', 'init'));

// For unsupported version of PHP
if(version_compare(PHP_VERSION, '5.2.17', '<=')) {
	add_action('admin_notices', array('dashboard_widget', 'update_nag'), 3);
	add_action('network_admin_notices', array('dashboard_widget', 'update_nag'), 3);
	remove_action('wp_dashboard_setup', array('dashboard_widget', 'init'));
	if(is_multisite())
		remove_action('wp_network_dashboard_setup', array('dashboard_widget', 'init'));
}

function server_status_load_text_domain() {
	load_plugin_textdomain('server-status', false, dirname(plugin_basename(__FILE__)). '/languages/');
}

class dashboard_widget {
	const slug = 'server_status';

	function init() {
		wp_add_dashboard_widget(
			self::slug, // slug
			__('Server Status', 'server-status'), // title
			array(__CLASS__, 'display'), // display function
			array(__CLASS__, 'control') // control function
		);
	}

	function display() {
		if(false === ($data = get_site_transient('server_status_cache'))) {
			if(in_array(PHP_OS, array('Linux', 'Darwin'))) {
				?>
				<p><strong><?php printf(__('This plugin is not compatible with this OS!!(ID: %s)', 'server-status'), PHP_OS); ?></strong></p>
				<p><?php printf(__('To make this plugin compatible, please send me this server info via'
						.' <a href="%s" onclick="window.open(\'%s\', \'_blank\', \'%s\'); return false;">Email</a>'
						.' or <a href="%s">Plugin Support</a>.', 'server-status'),
					'http://goo.gl/jb5cqO', 'http://goo.gl/bqItKA',
					'toolbar=0,scrollbars=0,location=0,statusbar=0,menubar=0,resizable=0,width=500,height=300',
					'http://wordpress.org/support/plugin/server-status'); ?>
				</p>
				<?php
				$data['uptime'] = @shell_exec('uptime 2>&1');
				$data['proc']['uptime'] = @shell_exec('cat /proc/uptime 2>&1');
				$data['proc']['loadavg'] = @shell_exec('cat /proc/loadavg 2>&1');
				$data['w'] = @shell_exec('w 2>&1');
				$data['w_hs'] = @shell_exec('w -hs 2>&1');
				$data['who'] = @shell_exec('who 2>&1');
				$data['who_am_i'] = @shell_exec('who am i 2>&1');
				$data['whoami'] = @shell_exec('whoami 2>&1');
				$data['id_un'] = @shell_exec('id -un 2>&1');
				$data['users'] = @shell_exec('users 2>&1');
				$data['uname'] = @shell_exec('uname 2>&1');
				$data['uname_a'] = @shell_exec('uname -a 2>&1');
				$data['PHP_OS'] = PHP_OS;
				$data['PHP_uname'] = php_uname();
				?>
				<form><div class="textarea-wrap" id="description-wrap">
<textarea rows="12">

----<?php _e('If you have any comment, add it above this line', 'server-status'); ?>----
<?php var_export($data, true); ?>
====
LOCALE: <?php echo get_locale(); ?> 
PHP_VERSION: <?php echo PHP_VERSION; ?> 
WORDPRESS_VERSION: <?php echo get_bloginfo('version'); ?> 
PLUGIN_VERSION: <?php
	$plugin_data = get_plugin_data(__FILE__);
	echo $plugin_data['Version'];
	unset($plugin_data);
?> 
SITE_URL: <?php echo site_url(); ?> 
HOME_URL: <?php echo home_url(); ?> 
ADMIN_URL: <?php echo admin_url(); ?> 
PLUGINS_URL: <?php echo plugins_url(); ?> 
SERVER_SOFTWARE: <?php echo $_SERVER['SERVER_SOFTWARE']; ?> </textarea>
				</div></form>
				<?php
				return false;
			}

			$opt['colorize'] = self::get_dashboard_widget_option(self::slug, 'colorize');
			$opt['expiration'] = self::get_dashboard_widget_option(self::slug, 'expiration');
			if(empty($opt['expiration']))
				$opt['expiration'] = 60;

			switch(PHP_OS) {
				case 'Linux':
				case 'Darwin':
					$os = PHP_OS;
					break;
				default:
					$os = 'Linux';
			}
			$instance = 'widget_'. $os .'_data';
			$instance = new $instance();
			$data = $instance->fetch($opt);

			set_site_transient('server_status_cache', $data, $opt['expiration']);
			echo '<p>'. sprintf(__('Cache Expiration was set for %s sec.', 'server-status'), $opt['expiration']) .'</p>';
		} else {
			echo '<p><strong>'. __('Using Cached Data', 'server-status') .'</strong></p>';
		//	delete_site_transient('server_status_cache');
		}
		echo '<p>'.date('H:i:s').' up&nbsp;' .$data['uptime']. ', &nbsp;' .$data['users']. ', &nbsp;load&nbsp;average:&nbsp;' .$data['loadavg']. '</p>';
	}

	function control() {
		if(isset($_POST) && !empty($_POST)) {
			$opt['colorize'] = stripslashes($_POST['colorize'])==='true' ? true : false;
			$opt['expiration'] = stripslashes($_POST['expiration']);
			if(!is_numeric($opt['expiration']) || 1>=($opt['expiration'] = intval($opt['expiration'])))
					$opt['expiration'] = 60;

			self::update_dashboard_widget_options(
				self::slug, // slug
				$opt
			);
			delete_site_transient('server_status_cache');
		}
		$opt['colorize'] = self::get_dashboard_widget_option(self::slug, 'colorize');
		$opt['expiration'] = self::get_dashboard_widget_option(self::slug, 'expiration');
		if(empty($opt['expiration']))
			$opt['expiration'] = 60;
		?>
		<p>Thank you for using <span style="font-style:italic !important;">Server Status</span> plugin!</p>
		<p><label>
				<input type="checkbox" name="colorize" value="true" <?php if($opt['colorize']) echo 'checked="checked" '; ?>/>
				<?php _e('Enable Colorize', 'server-status'); ?>
		</label></p>
		<p>
			<label>
				<?php printf(__('Cached Data Expiration: %s sec.', 'server-status'), '<input type="number" name="expiration" value="' .$opt['expiration']. '" />'); ?>
			</label>
			<small><?php _e('Min: 2 sec, Step: 1 sec', 'server-status'); ?></small>
		</p>
		<?php
	}

	private function get_dashboard_widget_options($widget_id='') {
		$data = get_site_option('dashboard_widget_options');

		if(empty($widget_id))
			return false;

		if(isset($data[$widget_id]))
			return $data[$widget_id];

		return false;
	}

	private function get_dashboard_widget_option($widget_id, $option) {
		$data = self::get_dashboard_widget_options($widget_id);

		if(!$data)
			return false;

		if(isset($data[$option]) && !empty($data[$option]))
			return $data[$option];
		else
			return false;
	}

	private function update_dashboard_widget_options($widget_id , $args = array(), $add_only = false) {
		$data = get_site_option('dashboard_widget_options');
		$w_opts = isset($data[$widget_id]) ? $data[$widget_id] : array();

		if($add_only)
			$data[$widget_id] = array_merge($args,$w_opts); // Add Only(Do NOT override old options)
		else
			$data[$widget_id] = array_merge($w_opts,$args); // Update(Override old options)

		return update_site_option('dashboard_widget_options', $data);
	}

	function update_nag(){
		echo '<div class="error"><p>'. sprintf(__( 'You are using unsupported PHP branch(%s). We recommend upgrading immediately.', 'server-status' ), PHP_VERSION) .'</p></div>';
	}
}

abstract class widget_data {
	protected $data = array();
	protected $opt = array();
	private $src = array();

	function __construct() {
		$this->add_action('fetch', array(&$this,'uptime'));
		$this->add_action('fetch', array(&$this,'users'));
		$this->add_action('fetch', array(&$this,'loadavg'));
	}

	function fetch(array $opt) {
		$this->opt = &$opt;
		$this->do_action('fetch');
		return $this->data;
	}

	protected function add_action($name, $function_to_add, $priority = 10, $accepted_args = 0) {
		if(is_callable($function_to_add, true, $function_name) && is_int($priority) && is_int($accepted_args)) {
			if(!isset($this->src[$name][$priority]))
				$this->src[$name][$priority] = array();

			$this->src[$name][$priority] = array_merge(
				$this->src[$name][$priority],
				array(
					$function_name => array(
							'function' => $function_to_add,
							'accepted_args' => $accepted_args
					)
				)
			);
		}
	}

	protected function remove_action($name, $function_to_remove, $priority = 10) {
		if(isset($this->src[$name][$priority][$function_to_remove])) {
			unset($this->src[$name][$priority][$function_to_remove]);
			if(empty($this->src[$name][$priority]))
				unset($this->src[$name][$priority]);
		}
	}

	private function do_action($name) {
		ksort($this->src[$name], SORT_NUMERIC);
		foreach($this->src[$name] as $priority_group) {
			foreach($priority_group as $function_to_do => $function_to_do_detail) {
				call_user_func($function_to_do_detail['function']);
			}
		}
	}

	abstract protected function uptime();

	abstract protected function users();

	abstract protected function loadavg();

	static function uptime_string($uptime_sec) {
		$data['year'] = floor($uptime_sec / 31536000);
		if($data['year'] == 0)
			$data['year'] = NULL;
		else
			$data['year'] .= $data['year']<=1 ? ' year, ' : ' years, ';
		$uptime_sec %= 31536000;

 		$data['week'] = floor($uptime_sec / 604800);
		if($data['week'] == 0) {
			$data['week'] = NULL;
			if(!$data['year'])
				$data['year'] = substr($data['year'], 0, -2);
		} else
			$data['week'] .= $data['week']<=1 ? ' week, ' : ' weeks, ';
		$uptime_sec %= 604800;

		$data['day'] = floor($uptime_sec / 86400);
		if($data['day'] == 0) {
			$data['day'] = NULL;
			if(!$data['week'])
				$data['week'] = substr($data['week'], 0, -2);
		} else
			$data['day'] .= $data['day']<=1 ? ' day, ' : ' days, ';
		$uptime_sec %= 86400;

		$data['hour'] = str_pad(floor($uptime_sec / 3600), 2, '0', STR_PAD_LEFT) .':';
		if($data['hour'] == '00:') {
			$data['hour'] = NULL;
			if(!$data['day'])
				$data['day'] = substr($data['day'], 0, -2);
		}
		$uptime_sec %= 3600;

		$data['min'] = floor($uptime_sec / 60);
		if(empty($data['hour'])) {
			if($data['min'] == '00')
				$data['min'] = NULL;
			else
				$data['min'] .= $data['min']<=1 ? ' min' : ' mins';
		} else
			$data['min'] = str_pad($data['min'], 2, '0', STR_PAD_LEFT);
		//$uptime_sec %= 60;

		//$data['sec'] = str_pad($uptime_sec, 2, '0', STR_PAD_LEFT);

		$data = implode($data);
		if(empty($data))
			$data = '<span style="color:limegreen;font-weight:bold;">' .__('Uptime data is unavailable!', 'server-status'). '</span>';

		unset($uptime_sec);
		return $data;
	}
}

class widget_Linux_data extends widget_data {
	protected function uptime() {
		// Uptime Data
		$this->data['uptime'] = @exec('cat /proc/uptime');
		$this->data['uptime'] = explode(' ', $this->data['uptime']);
		$this->data['uptime'] = floor($this->data['uptime'][0]);
		$this->data['uptime'] = parent::uptime_string($this->data['uptime']);
	}

	protected function users() {
		// Logged in User Data
		$this->data['users'] = @exec('users');
		$this->data['users'] = count(array_filter(explode(' ', $this->data['users'])));
		$this->data['users'] = sprintf(_n('%d user', '%d users', $this->data['users']), $this->data['users']);
	}

	protected function loadavg() {
		// Load Average Data
		$this->data['loadavg'] = @exec('cat /proc/loadavg');
		$this->data['loadavg'] = array_chunk(explode(' ', $this->data['loadavg']), 3, true);
		$this->data['loadavg'] = $this->data['loadavg'][0];
		if($this->opt['colorize']) {
			foreach($this->data['loadavg'] as &$loadavg) {
				if($loadavg >= 1.0)
					$loadavg = '<span style="color:red;font-weight:bold;">' .$loadavg. '</span>';
			}
			unset($loadavg);
		}
		$this->data['loadavg'] = implode(',&nbsp;', $this->data['loadavg']);
	}

}

class widget_Darwin_data extends widget_data {
	protected function uptime() {
		// Uptime Data
		$this->data['boottime'] = @exec('sysctl kern.boottime');
		//$this->data['uptime_sec'] = 'kern.boottime: { sec = 1271934886, usec = 667779 } Thu Apr 22 12:14:46 2010';
		$this->data['boottime'] = str_replace('kern.boottime: { sec = ', '', $this->data['boottime']);
		$this->data['boottime'] = explode(', ', $this->data['boottime']);
		$this->data['uptime'] = microtime(true) - $this->data['boottime'][0];
		$this->data['uptime'] = parent::uptime_string($this->data['uptime']);
		unset($this->data['boottime']);
	}

	protected function users() {
		// Logged in User Data
		$this->data['users'] = @exec('users');
		$this->data['users'] = count(array_filter(explode(' ', $this->data['users'])));
		$this->data['users'] .= sprintf(_n('%d user', '%d users', $this->data['users']), $this->data['users']);
	}

	protected function loadavg() {
		// Load Average Data
		$this->data['loadavg'] = @exec('sysctl vm.loadavg');
		//$this->data['loadavg'] = 'vm.loadavg: { 0.03 0.09 0.05 }';
		$this->data['loadavg'] = str_replace(array('vm.loadavg: { ', ' }'), '', $this->data['loadavg']);
		$this->data['loadavg'] = explode(' ', $this->data['loadavg']);
		if($this->opt['colorize']) {
			foreach($this->data['loadavg'] as &$loadavg) {
				if($loadavg >= 1.0)
					$loadavg = '<span style="color:red;font-weight:bold;">' .$loadavg. '</span>';
			}
			unset($loadavg);
		}
		$this->data['loadavg'] = implode(',&nbsp;', $this->data['loadavg']);
	}
}

?>
