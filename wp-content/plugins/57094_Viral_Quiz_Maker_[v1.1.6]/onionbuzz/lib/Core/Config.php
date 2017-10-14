<?php
namespace WpPluginAutoload\Core;

class Config{

    public $config = array();

    public function __construct()
    {
        $this->config = parse_ini_file(MYPLUGIN__PLUGIN_URL."config.ini", true);
    }
    public function get(){
        #echo '<pre>';
        #print_r($this->config);
        #echo '</pre>';
        return $this->config;
    }

}