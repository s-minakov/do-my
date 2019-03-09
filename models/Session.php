<?php

class Session 
{
     
    public function __construct()      
    {
        global $mysqli; 
        
        ini_set('session.gc_maxlifetime', SESSION_LIFE); //ВРЕМЯ ХРАНЕИЯ КУКИ И СЕССИИ
        session_set_cookie_params(COOKIE_LIFE);

        session_start();

        $check = $mysqli->real_escape_string($_SERVER['REMOTE_ADDR']);

        if( !isset($_SESSION['user_answer']) ) {
            $_SESSION['user_answer']= array();
        } 

        if( !isset($_SESSION['bot']) ) { // ПРОВЕРЯЕМ IP АДРЕС ПО СТОП ЛИСТУ и ПО УМОЛЧАНИЮ УСТАНАВЛИВАЕТСЯ ОТСУТСТВИЕ УГРОЗУ ОПАСНОСТИ БОТА
            $query = "SELECT * FROM stop_list WHERE ip = $check";
            $result = $mysqli->query($query);
            if ($result == false) {$_SESSION['bot'] = 0;}
            else {$_SESSION['bot'] = 2;}
        }
        $_SESSION['time_start'] = time();

    }
}