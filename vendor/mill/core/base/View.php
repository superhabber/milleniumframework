<?php

namespace mill\core\base;

/**
 * !DO NOT EDIT this file without knowledge of what you are doing
 * @author Yaroslav Palamarchuk
 */
class View {

    /**
     * for real route
     * @var $route array
     * */
    public $route = [];

    /**
     * for view file 
     * @var $view string
     * */
    public $view;

    /**
     * for layout file
     * @var $layout string
     * */
    public $layout;

    /**
     * title of page
     * @var string 
     */
    public $title;

    /**
     * description of page
     * @var string 
     */
    public $description;

    /**
     * keywords of page 
     * @var string 
     */
    public $keywords;

    /**
     * css and js files
     * @var array 
     */
    public $scripts = [];

    /**
     * register view and layout
     * @param array $route for real routes
     * */
    public function __construct($route, $layout = '', $view = '') {
        $this->route = $route;
        if ($layout === false) {
            $this->layout = false;
        } else {
            $this->layout = $layout ?: LAYOUT;
        }
        $this->view = $view;

    }

    protected function compressPage($buffer) {
        $search = [
            "/(\n)+/",
            "/\r\n+/",
            "/\n(\t)+/",
            "/\n(\ )+/",
            "/\>(\n)+</",
            "/\r\n</"
        ];
        $replace = [
            "\n",
            "\n",
            "\n",
            "\n",
            '><',
            '><'
        ];
        return preg_replace($search, $replace, $buffer);
    }

    /**
     * for variables from controller to view
     * @param array $vars for variables
     * */
    public function render($vars, $metatags, $scripts) {
        $this->route['prefix'] = str_replace('\\', '/', $this->route['prefix']);
        /**
         * you can get this var in your layout and without function scripts() get all scripts
         */
        $scripts = $this->getScripts($scripts);
        //if variables in array get it
        if (is_array($vars))
            extract($vars);
        /**
         * view file name
         * @var string
         */
        $file_view = APP . "/views/{$this->route['prefix']}{$this->route['controller']}/{$this->view}.php";
        if(GZIP){
            ob_start([$this, 'compressPage']);
        }else{
            ob_start();
        }

        //if file exists
        if (is_file($file_view)) {
            require $file_view;
        } else {
            throw new \Exception("<b>Вид {$file_view} не найден</b>", 404);
        }
        /**
         * content from view file
         * @var string
         */
        $content = ob_get_contents();
        ob_clean();

        $this->title = $metatags['title'];
        $this->description = $metatags['description'];
        $this->keywords = $metatags['keywords'];

        if (false !== $this->layout) {
            /**
             * layout file full path
             * @var string
             */
            $file_layout = APP . "/views/layouts/{$this->layout}.php";
            if (is_file($file_layout)) {
                require $file_layout;
            } else {
                throw new \Exception('<b>Шаблон '. $file_layout . ' не найден</b>', 404);
            }
        }else{
            echo $content;
        }
    }

    public function getScripts($scripts) {
        $s = require ROOT . '/config/scripts.php';
        /**
         * first array loop
         */
        foreach ($s as $name => $script) {
            foreach ($script as $sc) {
                $this->scripts[$name][] = $sc;
            }
        }
        /**
         * second array loop
         */
        foreach ($scripts as $name => $script) {
            foreach ($script as $sc) {
                $this->scripts[$name][] = $sc;
            }
        }
        return $this->scripts;
    }

    /**
     * makes script tags
     */
    public function scripts() {
        foreach ($this->scripts['js'] as $script) {
            echo "<script src='" . $script . "'> </script>";
        }
    }
    
    public function miniscripts($js) {
        if (\mill\core\App::$app->cache->get($js)) {
            if(!file_exists(ROOT . '/public/js/minified/' . $js )){
                file_put_contents(ROOT . '/public/js/minified/' . $js, \mill\core\App::$app->cache->get($js));
            }
        } else {
            $minified = '';
            foreach ($this->scripts['js'] as $script) {
                if (preg_match("/https:/", $script, $match)) {
                    $s = file_get_contents($script);
                } else {
                    $s = file_get_contents('http://' . \mill\html\Url::domain() . '/' . $script);
                }
                $minified .= $s;
            }
            $search = [
                "/(\n)+/",
                "/\r\n+/",
                "/\n(\t)+/",
                "/\n(\ )+/",
                "/\>(\n)+</",
                "/\r\n</"
            ];
            $replace = [
                "\n",
                "\n",
                "\n",
                "\n",
                '><',
                '><'
            ];
            $data = preg_replace($search, $replace, $minified);
            
            \mill\core\App::$app->cache->set($js, $data);
            
        }
        echo "<script src='/js/minified/$js'></script>";
    }

    public function styles() {
        foreach ($this->scripts['css'] as $script) {
            echo "<link href='/" . ltrim($script, '/') . "' rel='stylesheet'>";
        }
    }

}
