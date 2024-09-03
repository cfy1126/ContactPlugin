<?php
/**
 * 
 * Plugin name: Contact Plugin
 * Description: Plugin for my custom contact form
 * Version: 1.0.0
 * Text Domain: contact-plugin
 * 
 */

if( !defined('ABSPATH') )
{
      die('You cannot be here');
}

if( !class_exists('ContactPlugin') )
{



            class ContactPlugin{


                  public function __construct()
                  {

                        /**
                         * plugin_dir_path:
                              * 作用：返回插件目录的文件系统路径。
                              * 返回值：文件系统路径（例如：/var/www/html/wp-content/plugins/my-plugin/）。
                              * 使用场景：当你需要在服务器文件系统中定位插件文件时使用，例如包含文件或读取文件内容。

                         * plugin_dir_url:
                              * 作用：返回插件目录的 URL。
                              * 返回值：URL（例如：http://example.com/wp-content/plugins/my-plugin/）。
                              * 使用场景：当你需要在浏览器中访问插件文件时使用，例如加载插件的 CSS、JavaScript 或图像文件。
                         */
                        define('MY_PLUGIN_PATH', plugin_dir_path( __FILE__ ));

                        define('MY_PLUGIN_URL', plugin_dir_url( __FILE__ ));

                        require_once(MY_PLUGIN_PATH . '/vendor/autoload.php');

                  }

                  /***
                   * require_once 在文件缺失时会停止脚本执行，适用于必须包含的文件。
                   * include_once 在文件缺失时会发出警告，但继续执行脚本，适用于可选文件。
                   */
                  public function initialize()
                  {
                        include_once MY_PLUGIN_PATH . 'includes/utilities.php';

                        include_once MY_PLUGIN_PATH . 'includes/options-page.php';

                        include_once MY_PLUGIN_PATH . 'includes/contact-form.php';
                  }


            }

            $contactPlugin = new ContactPlugin;
            $contactPlugin->initialize();

}