<?php
/**
 * Class node
 *
 * @info Knoten
 */

class node {

    // vars
    public $data = array();
    public $extensions = array();
    public $attr = array();
    public $f3 = "";

    /**
     * node constructor.
     *
     * @param $f3
     */
    function __construct( $f3 ) {

        //vars
        $this->markdown = new Parsedown();
        $this->f3 = $f3;
        $this->BASEURL = $f3->get( 'scheme' ) . '://' . $f3->get( 'HOST' );

        /*
         * BASEURL
         */
        if ( substr( $this->BASEURL, - 1 ) != "/" )
        {
            $this->BASEURL .= '/';
        }

        /*
         * SCRIPS
         */
        if ( dirname( $_SERVER[ 'SCRIPT_NAME' ] ) != "/" )
        {
            $this->BASEURL .= trim( dirname( $_SERVER[ 'SCRIPT_NAME' ] ), "/" );
        }

        if ( substr( $this->BASEURL, - 1 ) != "/" )
        {
            $this->BASEURL .= '/';
        }

        /*
         * DOCUMENT ROOT
         */
        $this->BASEPATH = $_SERVER[ 'DOCUMENT_ROOT' ];

        if ( substr( $this->BASEPATH, - 1 ) != "/" )
        {
            $this->BASEPATH .= '/';
        }

        if ( dirname( $_SERVER[ 'SCRIPT_NAME' ] ) != "/" )
        {
            $this->BASEPATH .= dirname( $_SERVER[ 'SCRIPT_NAME' ] );
        }

        if ( substr( $this->BASEPATH, - 1 ) != "/" )
        {
            $this->BASEPATH .= '/';
        }

        // Set BASEPATH, BASEURL
        $f3->set( 'BASEPATH', $f3->fixslashes( $this->BASEPATH ) );
        $f3->set( 'BASEURL', $f3->fixslashes( $this->BASEURL ) );

        foreach ( $f3->get( 'firtzattr_default' ) as $var )
        {
            $attr[ $var ] = "";
        }

        $attr[ 'podcastalias' ] = array();

        //read firtz.conf wenn exist
        if ( file_exists( './firtz.cfg' ) )
        {
            // firtz global config file... at last :(
            foreach ( $f3->get( 'firtzattr_default' ) as $var )
            {
                $attr[ $var ] = "";
            }

            $fh = fopen( './firtz.cfg', 'r' );

            $thisattr = "";

            while ( ! feof( $fh ) )
            {
                $line = trim( fgets( $fh ) );

                if ( $line == "" || substr( $line, 0, 2 ) == "#:" )
                {
                    continue;
                }

                if ( $line == "---end---" )
                {
                    break;
                }

                /* a new attribute */
                if ( substr( $line, - 1 ) == ":" &&
                     in_array( substr( $line, 0, - 1 ), $f3->get( 'firtzattr_default' ) )
                )
                {
                    $thisattr = substr( $line, 0, - 1 );
                    $attr[ $thisattr ] = "";
                }
                elseif ( $thisattr == "podcastalias" )
                {
                    $alias = explode( " ", $line );
                    if ( sizeof( $alias ) == 3 )
                    {
                        $attr[ 'podcastalias' ][] =
                            array( 'format' => $alias[ 0 ], 'podcast' => $alias[ 1 ], 'route' => $alias[ 2 ] );
                    }
                }
                else
                {
                    /* concat a new line to existing attribute */
                    if ( $thisattr != "" )
                    {
                        $attr[ $thisattr ] .= ( $attr[ $thisattr ] != "" ) ? "\n" . $line : $line;
                    }
                }

            }

            fclose( $fh );
        }

        $this->attr = $attr;
    }

    /**
     * @param $date
     *
     * @return string
     */
    function time_difference( $date ) {

        if ( empty( $date ) )
        {
            return "No date provided";
        }

        $periods = array( "s", "m", "h", "d", " weeks", " months", " years", " dc" );
        $lengths = array( "60", "60", "24", "7", "4.35", "12", "10" );

        $now = time();
        $unix_date = strtotime( $date );

        // check validity of date
        if ( empty( $unix_date ) )
        {
            return "Bad date";
        }

        // is it future date or past date
        if ( $now > $unix_date )
        {
            $difference = $now - $unix_date;
            $tense = "";

        }
        else
        {
            $difference = $unix_date - $now;
            $tense = "from now";
        }

        for ( $j = 0; $difference >= $lengths[ $j ] && $j < count( $lengths ) - 1; $j ++ )
        {
            $difference /= $lengths[ $j ];
        }

        $difference = round( $difference );

        if ( $difference != 1 )
        {
            $periods[ $j ] .= "";
        }

        return "$difference$periods[$j] {$tense}";
    }


    /**
     * Lade alle Erweiterungen
     * @param $f3
     */
    function loadAllTheExtensions( $f3 ) {

        if ( ! file_exists( $f3->get( 'EXTDIR' ) ) )
        {
            return;
        }

        foreach ( glob( $f3->get( 'EXTDIR' ) . '/*', GLOB_ONLYDIR ) as $dir )
        {
            if ( substr( basename( $dir ), 0, 1 ) == "_" )
            {
                continue;
            }

            $extension = new extension ( $f3, $dir );

            if ( $extension === false )
            {
                die( "failed to load extension at $dir" );
            }
            else
            {
                foreach ( $this->extensions as $ext )
                {
                    if ( $ext->slug == $extension->slug )
                    {
                        die( "failed to load extension at $dir - slug $this->slug already registered!" );
                    }
                }
                $this->extensions[ $extension->slug ] = $extension;
            }
        }
    }

}

?>
