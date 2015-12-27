<?php
/**
 * Easy Search Redirect GeHackE
 */

if ( isset( $_GET[ "s" ] ) && isset( $_GET[ "r" ] ) )
{
    $tag = @$_GET[ "s" ]; #laber%20rababer
    $url = @$_GET[ "r" ]; #<https://domain.tld>/<feedname>/show/search/
    header( 'location: ' . $url . '/search/' . $tag . '' );
}
?>