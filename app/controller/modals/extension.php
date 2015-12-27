<?php


/**
 * Class extension
 *
 * @infos weitere slugs und templates
 */
class extension {

    // vars
    public $data = array();
    public $slug = "";
    public $arguments = array();
    public $templates = array();
    public $route = "";
    public $dir = "";
    public $script = "";
    public $type = "";
    public $prio = 99;
    public $f3 = "";

    /**
     * extension constructor.
     *
     * @param $f3
     * @param $EXTDIR
     */
    function __construct( $f3, $EXTDIR ) {

        if ( ! file_exists( $EXTDIR . "/ext.cfg" ) )
        {
            return false;
        }

        // vars
        $this->dir = $EXTDIR;
        $this->f3 = $f3;

        $this->template[ 'file' ] = "";
        $this->template[ 'type' ] = "";

        $route = "";
        $thisattr = "";

        $fh = fopen( $EXTDIR . "/ext.cfg", 'r' );

        // Extension Path from config.ini
        #$ext_path = $f3->get( 'EXTDIR' );

        // read ext.cfg
        while ( ! ( feof( $fh ) ) )
        {
            $line = trim( fgets( $fh ) );

            if ( substr( $line, 0, 2 ) == "#:" || $line == "" )
            {
                continue;
            }

            if ( substr( $line, - 1 ) == ":" && ( in_array( substr( $line, 0, - 1 ), $f3->get( 'extattr' ) ) ) )
            {
                $thisattr = substr( $line, 0, - 1 );
            }
            else
            {
                if ( $thisattr == "arguments" )
                {
                    foreach ( explode( " ", $line ) as $arg )
                    {
                        $this->arguments[] = $arg;
                        $route .= "@" . $arg . "/";
                    }
                }

                if ( $thisattr == "template" )
                {
                    if ( sizeof( explode( " ", $line ) ) == 2 )
                    {
                        list( $this->template[ 'file' ], $this->template[ 'type' ] ) = explode( " ", $line );
                    }
                    else
                    {
                        $this->template[ 'file' ] = chop( $line );
                        $this->template[ 'type' ] = "text/html";
                    }
                }

                if ( $thisattr == "script" )
                {
                    foreach ( explode( " ", $line ) as $script )
                    {
                        if ( file_exists( $EXTDIR . '/' . $script ) )
                        {
                            include_once( $EXTDIR . '/' . $script );
                        }
                    }
                }

                if ( $thisattr == 'settings' )
                {
                    /* extension variables go to @ext['extslug']*/
                    if ( ! isset( $thisextvars ) )
                    {
                        $thisextvars = array();
                    }

                    $var = explode( ' ', $line )[ 0 ];
                    $value = substr( $line, strpos( $line, ' ' ) + 1 );
                    $thisextvars[ $var ] = $value;
                }

                if ( $thisattr == 'episode-settings' )
                {
                    /* these are settings, this extension adds to episodes */
                    if ( ! isset( $episodevars ) )
                    {
                        $episodevars = array();
                    }

                    $episodevars[] = $line;
                }

                if ( $thisattr == "slug" )
                {
                    $this->slug = $line;
                }

                if ( $thisattr == "type" )
                {
                    $this->type = $line;
                }
            }

        }

        fclose( $fh );

        if ( isset( $thisextvars ) )
        {
            $extvars = $f3->get( 'extvars' );
            $extvars[ $this->slug ] = $thisextvars;
            $f3->set( 'extvars', $extvars );
        }

        if ( isset( $episodevars ) )
        {
            $itemattr = $f3->get( 'itemattr' );
            foreach ( $episodevars as $evar )
            {
                $itemattr[] = $evar;
            }
            $f3->set( 'itemattr', $itemattr );
        }

        //templates
        $ui = $f3->get( 'UI' ) . ",app/extensions/" . $this->slug . "/";
        $f3->set( 'UI', $ui );

        $this->route = $this->slug . "/" . $route;

        //translation
        $dict = $f3->get( 'LOCALES' ) . ",app/extensions/" . $this->slug . "/language/";
        $f3->set( 'LOCALES', $dict );

    }

    /**
     * init
     */
    function init() {
        $run_func = $this->slug . "_init";
        if ( function_exists( $run_func ) )
        {
            $run_func();
        }
    }

    /**
     * rund
     */
    function run() {
        $run_func = $this->slug . "_run";
        if ( function_exists( $run_func ) )
        {
            $run_func();
        }
    }

}


?>