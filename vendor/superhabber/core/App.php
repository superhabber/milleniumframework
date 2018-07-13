<?php

namespace mill\core;

/**
 * Description of App
 * Main Class for Application
 * @author Yaroslav Palamarchuk
 */
class App {

    public static $app;
    
    public static $bin;

    public function __construct() {
        
        session_start();
        
        self::$app = Registry::instance();
//        
//        \Symfony\Component\Debug\Debug::enable();
        new ErrorHandler();
        self::$bin = new Props();
        
        
    }

}