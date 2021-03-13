<?php


namespace ICT\Core;

/*echo "<pre>";
print_r( \ICT\Core\DB::column_list('configuration'));
echo "</pre>";*/

/*echo "statistics:".PHP_EOL;
$aStatic = \ICT\Core\Core::statistic();
print_r($aStatic);*/

/*
echo "user:".PHP_EOL;
$user = \ICT\Core\User::search(array('user_id'=>1));
print_r($user);*/

class Test
{
    public function __construct()
    {
        echo "const";
    }

    public static function login()
    {
        \ICT\Core\do_login(1);
    }

    // \ICT\Core\Test::user(); from cli
    public static function user()
    {
        echo "user:" . PHP_EOL;
        $user = \ICT\Core\User::search(array('user_id' => 1));
        print_r($user);
    }

    public static function statistics()
    {
        echo "statistics:" . PHP_EOL;
        $aStatic = \ICT\Core\Core::statistic();
        print_r($aStatic);
    }

    public static function auth()
    {
        //$aStatic = \ICT\Core\Api\AuthenticateApi::create(['username'=>'sxkjk','password'=>'sxkdskk']);
        echo \ICT\Core\Api::authenticate(['username'=>'banna','password'=>'alio1610'],User::AUTH_TYPE_DIGEST);
      //  print_r($aStatic);
    }



}