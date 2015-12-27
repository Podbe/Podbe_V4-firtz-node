<?php


/**
 * Class podcastpage
 */
class podcastpage {

    public $item = array();
    public $f3 = "";
    public $markdown = "";

    /**
     * podcastpage constructor.
     *
     * @param            $f3
     * @param            $ITEMFILE
     * @param            $podcastattrs
     * @param            $slug
     * @param bool|false $auphonic
     * @param array      $item
     */
    public function __construct( $f3, $ITEMFILE, $podcastattrs, $slug, $auphonic = false, $item = array() ) {

        if ( ! file_exists( $ITEMFILE ) )
        {
            $this->item = array();

            return;
        }

        $this->markdown = new Parsedown();
        $this->f3 = $f3;
        $this->destroy = false;

        $reparse = false;

        if ( sizeof( $item ) == 0 )
        {
            /* new item, set attributes */
        }
        else
        {
            /* reparsing an old one to overwrite data */
            $reparse = true;
        }

        if ( $auphonic === false )
        {
            /* parse an .node */
            $item = $this->parseConfig( $f3, $ITEMFILE, $podcastattrs, $item );
        }
        else
        {
            /* parse the auphonic json file */
            $item = $this->parseAuphonic( $f3, $ITEMFILE, $podcastattrs );
            if ( $item === false )
            {
                $this->item = array();

                return;
            }
        }

        if ( $reparse === true )
        {
            /* just reparsing. skip the sanitation part */
            #$this->item = $item;
            #return;
        }

        /* data sanitation */
        if ( $item[ 'date' ] != "" )
        {
            $pubDate = strtotime( $item[ 'date' ] );
            if ( $pubDate === false )
            {
                $pubDate = filectime( $ITEMFILE );
            }
        }
        else
        {
            $pubDate = filectime( $ITEMFILE );
        }

        if ( $pubDate > time() )
        {
            $this->item = array();
            $this->destroy = true;

            return;
        }

        $item[ 'pubDate' ] = date( 'D, d M Y H:i:s O', $pubDate );

        $item[ 'pagelink' ] = $f3->get( 'BASEURL' ) . $podcastattrs[ 'slug' ] . "/directory/" . $slug;

        $item[ 'slug' ] = $slug;

        if ( $item[ 'guid' ] == "" )
        {
            $item[ 'guid' ] = $podcastattrs[ 'slug' ] . "-" . $item[ 'slug' ];
        }

        if ( $item[ 'description' ] == "" )
        {
            $item[ 'description' ] = $item[ 'article' ];
        }
        $item[ 'description' ] = substr( strip_tags( $item[ 'description' ] ), 0, 255 );

        $item[ 'summary' ] = substr( strip_tags( $this->markdown->text( $item[ 'article' ] ) ), 0, 4000 );

        if ( $item[ 'image' ] == "" )
        {
            $item[ 'image' ] = $podcastattrs[ 'image' ];
        }

        $item[ 'flattrdescription' ] = rawurlencode( $item[ 'description' ] );
        $item[ 'flattrkeywords' ] = rawurlencode( $item[ 'keywords' ] );
        $item[ 'flattrlink' ] = rawurlencode( $item[ 'pagelink' ] );
        $item[ 'flattrtitle' ] = rawurlencode( $item[ 'title' ] );

        /* who says, chapters are in order? sort them! */
        if ( $item[ 'chapters' ] )
        {
            usort( $item[ 'chapters' ], function ( $a, $b )
            {
                return ( $a[ 'start' ] > $b[ 'start' ] );
            }
            );
        }

        if ( strpos( $item[ 'duration' ], '.' ) !== false )
        {
            $item[ 'duration' ] = substr( $item[ 'duration' ], 0, strpos( $item[ 'duration' ], '.' ) );
        }

        $newkeywords = array();

        foreach ( explode( ",", $item[ 'keywords' ] ) as $key )
        {
            $newkeywords[] = trim( $key );
        }
        $item[ 'keywords' ] = substr( implode( ',', $newkeywords ), 0, 255 );

        $item[ 'append' ] = '';

        $item[ 'prepend' ] = '';

        $this->item = $item;
    }

    /**
     * PARSER:  CONFIG
     *
     * @param       $f3
     * @param       $filename
     * @param       $podcastattrs
     * @param array $item
     *
     * @return array
     */
    public function parseConfig( $f3, $filename, $podcastattrs, $item = array() ) {

        /* parse an .node file */
        /* if item is given, it's a reparsing for overwriting data from an auphonic-episode */
        $mime = $f3->get( 'mimetypes' );

        if ( sizeof( $item ) == 0 )
        {
            foreach ( $f3->get( 'itemattr' ) as $var )
            {
                $item[ $var ] = "";
            }
        }
        $thisattr = "";

        $item[ 'audiofiles' ] = array();

        $slug = basename( $filename, ".node" );
        $fh = fopen( $filename, 'r' );

        while ( ! feof( $fh ) )
        {
            $uline = fgets( $fh );
            $line = trim( $uline );

            /* continue if comment or empty line. except for article attribute */
            if ( ( $line == "" && $thisattr != "article" ) || substr( $line, 0, 2 ) == "#:" )
            {
                continue;
            }

            if ( $line == "---end---" )
            {
                break;
            }

            if ( substr( $line, - 1 ) == ":" && ( in_array( substr( $line, 0, - 1 ), $f3->get( 'itemattr' )
                                                  ) ||
                                                  in_array( substr( $line, 0, - 1 ), $podcastattrs[ 'audioformats' ]
                                                  ) )
            )
            {
                /* new attribute starts */
                $thisattr = substr( $line, 0, - 1 );
                $item[ $thisattr ] = "";
            }
            elseif ( $thisattr == "chapters" )
            {
                /**
                 * Ein Kapitel Zeile
                 * Kein Link oder ein Bild
                 * Muss mit separater | oder aufgrund von Titel-Attribut, das Leerzeichen enthalten könnten
                 */
                preg_match( '#((\d+:)?(\d\d?):(\d\d?)(?:\.(\d+))?) ([^<>\r\n]{3,}) ?(<([^<>\r\n]*)>\s*(<([^<>\r\n]*)>\s*)?)?\r?#',
                    $line, $chapreg
                );

                $chap = array( 'start' => '', 'title' => '', 'image' => '', 'href' => '' );

                if ( isset( $chapreg[ 1 ] ) && isset( $chapreg[ 6 ] ) )
                {
                    $chap[ 'start' ] = $chapreg[ 1 ];
                    $chap[ 'title' ] = trim( $chapreg[ 6 ] );

                    if ( isset( $chapreg[ 8 ] ) )
                    {
                        $chap[ 'href' ] = $chapreg[ 8 ];
                        if ( isset( $chapreg[ 10 ] ) )
                        {
                            $chap[ 'image' ] = $chapreg[ 10 ];
                        }
                    }

                    $item[ 'chapters' ][] = $chap;
                }
            }
            elseif ( in_array( $thisattr, $podcastattrs[ 'audioformats' ] ) )
            {
                /* configured audio formats */
                /* only audioformats allowed, that are configured in podcast.cfg */
                $audio = explode( " ", $line );

                if ( ! array_key_exists( $thisattr, $mime ) )
                {
                    /* mimetype not found in presets */
                    if ( sizeof( $audio ) == 3 )
                    {
                        /* maybe it's in the .epi? */
                        $mimetype = $audio[ 2 ];
                    }
                    else
                    {
                        /* fallback. hmpf */
                        $mimetype = "audio/mpeg";
                    }
                }
                else
                {
                    /* everything went better than expected */
                    $mimetype = $mime[ $thisattr ];
                }

                if ( sizeof( $audio ) > 1 )
                {
                    /* that's great: length of file is given */
                    $item[ $thisattr ] = array( 'link' => $audio[ 0 ], 'length' => $audio[ 1 ], 'type' => $mimetype );
                }
                else
                {
                    /* boooh! get your metadata right! */
                    $item[ $thisattr ] = array( 'link' => $audio[ 0 ], 'length' => 0, 'type' => $mimetype );
                }

                $item[ 'audiofiles' ][ $thisattr ] = $item[ $thisattr ];
            }
            else
            {
                /* this is an attribute which may have linebreaks. append line to current attribute */
                if ( $thisattr != "" && $thisattr != "article" )
                {
                    $item[ $thisattr ] .= ( $item[ $thisattr ] != "" ) ? "\n" . $line : $line;
                }
                if ( $thisattr == "article" )
                {
                    $item[ $thisattr ] .= ( $item[ $thisattr ] != "" ) ? "\n" . $uline : $uline;
                }
            }

        }

        if ( $podcastattrs[ 'mediabaseurl' ] != "" )
        {
            foreach ( $podcastattrs[ 'audioformats' ] as $format )
            {
                /**
                 * media-base-path
                 */
                if ( $podcastattrs[ 'mediabasepath' ] != "" )
                {
                    $localfile = $podcastattrs[ 'mediabasepath' ] . "/" . $slug . "." . $format;
                    if ( ! file_exists( $localfile ) )
                    {
                        continue;
                    }
                }
                else
                {
                    $localfile = "";
                }

                /**
                 * formate
                 */
                if ( array_key_exists( $format, $mime ) )
                {
                    $mimetype = $mime[ $format ];
                }
                else
                {
                    $mimetype = "audio/mpeg";
                }

                /**
                 * audiofiles
                 */
                if ( isset( $item[ 'audiofiles' ][ $format ] ) )
                {
                    $audiofilename = basename( $item[ 'audiofiles' ][ $format ][ 'link' ], "." . $format );
                }
                else
                {
                    $audiofilename = $slug;
                }

                /**
                 * audio metadata
                 */
                $item[ $format ] = array(
                    'link'   => $podcastattrs[ 'mediabaseurl' ] . $audiofilename . "." . $format,
                    'length' => $localfile != "" ? filesize( $localfile ) : 0,
                    'type'   => $mimetype
                );

                $item[ 'audiofiles' ][ $format ] = $item[ $format ];

            }
        }

        fclose( $fh );

        return $item;
    }

    /**
     * PARSER: AUPHONIC
     *
     * @param $f3
     * @param $filename
     * @param $podcastattrs
     *
     * @return mixed
     */
    public function parseAuphonic( $f3, $filename, $podcastattrs ) {

        /* parse a json production description file */
        $mime = $f3->get( 'mimetypes' );

        foreach ( $f3->get( 'itemattr' ) as $var )
        {
            $item[ $var ] = "";
        }

        $thisattr = "";

        $prod = json_decode( str_replace( "\\r", "\\n", file_get_contents( $filename ) ) );

        if ( $prod === false )
        {
            return false;
        }

        $item[ 'title' ] = $prod->metadata->title;
        $item[ 'description' ] = strip_tags( $prod->metadata->subtitle );
        $item[ 'article' ] = $prod->metadata->summary;

        if ( isset( $prod->metadata->location ) )
        {
            if ( isset( $prod->metadata->location->latitude ) && isset( $prod->metadata->location->longitude ) )
            {
                $item[ 'location' ][ 'latitude' ] = $prod->metadata->location->latitude;
                $item[ 'location' ][ 'longitude' ] = $prod->metadata->location->longitude;
            }
        }

        $item[ 'duration' ] = $prod->length_timestring;
        $item[ 'date' ] = date( 'r', strtotime( $prod->change_time ) );

        foreach ( $prod->chapters as $chapter )
        {
            $chap = array(
                'start' => $chapter->start,
                'title' => $chapter->title,
                'image' => '',
                'href'  => ''
            );

            if ( isset( $chapter->url ) )
            {
                $chap[ 'href' ] = $chapter->url;
            }

            // images in chapters are located on auphonics server. no chance to get them :(
            // if (isset($chapter->image)) $chap['image'] = $chapter->image;
            $item[ 'chapters' ][] = $chap;

        }
        if ( isset( $prod->multi_input_files ) && $item[ 'chapters' ] != "" )
        {
            foreach ( $prod->multi_input_files as $mif )
            {
                if ( $mif->type == 'intro' )
                {
                    foreach ( $item[ 'chapters' ] as $key => $chap )
                    {
                        $item[ 'chapters' ][ $key ][ 'start' ] =
                            strftime( "%H:%M:%S", strtotime( $chap[ 'start' ] ) + $mif->input_length );
                    }
                }
            }

        }

        $services = array();

        foreach ( $prod->outgoing_services as $service )
        {
            // only services with a base_url work...
            if ( isset( $service->base_url ) && $service->base_url != "" )
            {
                $services[ $service->uuid ] = $service->base_url;
            }
            if ( $service->type == "amazons3" )
            {
                $services[ $service->uuid ] = 'http://' . $service->bucket . '.s3.amazonaws.com/';
            }
        }

        $item[ 'audiofiles' ] = array();

        foreach ( $prod->output_files as $output )
        {
            if ( sizeof( $output->outgoing_services ) == 0 )
            {
                continue;
            }

            $service = "";

            // Check if this services exists
            foreach ( $output->outgoing_services as $oservice )
            {
                if ( array_key_exists( $oservice, $services ) )
                {
                    $service = $oservice;
                    break;
                }
            }

            if ( $service == "" )
            {
                continue;
            }

            if ( $output->format == "image" )
            {
                $item[ 'image' ] = $services[ $service ] . $output->filename;
            }

            if ( in_array( $output->ending, $podcastattrs[ 'audioformats' ] ) )
            {
                $mimetype =
                    ( array_key_exists( $output->ending, $mime ) ? $mime[ $output->ending ] : "application/octet" );

                $item[ $output->ending ] = array(
                    'link'   => $services[ $service ] . $output->filename,
                    'length' => $output->size,
                    'type'   => $mimetype
                );

                $item[ 'audiofiles' ][ $output->ending ] = $item[ $output->ending ];
            }
        }

        # replace attributes by tags, starting with _
        # eg: _date:2013-12-17 10:00:00
        foreach ( $prod->metadata->tags as $key => $tag )
        {
            $tag = trim( $tag );
            if ( substr( $tag, 0, 1 ) == "_" && strpos( $tag, ':' ) !== false )
            {
                $tagname = substr( $tag, 1, strpos( $tag, ':' ) - 1 );
                $tagval = trim( substr( $tag, strpos( $tag, ':' ) + 1 ) );

                if ( in_array( $tagname, $f3->get( 'itemattr' ) ) )
                {
                    $item[ $tagname ] = $tagval;
                    unset( $prod->metadata->tags[ $key ] );
                }
            }
        }

        $item[ 'keywords' ] = implode( ",", $prod->metadata->tags );

        return $item;
    }

}


?>
