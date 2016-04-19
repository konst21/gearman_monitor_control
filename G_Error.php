<?php
include_once 'classes/Gearman_Db.php';
/**
 * Пишем ошибки в БД
 */
class G_Error
{
    public static function capture_normal( $number, $message, $file, $line )
    {
        $db = new Gearman_Db();
        $db->log_insert($message, $number, $file, $line);
    }

    public static function capture_exception( $exception )
    {
        $db = new Gearman_Db();
        $db->log_insert($exception, 'Exception', null, null);
    }

    public static function captureShutdown()
    {
        $error = error_get_last();
        if( $error ) {
            $db = new Gearman_Db();
            $db->log_insert($error, 'Shutdown', null, null);
        } else { return true; }
    }
}

ini_set( 'display_errors', 1 );
error_reporting( -1 );
set_error_handler( array( 'G_Error', 'capture_normal' ) );
set_exception_handler( array( 'G_Error', 'capture_exception' ) );
register_shutdown_function( array( 'G_Error', 'captureShutdown' ) );

/*// PHP set_error_handler TEST
IMAGINE_CONSTANT;

// PHP set_exception_handler TEST
throw new Exception( 'Imagine Exception' );

// PHP register_shutdown_function TEST ( IF YOU WANT TEST THIS, DELETE PREVIOUS LINE )
imagine_function( );*/

