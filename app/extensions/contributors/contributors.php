<?php

/**
 * @param $item
 *
 * @return mixed
 */
function contributors_episode( $item ) {

    global $f3;

    if ( $f3->get( 'version' ) < 2 )
    {
        return $item;
    }

    if ( ! isset( $item[ 'contributors' ] ) || $item[ 'contributors' ] == "" )
    {
        return $item;
    }

    $item[ 'contributors_data' ] = array();

    foreach ( explode( ' ', $item[ 'contributors' ] ) as $con )
    {
        $condata = array();

        if ( ! file_exists( "users/" . $con . ".user" ) )
        {
            continue;
        }

        $fh = fopen( "users/" . $con . ".user", 'r' );

        $thisattr = "";

        while ( ! ( feof( $fh ) ) )
        {
            $line = trim( fgets( $fh ) );

            if ( substr( $line, 0, 2 ) == "#:" || $line == "" )
            {
                continue;
            }

            if ( substr( $line, - 1 ) == ":" )
            {
                $thisattr = substr( $line, 0, - 1 );
            }
            else
            {
                $condata[ $thisattr ] = $line;
            }
        }

        fclose( $fh );

        $item[ 'contributors_data' ][ $con ] = $condata;
    }

    return $item;
}

?>