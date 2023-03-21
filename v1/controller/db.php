<?php 
class DB {
    private static $writeDBConnection;
    private static $readDBConnection;

    public static function connectWriteDB(){
        if(self::$writeDBConnection == null){
            self::$writeDBConnection = new PDO('mysql:host=212.1.208.151;dbname=u239669380_gyle;', 'u239669380_gyle', '7#qWhy@2mSJyiyd');
            self::$writeDBConnection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            self::$writeDBConnection->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        }
        return self::$writeDBConnection;
    }

    public static function connectReadDB(){
        if(self::$readDBConnection == null){
            self::$readDBConnection = new PDO('mysql:host=212.1.208.151;dbname=u239669380_gyle;', 'u239669380_gyle', '7#qWhy@2mSJyiyd');
            self::$readDBConnection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            self::$readDBConnection->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        }
        return self::$readDBConnection;
    }
}