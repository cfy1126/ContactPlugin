<?php

if( !defined('ABSPATH') )
{
      die('You cannot be here');
}

function get_plugin_options($name)
{
      // carbon fields 从主题容器中检索字段值
      return carbon_get_theme_option( $name );
}