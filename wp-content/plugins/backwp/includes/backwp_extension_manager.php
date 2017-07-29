<?php
/**
 * Manage BackWP Extensions
 */
class BackWP_Extension_Manager {
	private	$key = 'c7d97d59e0af29b2b2aa3ca17c695f96';

	public static function construct() {
		return new self();
	}

	public function __construct() {
		if (!get_option('backwp-premium-extensions'))
			add_option('backwp-premium-extensions', array(), null, 'no');
	}

	public function get_key() {
		return $this->key;
	}

	public function get_url() {
		return 'http://backwp.com';
	}

	public function get_install_url() {
		return 'admin.php?page=backwp-premium-extensions';
	}

	public function get_buy_url() {
		return $this->get_url() . '/buy/index.php';
	}

	public function get_extensions() {
    $extensions = array(
                   array('name' => 'Email Notification', 'file' => 'backwp_email_notification_extension', 'price' => '12', 'description' => 'Send you a email when the backup finished.', 'buy_url' => 'http://backwp.com/?pfd_checkout=1'),
                   array('name' => 'Cron Jobs', 'file' => 'backwp_cron_jobs_extension', 'price' => '19', 'description' => 'Scheduled backups using wp internal cron or Your server unix cron jobs.', 'buy_url' => 'http://backwp.com/?pfd_checkout=2'),         
                   array('name' => 'Backup Exclusions', 'file' => 'backwp_exclusions_extension', 'price' => '15', 'description' => 'Exclude tables on database backup or directories on files backup.', 'buy_url' => 'http://backwp.com/?pfd_checkout=3'),
                   array('name' => 'FTP Storage', 'file' => 'backwp_ftp_storage_extension', 'price' => '19', 'description' => 'Save your backup archives on remote FTP server.', 'buy_url' => 'http://backwp.com/?pfd_checkout=4')
                  );
    
    return $extensions;    		
	}
  /*
	public function get_installed() {
		$extensions = get_option('backwp-premium-extensions');
		if (!is_array($extensions))
			return array();

		return $extensions;
	}

	public function install($name, $file) {
		@umask(0000);

		if (!defined('FS_METHOD'))
			define('FS_METHOD', 'direct');

		WP_Filesystem();

		$params = array(
			'key' => $this->key,
			'name' => $name,
			'site' => get_site_url(),
			'version' => BACKUP_TO_DROPBOX_VERSION,
		);

		$download_file = download_url("{$this->get_url()}/download?" . http_build_query($params));

		if (is_wp_error($download_file)) {
			$errorMsg = $download_file->get_error_messages();
			throw new Exception(__('There was an error downloading your premium extension') . ' - ' . $errorMsg[0]);
		}

		$result = unzip_file($download_file, EXTENSIONS_DIR);
		if (is_wp_error($result)) {
			$errorMsg = $result->get_error_messages();
			if ($errorMsg[0] == "Incompatible Archive.")
				$errorMsg[0] = file_get_contents($download_file);

			unlink($download_file);
			throw new Exception(__('There was an error installing your premium extension') . ' - ' . $errorMsg[0]);
		}

		unlink($download_file);

		$this->activate($name, $file);
	}

	public function activate($name, $file) {
		$extensions = get_option('backwp-premium-extensions');
		$extensions[$name] = $file;
		update_option('backwp-premium-extensions', $extensions);
	}

	public function init() {
		$installed = $this->get_installed();
		$active = array();
		foreach ($installed as $name => $file) {
			if (file_exists(EXTENSIONS_DIR . $file)) {
				include_once EXTENSIONS_DIR . $file;
				$active[$name] = $file;
			}

		}
		update_option('backwp-premium-extensions', $active);
	}
  
  */
}





