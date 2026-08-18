<?php

namespace KOMUNA;

class Widgets_Loader{
    public static $_instance = null;
    

    protected $theme_path;

    public static function instance(){
        if(is_null(self::$_instance)){
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    public function __construct(){
        $this->theme_path = plugin_dir_path(__FILE__);
        add_action( 'elementor/widgets/widgets_registered', [$this, 'register_widgets'], 99 );
    }

    private function include_widgets_files(){
        require_once(__DIR__ . '/widgets/list/list.php');
    }

    public function register_widgets(){
        $this->include_widgets_files();

        \Elementor\Plugin::instance()->widgets_manager->register_widget_type(new Widgets\ListWidget());
    }

}

Widgets_Loader::instance();