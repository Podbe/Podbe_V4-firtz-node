<?php
/**
 * Created by Wikibyte.org
 * User: McCouman
 * Date: 20.12.15
 * Time: 16:15
 */

ini_set( 'auto_detect_line_endings', true );


//--- set base settings


$f3->set( 'BITMASK', ENT_NOQUOTES | ENT_XML1 );

$f3->set( 'generator', 'firtz podcast publisher v' . $f3->get( 'version' ) . "." . $f3->get( 'revision' )
);

$f3->set( 'BASEURL', "http://" . str_replace( "/", "", $f3->get( 'HOST' ) ) . dirname( $_SERVER[ 'SCRIPT_NAME' ] )
);

$f3->set( 'BASEPATH', $_SERVER[ 'DOCUMENT_ROOT' ] );

$f3->set( 'og', array() );

$f3->set( 'extvars', array() );


//--- set http/s protocol settings


if ( isset( $_SERVER[ 'HTTPS' ] ) && $_SERVER[ 'HTTPS' ] != "" )
{
    $f3->set( 'scheme', 'https' );
}
else
{
    $f3->set( 'scheme', 'http' );
}


?>