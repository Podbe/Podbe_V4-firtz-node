<?php
/**
 * Created by Wikibyte.org
 * User: McCouman
 * Date: 20.12.15
 * Time: 16:00
 */

//MODE: clone xml
if ( php_sapi_name() == "cli" )
{
    function dir_recurse_copy( $src, $dst ) {

        $dir = opendir( $src );

        @mkdir( $dst );

        while ( false !== ( $file = readdir( $dir ) ) )
        {
            if ( ( $file != '.' ) && ( $file != '..' ) )
            {
                if ( is_dir( $src . '/' . $file ) )
                {
                    dir_recurse_copy( $src . '/' . $file, $dst . '/' . $file );
                }
                else
                {
                    copy( $src . '/' . $file, $dst . '/' . $file );
                }
            }
        }

        closedir( $dir );
    }

    $f3->set( 'clonemode', true );
    $f3->set( 'extxml', '.xml' );
    $f3->set( 'exthtml', '.html' );

    # CLI mode... create static pages
    foreach ( $f3->get( 'podcasts' ) as $slug )
    {
        $podcastPATH = $f3->get( 'PODCASTDIR' ) . '/' . $slug;
        $podcastCONFIG = $podcastPATH . '/directory.cfg';

        $podcast = new podcast( $f3, $slug, $podcastCONFIG );

        if ( $podcast->attr[ 'cloneurl' ] == '' || $podcast->attr[ 'clonepath' ] == '' )
        {
            continue;
        }
        $DEST = $podcast->attr[ 'clonepath' ];

        if ( ! file_exists( $DEST ) )
        {
            @mkdir( $DEST );
            if ( ! file_exists( $DEST ) )
            {
                echo "could not create destination path $DEST";
                exit;
            }
        }


        if ( ! is_writable( $DEST ) )
        {
            echo "no permissions to write to $DEST!";
            exit;
        }

        if ( strpos( $podcast->attr[ 'sitecss' ], '/podcasts/' ) !== false )
        {
            $origcss = $podcastPATH . '/' . basename( $podcast->attr[ 'sitecss' ] );
            $clonecss = $f3->fixslashes( $DEST . '/css/' . basename( $podcast->attr[ 'sitecss' ] ) );
            copy( $origcss, $clonecss );
            $podcast->attr[ 'sitecss' ] =
                $podcast->attr[ 'cloneurl' ] . '/css/' . basename( $podcast->attr[ 'sitecss' ] );
        }

        dir_recurse_copy( 'js', $DEST . '/js' );
        dir_recurse_copy( 'css', $DEST . '/css' );
        dir_recurse_copy( 'pwp', $DEST . '/pwp' );

        $DEST = $f3->fixslashes( $DEST . '/' . $slug );
        @mkdir( $DEST );
        @mkdir( $f3->fixslashes( $DEST . '/directory/' ) );

        $f3->set( 'BASEURL', $podcast->attr[ 'cloneurl' ] );

        $podcast->findPodcasts();
        $podcast->loadPodcasts();

        foreach ( $podcast->attr[ 'audioformats' ] as $audio )
        {
            $xml = $podcast->renderRSS2( $audio, true );
            file_put_contents( $DEST . '/' . $audio . '.xml', $xml );
        }

        $f3->set( 'node', '' );

        $html = $podcast->renderHTML( true );

        file_put_contents( $DEST . '/directory/index.html', $html );

        foreach ( $podcast->real_slugs as $episode_slug )
        {
            $f3->set( 'node', $episode_slug );
            $html = $podcast->renderHTML( true );
            @mkdir( $DEST . '/directory/' . $episode_slug );
            file_put_contents( $DEST . '/directory/' . $episode_slug . '/index.html', $html );
        }

        foreach ( glob( 'app/theme/pages/*.html' ) as $page )
        {
            $html = $podcast->renderHTML( true, basename( $page, '.html' ) );
            @mkdir( $f3->fixslashes( $DEST . '/page' ) );
            file_put_contents( $DEST . '/page/' . basename( $page ), $html );
        }


        $frontpodcasts[] = $podcast->attr;
        $f3->set( 'frontlanguage', substr( $podcast->attr[ 'language' ], 0, 2 ) );
        $f3->set( 'fronttitle', $podcast->attr[ 'title' ] );
        $f3->set( 'frontauthor', $podcast->attr[ 'author' ] );

    }

    $f3->set( 'frontpodcasts', $frontpodcasts );
    $front = Template::instance()->render( 'front.html' );

    file_put_contents( $podcast->attr[ 'clonepath' ] . 'index.html', $front );

    exit;
}

?>