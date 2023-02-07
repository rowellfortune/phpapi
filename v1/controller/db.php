<?php 

class DB {
    private static $writeDBConnection;
    private static $readDBConnection;

    public static function connectWriteDB(){
        if(self::$writeDBConnection == null){
            // self::$writeDBConnection = new PDO('mysql:host=localhost;dbname=dating;', 'root', 'root');
            self::$writeDBConnection = new PDO('mysql:host=191.101.79.103;dbname=u618358202_pt;', 'u618358202_pt', '7#qWhy@2mSJyiyd');
            self::$writeDBConnection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            self::$writeDBConnection->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        }
        return self::$writeDBConnection;
    }

    public static function connectReadDB(){
        if(self::$readDBConnection == null){
            // self::$readDBConnection = new PDO('mysql:host=localhost;dbname=dating;', 'root', 'root');
            self::$readDBConnection = new PDO('mysql:host=191.101.79.103;dbname=u618358202_pt;', 'u618358202_pt', '7#qWhy@2mSJyiyd');
            self::$readDBConnection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            self::$readDBConnection->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        }
        return self::$readDBConnection;
    }
}