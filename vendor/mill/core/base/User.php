<?php

namespace mill\core\base;

/**
 * Description of User
 *
 * @author Yaroslav Palamarchuk
 */
class User {
    
    public $properties = [];

    
    public function __construct(){
        if($_SESSION['user']){
            foreach($_SESSION['user'] as $k => $v){
                $this->properties[$k] = $v;
            }
        }
    }
    
    public function login($login, $password){
        if($login && $password){
            $user = \R::findOne('user', 'login = ? LIMIT 1', [$login]);
            if($user){
                if(password_verify($password, $user->password)){
                    foreach ($user as $k => $v){
                        if($k != 'password'){
                            $_SESSION['user'][$k] = $v;
                            $this->properties[$k] = $v;
                        }
                    }
                    return true;
                }

            }
        }
        return false;
    }
    
    public function logout($redirect){
        $this->logined = false;
        if(isset($_SESSION['user'])) unset($_SESSION['user']);
        redirect($redirect);
    }
    
    public function property($name = ''){
        if($name){
            return $this->properties[$name];
        }else{
            return $this->properties;
        }
    }
 
    
    
}
