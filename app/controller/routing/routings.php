<?php
/**
 * Created by Wikibyte.org
 * User: McCouman
 * Date: 20.12.15
 * Time: 15:42
 */

//-------------------- feeds xml

/**
 * FEED <MP3> - ROUTE:
 */
$f3->route( 'GET|HEAD /@podcast/@audio', function ( $f3, $params )
{
    $slug = $params[ 'podcast' ];

    if ( ! in_array( $slug, $f3->get( 'podcasts' ) ) )
    {
        $f3->error( 404 );
    }

    $BASEPATH = $f3->get( 'PODCASTDIR' ) . '/' . $slug;
    $podcastCONFIG = $BASEPATH . '/directory.cfg';

    $podcast = new podcast( $f3, $slug, $podcastCONFIG );

    if ( $podcast->attr[ 'redirect' ] != "" )
    {
        header( 'HTTP/1.1 301 Moved Permanently' );
        header( 'Location: ' . $podcast->attr[ 'redirect' ] );
        die();
    }

    $f3->set( 'audio', $params[ 'audio' ] );

    $podcast->findPodcasts();
    $podcast->loadPodcasts();

    if ( $podcast->attr[ 'rfc5005' ] == "on" )
    {
        $f3->set( 'rfc5005', 'on' );
        $f3->set( 'maxpage', ceil( sizeof( $podcast->episodes ) / 10 ) );
        $podcast->episodes = array_slice( $podcast->episodes, 0, 10 );
    }

    $podcast->renderRSS2( $params[ 'audio' ] );

}, $f3->get( 'CDURATION' )
);

/**
 * FEED <STANDARD> - ROUTE:
 */
$f3->route( 'GET|HEAD /@podcast', function ( $f3, $params )
{
    $slug = $params[ 'podcast' ];
    if ( ! in_array( $slug, $f3->get( 'podcasts' ) ) )
    {
        $f3->error( 404 );
    }

    $BASEPATH = $f3->get( 'PODCASTDIR' ) . '/' . $slug;
    $podcastCONFIG = $BASEPATH . '/directory.cfg';

    $podcast = new podcast( $f3, $slug, $podcastCONFIG );

    if ( $podcast->attr[ 'redirect' ] != "" )
    {
        header( 'HTTP/1.1 301 Moved Permanently' );
        header( 'Location: ' . $podcast->attr[ 'redirect' ] );
        die();
    }

    $podcast->findPodcasts();
    $podcast->loadPodcasts();

    if ( $podcast->attr[ 'rfc5005' ] == "on" )
    {
        $f3->set( 'rfc5005', 'on' );
        $f3->set( 'maxpage', ceil( sizeof( $podcast->episodes ) / 10 ) );
        $podcast->episodes = array_slice( $podcast->episodes, 0, 10 );
    }

    $podcast->renderRSS2();
}, $f3->get( 'CDURATION' )
);

/**
 * FEED <PAGING> - ROUTE: pagination feed xml (1|2|3...)
 */
$f3->route( 'GET|HEAD /@podcast/@audio/page/@page', function ( $f3, $params )
{
    $slug = $params[ 'podcast' ];
    if ( ! in_array( $slug, $f3->get( 'podcasts' ) ) )
    {
        $f3->error( 404 );
    }

    $BASEPATH = $f3->get( 'PODCASTDIR' ) . '/' . $slug;
    $podcastCONFIG = $BASEPATH . '/directory.cfg';

    $podcast = new podcast( $f3, $slug, $podcastCONFIG );

    if ( $podcast->attr[ 'redirect' ] != "" )
    {
        header( 'HTTP/1.1 301 Moved Permanently' );
        header( 'Location: ' . $podcast->attr[ 'redirect' ] );
        die();
    }

    $f3->set( 'rfc5005', 'on' );

    $podcast->findPodcasts();
    $podcast->loadPodcasts();

    $f3->set( 'page', ltrim( $params[ 'page' ], '0' ) );
    $f3->set( 'maxpage', ceil( sizeof( $podcast->episodes ) / 10 ) );

    if ( $f3->get( 'page' ) == 'first' || $f3->get( 'page' ) == 'current' )
    {
        $f3->set( 'page', 1 );
    }
    if ( $f3->get( 'page' ) == 'last' )
    {
        $f3->set( 'page', $f3->get( 'maxpage' ) );
    }

    $podcast->episodes = array_slice( $podcast->episodes, ( $f3->get( 'page' ) - 1 ) * 10, 10 );
    $f3->set( 'audio', $params[ 'audio' ] );

    $podcast->renderRSS2( $params[ 'audio' ] );

}, $f3->get( 'CDURATION' )
);

//-------------------- page sites

/**
 * PAGE ROUTE:
 */
$f3->route( 'GET|HEAD /@podcast/page/@page', function ( $f3, $params )
{
    $slug = $params[ 'podcast' ];

    if ( ! in_array( $slug, $f3->get( 'podcasts' ) ) )
    {
        $f3->error( 404 );
    }

    $BASEPATH = $f3->get( 'PODCASTDIR' ) . '/' . $slug;
    $podcastCONFIG = $BASEPATH . '/directory.cfg';

    $podcast = new podcast( $f3, $slug, $podcastCONFIG );

    if ( $podcast->attr[ 'redirect' ] != "" )
    {
        header( 'HTTP/1.1 301 Moved Permanently' );
        header( 'Location: ' . $podcast->attr[ 'redirect' ] );
        die();
    }

    $f3->set( 'rfc5005', 'on' );

    $podcast->findPodcasts();
    $podcast->loadPodcasts();

    $f3->set( 'page', ltrim( $params[ 'page' ], '0' ) );
    $f3->set( 'maxpage', ceil( sizeof( $podcast->episodes ) / 10 ) );

    if ( $f3->get( 'page' ) == 'first' || $f3->get( 'page' ) == 'current' )
    {
        $f3->set( 'page', 1 );
    }
    if ( $f3->get( 'page' ) == 'last' )
    {
        $f3->set( 'page', $f3->get( 'maxpage' ) );
    }

    $podcast->episodes = array_slice( $podcast->episodes, ( $f3->get( 'page' ) - 1 ) * 10, 10 );

    $podcast->renderRSS2();

}, $f3->get( 'CDURATION' )
);

/**
 * Google Maps - ROUTE:
 */
$f3->route( 'GET|HEAD /@podcast/map', function ( $f3, $params )
{
    $slug = $params[ 'podcast' ];

    if ( ! in_array( $slug, $f3->get( 'podcasts' ) ) )
    {
        $f3->error( 404 );
    }

    $podcast = new podcast( $f3, $slug, $f3->get( 'PODCASTDIR' ) . '/' . $slug . '/directory.cfg' );

    $podcast->findPodcasts();
    $podcast->loadPodcasts();

    $podcast->renderMap();

}, $f3->get( 'CDURATION' )
);

//-------------------- blog sites

/**
 * INDEX BLOG ROUTE: blog index page
 */
$f3->route( 'GET|HEAD /@podcast/directory', function ( $f3, $params )
{

    $slug = $params[ 'podcast' ];

    if ( ! in_array( $slug, $f3->get( 'podcasts' ) ) )
    {
        $f3->error( 404 );
    }

    $BASEPATH = $f3->get( 'PODCASTDIR' ) . '/' . $slug;
    $podcastCONFIG = $BASEPATH . '/directory.cfg';

    $podcast = new podcast( $f3, $slug, $podcastCONFIG );
    $podcast->findPodcasts();
    $podcast->loadPodcasts();
    $f3->set( 'page', 1 );
    $f3->set( 'maxpage', ceil( sizeof( $podcast->episodes ) / $podcast->attr[ 'articles-per-page' ] ) );
    $podcast->episodes = array_slice( $podcast->episodes, 0, $podcast->attr[ 'articles-per-page' ] );

    $podcast->renderHTML();

}, $f3->get( 'CDURATION' )
);

/**
 * BLOG ROUTE:  blog single page
 */
$f3->route( 'GET|HEAD /@podcast/directory/@node', function ( $f3, $params )
{
    $slug = $params[ 'podcast' ];
    if ( ! in_array( $slug, $f3->get( 'podcasts' ) ) )
    {
        $f3->error( 404 );
    }

    $BASEPATH = $f3->get( 'PODCASTDIR' ) . '/' . $slug;
    $podcastCONFIG = $BASEPATH . '/directory.cfg';
    $f3->set( 'singlepage', true );
    $f3->set( 'node', $params[ 'node' ] );
    $f3->set( 'admin', true );
    $podcast = new podcast( $f3, $slug, $podcastCONFIG );
    $podcast->findPodcasts();
    $podcast->loadPodcasts( $params[ 'node' ] );
    $podcast->renderHTML();
}, $f3->get( 'CDURATION' )
);

//-------------------- search

/**
 * SEARCH ROUTE:  Search Page
 */
$f3->route( 'GET|HEAD /@podcast/search', function ( $f3, $params )
{
    $slug = $params[ 'podcast' ];
    if ( ! in_array( $slug, $f3->get( 'podcasts' ) ) )
    {
        $f3->error( 404 );
    }

    $f3->reroute( '/' . $slug . '/directory' );

}, $f3->get( 'CDURATION' )
);

/**
 * SEARCH TAG ROUTE:  Search Tag Page
 */
$f3->route( 'GET|HEAD /@podcast/search/@tag', function ( $f3, $params )
{
    $slug = $params[ 'podcast' ];
    if ( ! in_array( $slug, $f3->get( 'podcasts' ) ) )
    {
        $f3->error( 404 );
    }

    $BASEPATH = $f3->get( 'PODCASTDIR' ) . '/' . $slug;
    $podcastCONFIG = $BASEPATH . '/directory.cfg';
    if ( $params[ 'tag' ] == "" )
    {
        $f3->reroute( '/' . $slug . '/directory' );
    }
    $f3->set( 'search', $params[ 'tag' ] );

    $podcast = new podcast( $f3, $slug, $podcastCONFIG );
    $podcast->findPodcasts();
    $podcast->loadPodcasts();

    $podcast->renderHTML();

}, $f3->get( 'CDURATION' )
);

/**
 * SEARCH TAG ROUTE:  Search Audio Page
 */
$f3->route( 'GET|HEAD /@podcast/search/@tag/@audio', function ( $f3, $params )
{
    $slug = $params[ 'podcast' ];
    if ( ! in_array( $slug, $f3->get( 'podcasts' ) ) )
    {
        $f3->error( 404 );
    }

    $BASEPATH = $f3->get( 'PODCASTDIR' ) . '/' . $slug;
    $podcastCONFIG = $BASEPATH . '/directory.cfg';

    if ( $params[ 'tag' ] == "" )
    {
        $f3->reroute( '/' . $slug . '/directory' );
    }
    $f3->set( 'search', $params[ 'tag' ] );

    $podcast = new podcast( $f3, $slug, $podcastCONFIG );

    $f3->set( 'audio', $params[ 'audio' ] );
    $f3->set( 'admin', false );
    $podcast->findPodcasts();
    $podcast->loadPodcasts();

    if ( $podcast->attr[ 'rfc5005' ] == "on" )
    {
        $f3->set( 'rfc5005', 'on' );
        $f3->set( 'maxpage', ceil( sizeof( $podcast->episodes ) / 10 ) );
        $podcast->episodes = array_slice( $podcast->episodes, 0, 10 );
    }

    $podcast->renderRSS2( $params[ 'audio' ] );

}, $f3->get( 'CDURATION' )
);

//-------------------- specificals

/**
 * IGNORE ROUTE:  ignore
 */
$f3->route( 'GET|HEAD /@podcast/directory/@node/@ignore', function ( $f3, $params )
{
    $slug = $params[ 'podcast' ];
    if ( ! in_array( $slug, $f3->get( 'podcasts' ) ) )
    {
        $f3->error( 404 );
    }

    $BASEPATH = $f3->get( 'PODCASTDIR' ) . '/' . $slug;
    $podcastCONFIG = $BASEPATH . '/directory.cfg';

    $f3->set( 'admin', false );
    $f3->set( 'singlepage', true );
    $f3->set( 'node', $params[ 'node' ] );

    $podcast = new podcast( $f3, $slug, $podcastCONFIG );
    $podcast->findPodcasts();
    $podcast->loadPodcasts( $params[ 'node' ] );

    $podcast->renderHTML();

}, $f3->get( 'CDURATION' )
);

/**
 * BLOG PAGING ROUTE:  pagination (1|2|3...)
 */
$f3->route( 'GET|HEAD /@podcast/directory/pager/@pagenum', function ( $f3, $params )
{
    $slug = $params[ 'podcast' ];
    if ( ! in_array( $slug, $f3->get( 'podcasts' ) ) )
    {
        $f3->error( 404 );
    }
    $pagenum = ltrim( $params[ 'pagenum' ], '0' );
    if ( ! is_numeric( $pagenum ) )
    {
        $pagenum = 1;
    }

    $BASEPATH = $f3->get( 'PODCASTDIR' ) . '/' . $slug;
    $podcastCONFIG = $BASEPATH . '/directory.cfg';

    $podcast = new podcast( $f3, $slug, $podcastCONFIG );
    $podcast->findPodcasts();

    $podcast->loadPodcasts();
    $f3->set( 'page', $pagenum );
    $f3->set( 'maxpage', ceil( sizeof( $podcast->episodes ) / $podcast->attr[ 'articles-per-page' ] ) );

    $podcast->episodes = array_slice( $podcast->episodes, ( $pagenum - 1 ) * $podcast->attr[ 'articles-per-page' ],
        $podcast->attr[ 'articles-per-page' ]
    );
    $podcast->renderHTML();

}, $f3->get( 'CDURATION' )
);

/**
 * FRONT HTML ROUTE: (Installation Page)
 *
 * podcast page without any parameters simple list of available podcasts (web page)
 * if(single page) { then->redirect(web-page) }
 */
$f3->route( 'GET /', function ( $f3, $params )
{
    $podcasts = array();
    $firtz = $f3->get( 'firtz' );
    $allpodcasts = $f3->get( 'podcasts' );

    if ( sizeof( $allpodcasts ) == 1 )
    {
        $f3->reroute( '/' . $allpodcasts[ 0 ] . '/directory' );
    }

    foreach ( $allpodcasts as $slug )
    {
        $podcastPATH = $f3->get( 'PODCASTDIR' ) . '/' . $slug;
        $podcastCONFIG = $podcastPATH . '/directory.cfg';

        $podcast = new podcast( $f3, $slug, $podcastCONFIG );

        $podcasts[] = $podcast->attr;

        if ( 'http://' . $f3->get( 'HOST' ) == $podcast->attr[ 'baseurl' ] )
        {
            $f3->reroute( '/' . $slug . '/directory' );
        }

        $f3->set( 'frontlanguage', substr( $podcast->attr[ 'language' ], 0, 2 ) );
        $f3->set( 'fronttitle', $podcast->attr[ 'title' ] );
        $f3->set( 'frontauthor', $podcast->attr[ 'author' ] );
    }
    $f3->set( 'admin', false );
    $f3->set( 'frontpodcasts', $podcasts );

    echo Template::instance()->render( 'front.html' );

}, $f3->get( 'CDURATION' )
);

//-------------------- pages

/**
 * PAGE ROUTE: staticle pages - (folder/default.html)
 *
 * single page mode with custom content page put them im:
 * templates/pages/
 */
$f3->route( 'GET|HEAD /@podcast/page/@page', function ( $f3, $params )
{
    $slug = $params[ 'podcast' ];

    if ( ! in_array( $slug, $f3->get( 'podcasts' ) ) )
    {
        $f3->error( 404 );
    }

    $BASEPATH = $f3->get( 'PODCASTDIR' ) . '/' . $slug;
    $podcastCONFIG = $BASEPATH . '/directory.cfg';

    $f3->set( 'singlepage', true );
    $f3->set( 'pagename', $params[ 'page' ] );
    $f3->set( 'admin', false );

    $podcast = new podcast( $f3, $slug, $podcastCONFIG );
    $podcast->findPodcasts();
    $podcast->loadPodcasts();

    $podcast->renderHTML( false, $params[ 'page' ] );

}, $f3->get( 'CDURATION' )
);

/**
 * PAGE ROUTE: staticle pages | page - (folder/folder2/default.html)
 *
 * single page mode with custom content page put them im:
 * templates/pages/
 */
$f3->route( 'GET|HEAD /@podcast/page/@dir/@page', function ( $f3, $params )
{
    $slug = $params[ 'podcast' ];

    if ( ! in_array( $slug, $f3->get( 'podcasts' ) ) )
    {
        $f3->error( 404 );
    }

    $BASEPATH = $f3->get( 'PODCASTDIR' ) . '/' . $slug;
    $podcastCONFIG = $BASEPATH . '/directory.cfg';

    $f3->set( 'singlepage', true );
    $f3->set( 'admin', false );

    $podcast = new podcast( $f3, $slug, $podcastCONFIG );
    $podcast->findPodcasts();
    $podcast->loadPodcasts();

    $podcast->renderHTML( false, $params[ 'dir' ] . '/' . $params[ 'page' ] );

}, $f3->get( 'CDURATION' )
);

//-------------------- static pages

//Impressum
$f3->route( 'GET /@podcast/impressum', function ( $f3, $params )
{
    $slug = $params[ 'podcast' ];

    if ( ! in_array( $slug, $f3->get( 'podcasts' ) ) )
    {
        $f3->error( 404 );
    }

    $BASEPATH = $f3->get( 'PODCASTDIR' ) . '/' . $slug;
    $podcastCONFIG = $BASEPATH . '/directory.cfg';
    $podcast = new podcast( $f3, $slug, $podcastCONFIG );

    // ist template vorhanden?
    if ( file_exists( $podcast->podcastDir . "/app/themes" ) )
    {
        $ui = $podcast->podcastDir . "/app/themes/ ; " . $f3->get( 'UI' );
        $f3->set( 'UI', $ui );
        $f3->set( 'TEMPLATEPATH', $podcast->podcastDir . "/app/themes" );
    }
    $temp_path = $f3->get( "TEMPLATEPATH" ) . 'static/';

    //Setze Impressum daten
    $f3->set( 'author', $podcast->attr[ "author" ] );
    $f3->set( 'street', $podcast->attr[ "street" ] );
    $f3->set( 'plz', $podcast->attr[ "plz" ] );
    $f3->set( 'city', $podcast->attr[ "city" ] );
    $f3->set( 'country', $podcast->attr[ "country" ] );
    $f3->set( 'email', $podcast->attr[ "email" ] );

    #echo '<pre>';
    #var_dump( $straße );
    #var_dump($podcast->author);
    #echo '</pre>';

    // out template
    echo Template::instance()->render( $temp_path . 'Impressum.html' );

}, $f3->get( 'CDURATION' )
);

//Adminpage
/*
$f3->route( 'GET /@podcast/admin/@page', function ( $f3, $params )
{
    $slug = $params[ 'podcast' ];

    if ( ! in_array( $slug, $f3->get( 'podcasts' ) ) )
    {
        $f3->error( 404 );
    }

    $BASEPATH = $f3->get( 'PODCASTDIR' ) . '/' . $slug;
    $podcastCONFIG = $BASEPATH . '/directory.cfg';
    $podcast = new podcast( $f3, $slug, $podcastCONFIG );

    // ist template vorhanden?
    if ( file_exists( $podcast->podcastDir . "/app/themes" ) )
    {
        $ui = $podcast->podcastDir . "/app/themes/ ; " . $f3->get( 'UI' );
        $f3->set( 'UI', $ui );
        $f3->set( 'TEMPLATEPATH', $podcast->podcastDir . "/app/themes" );
    }
    $temp_path = $f3->get( "TEMPLATEPATH" ) . 'templates/admin';

    $adminpage = $params[ 'page' ];

    #echo '<pre>';
    #var_dump( $straße );
    #var_dump($podcast->author);
    #echo '</pre>';

    // out template
    echo Template::instance()->render( $temp_path . '/'.$adminpage.'.html' );

    /*
    $f3->set( 'singlepage', true );
    $f3->set( 'admin', true );
    $f3->set( 'pagename', $params[ 'page' ] );

    $BASEPATH = $f3->get( 'PODCASTDIR' ) . '/' . $slug;
    $podcastCONFIG = $BASEPATH . '/directory.cfg';
    $podcast = new podcast( $f3, $slug, $podcastCONFIG );
    $podcast->renderADMIN( false, $params[ 'page' ] );*

}, $f3->get( 'CDURATION' )
);
*/

//login Admin
$f3->route( 'GET|HEAD /@podcast/admin/login', function ( $f3, $params )
{
    $slug = $params[ 'podcast' ];

    if ( ! in_array( $slug, $f3->get( 'podcasts' ) ) )
    {
        $f3->error( 404 );
    }

    global $login;

    $userData = login( $login );

    $valid_users = array_keys( $userData );
    $user = @$_SERVER[ 'PHP_AUTH_USER' ];
    $pass = @$_SERVER[ 'PHP_AUTH_PW' ];

    $validated = ( in_array( $user, $valid_users ) ) && ( $pass == $login[ $user ] );

    if ( ! $validated )
    {
        header( 'WWW-Authenticate: Basic realm="Node Adminpage"' );
        header( 'HTTP/1.0 401 Unauthorized' );
        die ( "Not authorized" );
    }
    else
    {
        $f3->set( 'sysop', $user );
        $f3->set( 'adminlink', $slug . '/admin/login' );
        $temp_path = $f3->get( "TEMPLATEPATH" ) . 'templates/admin';
        $admin_url = $f3->get( 'BASEURL' ) . $f3->get( 'adminlink' );

        //------------------------------------ Adminpage: Home -----------------------------------
        if ( @$_GET[ 'intern' ] == '' )
        {
            get_adminpageHome( $temp_path );
        }

        //-------------------------------- Adminpage: Einstellungen -------------------------------
        if ( @$_GET[ 'intern' ] == 'settings' )
        {
            get_adminpageSettings( $temp_path );
        }

        //-------------------------------- Adminpage: Aktualisieren HOME---------------------------
        if ( @$_GET[ 'intern' ] == 'updatepage' )
        {
            $f3->clear('CACHE');
            get_adminpageUpdate( $temp_path );
        }
        ###--- lade neue podcasts
        elseif ( @$_GET[ 'intern' ] == 'update' && @$_GET[ 'up' ] == 'load-new-data' )
        {
            $f3->clear('CACHE');
            get_adminpageCheckNewPodcasts( $admin_url );
        }
        ###--- löschen id
        elseif ( @$_GET[ 'intern' ] == 'update' && @$_GET[ 'delete' ] != '' )
        {
            $f3->clear('CACHE');
            $delete_id = @$_GET[ 'delete' ];
            get_adminpageDeletePodcast( $delete_id, $admin_url );
        }



    }


}, $f3->get( 'CDURATION' )
);

//-------------------- extensions

foreach ( $firtz->attr[ 'podcastalias' ] as $alias )
{
    $f3->route( 'GET|HEAD ' . $alias[ 'route' ], function ( $f3, $params ) use ( $alias )
    {
        header( 'HTTP/1.1 301 Moved Permanently' );
        header( 'Location: /' . $alias[ 'podcast' ] . '/' . $alias[ 'format' ] );
        die();
    }
    );

}

foreach ( $firtz->extensions as $slug => $extension )
{
    if ( $extension->type != 'output' )
    {
        continue;
    }

    $slug = $extension->slug;
    $extension->init();

    $f3->route( "GET|HEAD /@podcast/$slug/*", function ( $f3, $params ) use ( $slug )
    {
        $firtz = $f3->get( 'firtz' );
        $extension = $firtz->extensions[ $slug ];

        $arguments = array();
        $arguments_ext = $extension->arguments;
        $arguments_get = explode( "/", $params[ 2 ] );

        foreach ( $arguments_get as $key => $val )
        {
            if ( isset( $arguments_ext[ $key ] ) )
            {
                $argname = $arguments_ext[ $key ];
                $arguments[ $argname ] = $val;
                $f3->set( $argname, $val );
            }
            else
            {
                $f3->set( $argname, '' );
            }

        }

        $extension->arguments = $arguments;

        $podcastslug = $params[ 'podcast' ];
        if ( ! in_array( $params[ 'podcast' ], $f3->get( 'podcasts' ) ) )
        {
            $f3->error( 404 );
        }

        $BASEPATH = $f3->get( 'PODCASTDIR' ) . '/' . $params[ 'podcast' ];
        $podcastCONFIG = $BASEPATH . '/directory.cfg';

        $podcast = new podcast( $f3, $podcastslug, $podcastCONFIG );
        $podcast->findPodcasts();
        $podcast->loadPodcasts();

        $podcast->runExt( $f3, $extension );

    }, $f3->get( 'CDURATION' )
    );

}

foreach ( $firtz->extensions as $slug => $extension )
{
    if ( $extension->type != 'output' )
    {
        continue;
    }

    $slug = $extension->slug;

    $f3->route( "GET|HEAD /@podcast/$slug", function ( $f3, $params ) use ( $slug )
    {
        $firtz = $f3->get( 'firtz' );

        $extension = $firtz->extensions[ $slug ];

        $arguments = array();
        $arguments_ext = $extension->arguments;

        foreach ( $arguments_ext as $argname )
        {
            $arguments[ $argname ] = "";
            $f3->set( $argname, "" );
        }

        $extension->arguments = $arguments;

        $podcastslug = $params[ 'podcast' ];
        if ( ! in_array( $params[ 'podcast' ], $f3->get( 'podcasts' ) ) )
        {
            $f3->error( 404 );
        }

        $BASEPATH = $f3->get( 'PODCASTDIR' ) . '/' . $params[ 'podcast' ];
        $podcastCONFIG = $BASEPATH . '/directory.cfg';

        $podcast = new podcast( $f3, $podcastslug, $podcastCONFIG );
        $podcast->findPodcasts();
        $podcast->loadPodcasts();

        $podcast->runExt( $f3, $extension );

    }, $f3->get( 'CDURATION' )
    );

}

//-------------------- #_old

/*
         * API ADN ROUTE: send a adn thread
         *
        $f3->route( 'GET /@podcast/adnthreadx/@postid', function ( $f3, $params )
        {
            $slug = $params[ 'podcast' ];

            if ( ! in_array( $slug, $f3->get( 'podcasts' ) ) )
            {
                $f3->error( 404 );
            }

            $BASEPATH = $f3->get( 'PODCASTDIR' ) . '/' . $slug;
            $podcastCONFIG = $BASEPATH . '/directory.cfg';
            $podcast = new podcast( $f3, $slug, $podcastCONFIG );

            if ( file_exists( $podcast->podcastDir . "/templates" ) )
            {
                $ui = $podcast->podcastDir . "/templates/ ; " . $f3->get( 'UI' );
                $f3->set( 'UI', $ui );
                $f3->set( 'templatepath', $podcast->podcastDir . "/templates" );
            }

            $f3->set( 'podcastattr', $podcast->attr );

            $stream = "https://alpha-api.app.net/stream/0/posts/";

            $curl = curl_init();

            curl_setopt( $curl, CURLOPT_URL, $stream . $params[ 'postid' ] . '/replies'
            );
            curl_setopt( $curl, CURLOPT_TIMEOUT, 15 );
            curl_setopt( $curl, CURLOPT_FOLLOWLOCATION, true );
            curl_setopt( $curl, CURLOPT_RETURNTRANSFER, true );
            curl_setopt( $curl, CURLOPT_HEADER, false );
            curl_setopt( $curl, CURLOPT_HTTPHEADER, array( 'Authorization: Bearer ' . $podcast->attr[ 'adntoken' ] )
            );
            curl_setopt( $curl, CURLOPT_SSL_VERIFYPEER, false );

            $return = curl_exec( $curl );

            curl_close( $curl );
            $x = json_decode( $return, true );

            foreach ( $x[ 'data' ] as $key => $post )
            {
                $x[ 'data' ][ $key ][ 'html' ] =
                    preg_replace( '/([^a-zA-Z0-9])\@([a-zA-Z0-9_]+)/',
                        '\1<a href="http://alpha.app.net/\2" rel="nofollow" target="_blank" title="directory \2\'s ADN Profile">@\2</a>\3',
                        $x[ 'data' ][ $key ][ 'html' ]
                    );
                $x[ 'data' ][ $key ][ 'html' ] =
                    preg_replace( '/(^|\s)#(\w+)/',
                        '\1#<a href="https://alpha.app.net/hashtags/\2" rel="nofollow" target="_blank" title="Posts tagged with \2">\2</a>',
                        $x[ 'data' ][ $key ][ 'html' ]
                    );

            }

            $f3->set( 'adnposts', array_reverse( $x[ 'data' ] ) );

            echo Template::instance()->render( 'adnthread.html' );

        }, $f3->get( 'CDURATION' )
        );*/

/*
 * API ROW ROUTE: get raws
 *
$f3->route( 'GET|HEAD /@podcast/raw/@node', function ( $f3, $params )
{
    $slug = $params[ 'podcast' ];

    if ( ! in_array( $slug, $f3->get( 'podcasts' ) ) )
    {
        $f3->error( 404 );
    }

    $BASEPATH = $f3->get( 'PODCASTDIR' ) . '/' . $slug;
    $podcastCONFIG = $BASEPATH . '/directory.cfg';

    $f3->set( 'node', $params[ 'node' ] );

    $podcast = new podcast( $f3, $slug, $podcastCONFIG );
    $podcast->findEpisodes();
    $podcast->loadEpisodes( $params[ 'node' ] );

    $podcast->renderRaw( $params[ 'node' ] );

}, $f3->get( 'CDURATION' )
);
*/

?>