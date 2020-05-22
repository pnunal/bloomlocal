<?php

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Bloomlocal_Updater {

    protected $current_version;
    protected $plugin_slug;
    protected $slug;
    protected $info;
    protected $transient_key;

    public function __construct($current_version, $plugin_slug) {
        $this->current_version = $current_version;
        $this->plugin_slug = $plugin_slug;
        list($t1, $t2) = explode('/', $plugin_slug);
        $this->slug = str_replace('.php', '', $t2);

        $this->transient_key = sprintf('update_plugins_%s_version', $this->slug);

        $this->info = (object) array(
            'slug' => $this->slug,
            'name' => ucwords($this->slug),
            'plugin_name' => ucwords($this->slug),
            'plugin_slug' => $this->plugin_slug,
            'url' => 'http://bloomlocal.net',
        );
    }

    public function check_update($transient) {
        if (empty($transient->checked)) {
			return $transient;
        }

        $obj = $this->get_info();

		// If a newer version is available, add the update
		if (version_compare($this->current_version, $obj->new_version, '<')) {
			$transient->response[$this->plugin_slug] = $obj;
        }

		return $transient;
    }

    public function check_info($info, $action, $arg) {
        if (($action == 'query_plugins' || $action == 'plugin_information') && isset($arg->slug) && $arg->slug === $slug) {
            $obj = $this->get_info();
            $obj->requires = '5.4';
            $obj->tested = '5.4';
            $obj->last_updated = '2020-05-22';
            $obj->sections = array(
                'description' => sprintf('Latest version: <a href="https://github.com/rroble/bloomlocal/releases/latest">%s</a>', $obj->new_version),
            );
            $obj->download_link = $obj->package;

            return $obj;
		}
		
		return $info;
    }

    protected function get_info() {
        $this->info->new_version = $this->get_new_version();
        $this->info->package = sprintf('https://github.com/rroble/bloomlocal/releases/download/v%s/bloomlocal-%s.zip',
                                $this->info->new_version, $this->info->new_version);
        return $this->info;
    }

    protected function get_new_version() {
        if (false !== ($version = get_transient($this->transient_key))) {
            return $version;
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://github.com/rroble/bloomlocal/releases/latest');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $output = curl_exec($ch); 
        $tag = explode('tag/v', $output);
        if (isset($tag[1])) {
            $vers = explode('"', $tag[1]);
            if (isset($vers[0])) {
                set_transient($this->transient_key, $vers[0], 43200); // 12 hours cache
                return $vers[0];
            }
        }

        return $this->current_version;
    }

    static public function init($current_version, $plugin_slug) {
        $plugin = new static($current_version, $plugin_slug);
        add_filter('pre_set_site_transient_update_plugins', array($plugin, 'check_update'));
        add_filter('plugins_api', array($plugin, 'check_info'), 10, 3);
    }
}
