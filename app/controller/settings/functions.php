<?php
/**
 * Created by Wikibyte.org
 * User: McCouman
 * Date: 20.12.15
 * Time: 16:17
 */

/**
 * @param $content
 */
function firtzConvertAmp( $content ) {
    echo preg_replace( '/&([^#])(?![a-z1-4]{1,8};)/i', '&#038;$1', $content );
}

/**
 * @param $a
 * @param $b
 *
 * @return bool
 */
function sortByPubDate( $a, $b ) {
    if ( strtotime( $a->item[ 'pubDate' ] ) == strtotime( $b->item[ 'pubDate' ] ) )
    {
        return ( $a->item[ 'slug' ] < $b->item[ 'slug' ] );
    }

    return ( strtotime( $a->item[ 'pubDate' ] ) < strtotime( $b->item[ 'pubDate' ] ) );

}

/**
 * @param       $text
 * @param array $allowed_tags
 *
 * @return mixed
 */
function strip_article( $text, $allowed_tags = array() ) {

    $rtext = preg_replace_callback( '/<\/?([^>\s]+)[^>]*>/i', function ( $matches ) use ( &$allowed_tags )
    {
        return in_array( strtolower( $matches[ 1 ] ), $allowed_tags ) ? $matches[ 0 ] : '';
    }, $text
    );

    return $rtext;
}

/**
 * @param $text
 *
 * @return mixed
 */
function markdown_text( $text ) {
    global $f3;

    $node = $f3->get( 'firtz' );

    return $node->markdown->text( $text );
}

// ----------------------------- set podcast

$firtz = new node( $f3 );
$firtz->loadAllTheExtensions( $f3 );
$f3->set( 'firtz', $firtz );

$podcasts = array();

foreach ( glob( $f3->get( 'PODCASTDIR' ) . '/*', GLOB_ONLYDIR ) as $dir )
{
    if ( substr( basename( $dir ), 0, 1 ) != "_" )
    {
        $podcasts[] = basename( $dir );
    }
}

$f3->set( 'podcasts', $podcasts );

// ----------------------------- login

function login( $array ) {
    $user_password = $array;

    return $user_password;
}

?>