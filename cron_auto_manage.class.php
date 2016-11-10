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
    
    /**
     * 计划任务执行方法
     */
    public function run() {
    	
        $time = gmtime();
        $limit = !empty($this->configure['auto_manage_count']) ? $this->configure['auto_manage_count'] : 5;
        $sql = "SELECT * FROM " . $GLOBALS['ecs']->table('auto_manage') . " WHERE starttime > '0' AND starttime <= '$time' OR endtime > '0' AND endtime <= '$time' LIMIT $limit";
        $autodb = $db->getAll($sql);
        foreach ($autodb as $key => $val)
        {
            $del = $up = false;
            if ($val['type'] == 'goods')
            {
                $goods = true;
                $where = " WHERE goods_id = '$val[item_id]'";
            }
            else
            {
                $goods = false;
                $where = " WHERE article_id = '$val[item_id]'";
            }
        
        
            //上下架判断
            if(!empty($val['starttime']) && !empty($val['endtime']))
            {
                //上下架时间均设置
                if($val['starttime'] <= $time && $time < $val['endtime'])
                {
                    //上架时间 <= 当前时间 < 下架时间
                    $up = true;
                    $del = false;
                }
                elseif($val['starttime'] >= $time && $time > $val['endtime'])
                {
                    //下架时间 <= 当前时间 < 上架时间
                    $up = false;
                    $del = false;
                }
                elseif($val['starttime'] == $time && $time == $val['endtime'])
                {
                    //下架时间 == 当前时间 == 上架时间
                    $sql = "DELETE FROM " . $GLOBALS['ecs']->table('auto_manage') . "WHERE item_id = '$val[item_id]' AND type = '$val[type]'";
                    $db->query($sql);
                    continue;
                }
                elseif($val['starttime'] > $val['endtime'])
                {
                    // 下架时间 < 上架时间 < 当前时间
                    $up = true;
                    $del = true;
                }
                elseif($val['starttime'] < $val['endtime'])
                {
                    // 上架时间 < 下架时间 < 当前时间
                    $up = false;
                    $del = true;
                }
                else
                {
                    // 上架时间 = 下架时间 < 当前时间
                    $sql = "DELETE FROM " . $GLOBALS['ecs']->table('auto_manage') . "WHERE item_id = '$val[item_id]' AND type = '$val[type]'";
                    $db->query($sql);
        
                    continue;
                }
            }
            elseif(!empty($val['starttime']))
            {
                //只设置了上架时间
                $up = true;
                $del = true;
            }
            else
            {
                //只设置了下架时间
                $up = false;
                $del = true;
            }
        
            if ($goods)
            {
                if ($up)
                {
                    $sql = "UPDATE " . $GLOBALS['ecs']->table('goods') . " SET is_on_sale = 1 $where";
                }
                else
                {
                    $sql = "UPDATE " . $GLOBALS['ecs']->table('goods') . " SET is_on_sale = 0 $where";
                }
            }
            else
            {
                if ($up)
                {
                    $sql = "UPDATE " . $GLOBALS['ecs']->table('article') . " SET is_open = 1 $where";
                }
                else
                {
                    $sql = "UPDATE " . $GLOBALS['ecs']->table('article') . " SET is_open = 0 $where";
                }
            }
            $db->query($sql);
            if ($del)
            {
                $sql = "DELETE FROM " . $GLOBALS['ecs']->table('auto_manage') . "WHERE item_id = '$val[item_id]' AND type = '$val[type]'";
                $db->query($sql);
            }
            else
            {
                if($up)
                {
                    $sql = "UPDATE " . $GLOBALS['ecs']->table('auto_manage') . " SET starttime = 0 WHERE item_id = '$val[item_id]' AND type = '$val[type]'";
                }
                else
                {
                    $sql = "UPDATE " . $GLOBALS['ecs']->table('auto_manage') . " SET endtime = 0 WHERE item_id = '$val[item_id]' AND type = '$val[type]'";
                }
                $db->query($sql);
            }
        }
    }

}

// end