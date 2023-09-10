<?php

use Predis\Client;

/**
 *
 * pluginManager Class
 *
 * 插件机制的实现核心类
 *
 * @link https://www.jb51.net/article/51980.htm
 */
class app extends api
{
    /**
     *
     * 监听已注册的插件
     *
     * @access private
     * @var array
     */
    private $_listeners = array();

    /**
     *
     * 构造函数
     *
     * @access public
     * @return void
     */
    public function __construct()
    {
    }

    /**
     *
     * 链接 Redis
     *
     */
    public function linkRedis()
    {
        $redisConfig = APP_REDIS_CONFIG;
        //Redis

        $this->redis = new Client(
            array("host" => $redisConfig[0][0], "port" => $redisConfig[0][2]),
            $redisConfig ? array("parameters" => array("password" => $redisConfig[0][1])) : NULL
        );

        //$this->redisSelect(1);
    }

    /**
     *
     * 返回所有插件名称和路径
     *
     * @return array() $plugins 返回数组包含每组插件$name:插件名称，也是php文件名；$directory:插件所在路径
     */
    public function getPlugins()
    {
        $dir = APP_DIR_PLUGINS;
        $scanDir = scandir($dir);

        $plugins = array();
        foreach ($scanDir as $name) {
            if (!preg_match("/^\./i", $name)) {
                $plugins[] = array(
                    'name' => $name,
                    //不带后缀 例如:hitokoto
                    'path' => $dir . $name
                );
            }
        }

        return $plugins;
    }

    /**
     *
     * 运行启用的插件
     *
     */
    public function runPlugins($plugins)
    {
        foreach ($plugins as $plugin) {
            $pluginPath = $plugin['path'];

            $configPath = $pluginPath . "/config.json";
            $reqRet = file_get_contents($configPath);
            $resJson = json_decode($reqRet);

            if (!$resJson->switch)
                continue;

            $actionsPath = $pluginPath . "/actions.php";
            if (@file_exists($actionsPath)) {
                include_once($actionsPath);

                $pluginName = $plugin['name'];
                $class = $pluginName . '_actions';

                if (class_exists($class)) {
                    //初始化所有插件
                    //$this 是本类的引用
                    new $class($this);
                }
            }
        }
        //假定每个插件文件夹中包含一个actions.php文件，它是插件的具体实现
    }

    /**
     *
     * 注册需要监听的插件方法（钩子）
     *
     * @param string $hook
     * @param object $reference
     * @param string $method
     */
    public function register($hook, &$reference, $method)
    {
        //获取插件要实现的方法
        $key = get_class($reference) . '->' . $method;
        //将插件的引用连同方法push进监听数组中
        $this->_listeners[$hook][$key] = array(&$reference, $method);
        //此处做些日志记录方面的东西
    }

    /**
     *
     * 触发一个钩子
     *
     * @param string $hook 钩子的名称
     * @param mixed $data 钩子的入参
     * @return mixed
     */
    public function trigger($hook, $data = '')
    {
        $result = '';
        //查看要实现的钩子，是否在监听数组之中
        if (isset($this->_listeners[$hook]) && is_array($this->_listeners[$hook]) && count($this->_listeners[$hook]) > 0) {
            //循环调用开始
            foreach ($this->_listeners[$hook] as $listener) {
                //取出插件对象的引用和方法
                $class = &$listener[0];
                $method = $listener[1];
                if (method_exists($class, $method)) {
                    //动态调用插件的方法
                    $result .= $class->$method($data);
                }
            }
        }
        //此处做些日志记录方面的东西

        return $result;
    }
}