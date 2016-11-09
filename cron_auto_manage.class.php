<?php
/**
 * 自动处理插件
 */
defined('IN_ECJIA') or exit('No permission resources.');

RC_Loader::load_app_class('cron_abstract', 'cron', false);

class cron_auto_manage extends cron_abstract
{
    /**
     * 获取插件配置信息
     */
    public function configure_config() {
        $config = include(RC_Plugin::plugin_dir_path(__FILE__) . 'config.php');
        if (is_array($config)) {
            return $config;
        }
        return array();
    }
    
    public function get_prepare_data() {
        $predata = array(
            'cron_code'     => $this->configure['cron_code'],
            'cron_name'     => $this->configure['cron_name'],
//         	'pay_online'   => $this->configure['pay_account_info'],
        );
        
        return $predata;
    }
    
    public function notify() {
    	 
        return;
    }
    
    public function response() {
    	 
        return;
    }

}

// end