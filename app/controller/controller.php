<?php
/**
 * Created by Wikibyte.org
 * User: McCouman
 * Date: 12.12.15
 * Time: 19:22
 */


/**
 * Class controller
 *
 * @info node app-controller
 */
class controller {

    function settings( $f3 ) {

        global $serverPath;
        global $datum;
        global $login;


        //--- Settings: Base
        require_once( 'settings/base.php' );

        //--- Settings: Attr.-Vars
        require_once( 'settings/attr.php' );

        //--- Settings: Global Functions
        require_once( 'settings/functions.php' );

        //--- Route: Website & Extension
        require_once( 'routing/routings.php' );

        //---- Route: Cloning
        require_once( 'routing/clone.php' );

        //---- Adminpage: functions
        require_once( "modals/admin.php" );

        //---- Login: Setup
        require_once( "setup.php" );

    }

    function userData() {
        include( "setup.php" );
        return array( $username, $password, $category );
    }
}


?>