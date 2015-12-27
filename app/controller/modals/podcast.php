<?php


/**
 * Class podcast
 *
 * @info alle Daten die benötigt werden, um eine Podcastseite zu erstellen
 *
 */
class podcast {

    //vars
    public $attr = array();

    public $episode_slugs = array();
    public $auphonic_slugs = array();
    public $real_slugs = array();
    public $episodes = array();

    public $podcastDir = "";
    public $f3 = "";
    public $markdown = "";

    /**
     * PODCAST CONSTRUCTOR
     *
     * @param $f3
     * @param $slug
     * @param $configfile
     */
    public function __construct( $f3, $slug, $configfile ) {

        $this->markdown = new Parsedown();

        if ( ! file_exists( $configfile ) )
        {
            echo "Config for $slug not found (missing $configfile)";
            die();
        }

        $this->f3 = $f3;
        $this->podcastDir = dirname( $configfile );
        $this->htmltemplate = 'site.html';
        $attr = array();

        /* populate attributes */
        foreach ( $f3->get( 'podcastattr_default' ) as $var )
        {
            $attr[ $var ] = "";
        }

        $fh = fopen( $configfile, 'r' );

        $thisattr = "";
        $categories = array();

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
            if ( substr( $line, - 1 ) == ":" && in_array( substr( $line, 0, - 1 ), $f3->get( 'podcastattr_default' ) ) )
            {
                $thisattr = substr( $line, 0, - 1 );
                $attr[ $thisattr ] = "";
            }
            elseif ( $thisattr != "" )
            {
                switch ( $thisattr )
                {
                    case 'category':

                        /* append a category line */
                        $thiscat = explode( "->", $line );
                        if ( sizeof( $thiscat ) > 1 )
                        {
                            $categories[] = array( 'a' => trim( $thiscat[ 0 ] ), 'b' => trim( $thiscat[ 1 ] ) );
                        }
                        else
                        {
                            $categories[] = array( 'a' => trim( $thiscat[ 0 ] ), 'b' => '' );
                        }

                        break;

                    case 'bitlove':

                        /* bitlove information */
                        $bitlove = explode( " ", $line );
                        if ( sizeof( $bitlove ) == 3 )
                        {
                            $attr[ 'bitlove' ][ $bitlove[ 0 ] ] = array(
                                'format'  => $bitlove[ 0 ],
                                'users'   => $bitlove[ 1 ],
                                'podcast' => $bitlove[ 2 ]
                            );
                        }

                        break;

                    case 'templatevars':

                        /* template variables go to @templatevars */
                        if ( ! isset( $templatevars ) )
                        {
                            $templatevars = array();
                        }
                        $varline = explode( ' ', $line );
                        $var = $varline[ 0 ];
                        $value = substr( $line, strpos( $line, ' ' ) + 1 );
                        $templatevars[ $var ] = $value;

                        break;

                    default:
                        $attr[ $thisattr ] .= ( $attr[ $thisattr ] != "" ) ? "\n" . $line : $line;
                        break;
                }
            }

        }

        fclose( $fh );

        /* sanitize data */

        /* cloning? */
        if ( $f3->get( 'clonemode' ) === true )
        {
            $f3->set( 'BASEURL', $attr[ 'cloneurl' ] );
        }

        /* theme */
        if ( ! isset( $templatevars ) )
        {
            $templatevars = array();
        }
        $f3->set( 'templatevars', $templatevars );
        if ( $attr[ 'template' ] != "" )
        {
            $f3->set( 'TEMPLATEPATH', 'app/themes/' . $attr[ 'template' ] );
        }

        $attr[ 'categories' ] = $categories;

        if ( $attr[ 'baseurl' ] != "" && substr( $attr[ 'baseurl' ], - 1 ) == "/" )
        {
            $attr[ 'baseurl' ] = substr( $attr[ 'baseurl' ], 0, - 1 );
        }

        $attr[ 'slug' ] = $slug;
        $attr[ 'link' ] = $f3->get( 'BASEURL' ) . $attr[ 'slug' ] . '/directory';
        $attr[ 'self' ] = $f3->get( 'REALM' );
        $attr[ 'selfrel' ] =
            ( substr( $attr[ 'self' ], - 1 ) == "/" ) ? substr( $attr[ 'self' ], 0, - 1 ) : $attr[ 'self' ];

        $attr[ 'summary' ] = substr( strip_tags( $attr[ 'summary' ] ), 0, 4000 );
        $attr[ 'keywords' ] = substr( $attr[ 'keywords' ], 0, 255 );

        if ( $attr[ 'description' ] == "" )
        {
            $attr[ 'description' ] = $attr[ 'summary' ];
        }

        $attr[ 'description' ] = substr( strip_tags( $attr[ 'description' ] ), 0, 255 );

        if ( $attr[ 'cloneurl' ] != '' && substr( $attr[ 'cloneurl' ], - 1 ) != '/' )
        {
            $attr[ 'cloneurl' ] .= '/';
        }

        if ( $attr[ 'flattrid' ] != "" )
        {
            $attr[ 'flattrlanguage' ] =
                ( $attr[ 'language' ] != "" ) ? str_replace( "-", "_", $attr[ 'language' ] ) : "";
            $attr[ 'flattrdescription' ] = rawurlencode( $attr[ 'description' ] );
            $attr[ 'flattrkeywords' ] = rawurlencode( $attr[ 'keywords' ] );
            $attr[ 'flattrlink' ] = rawurlencode( $f3->get( 'BASEURL' ) . $attr[ 'slug' ] . '/directory' );
            $attr[ 'flattrtitle' ] = rawurlencode( $attr[ 'title' ] );
        }

        $attr[ 'audioformats' ] = explode( " ", $attr[ 'formats' ] );
        $attr[ 'maintype' ] = $attr[ 'audioformats' ][ 0 ];
        $attr[ 'alternate' ] = $attr[ 'audioformats' ];

        /* fishy - might take a look into that */
        if ( $f3->get( 'clonemode' ) === false )
        {
            $attr[ 'baserel' ] =
                $f3->fixslashes( $f3->get( 'scheme' ) . '://' . $f3->get( 'HOST' ) . '/' . $slug . '/' );
            $attr[ 'instacast' ] = $f3->fixslashes( 'podcast://' . $f3->get( 'HOST' ) . '/' . $slug );
        }
        else
        {
            $attr[ 'baserel' ] = $f3->fixslashes( $attr[ 'cloneurl' ] . $slug . '/' );
            $attr[ 'instacast' ] =
                str_replace( $f3->get( 'scheme' ) . '://', 'podcast://', $f3->fixslashes( $attr[ 'cloneurl' ] . $slug )
                );
        }

        /*
        if ( file_exists( dirname( $configfile ) . "/" . $slug . ".css" ) )
        {
            /*	yet undocumented ;)
                if a $slug.css file exists in podcast directory, this one replaces the
                standard bootstrap.css
            *
            $attr[ 'sitecss' ] = $f3->get( 'BASEURL' ) . "podcasts/" . $slug . "/" . $slug . ".css";
        }
        else
        {
            $attr[ 'sitecss' ] = $f3->get( 'BASEURL' ) . 'css/bootstrap.min.css';
        }
*/

        $f3->set( 'sitetemplate', 'site.html' );

        if ( $attr[ 'rfc5005' ] == "" )
        {
            $attr[ 'rfc5005' ] = 'off';
        }

        if ( $attr[ 'mediabaseurl' ] != '' && substr( $attr[ 'mediabaseurl' ], - 1 ) != '/' )
        {
            $attr[ 'mediabaseurl' ] .= '/';
        }

        if ( $attr[ 'articles-per-page' ] == "" )
        {
            $attr[ 'articles-per-page' ] = 3;
        }

        if ( $attr[ 'auphonic-mode' ] == '' )
        {
            $attr[ 'auphonic-mode' ] = 'off';
        }

        //Themes
        $ui = $f3->get( 'UI' );

        if ( $attr[ 'template' ] != '' )
        {
            $f3->set( 'UI', $ui . ',app/themes/' . $attr[ 'template' ] . '/,app/themes/templates/' );
            $this->loadTemplateConfig( $attr[ 'template' ] );
        }
        else
        {
            $f3->set( 'UI', $ui . ',app/themes/templates/' );
            $this->loadTemplateConfig( 'templates' );
        }

        //Translation
        if ( $attr[ 'language' ] != "" )
        {
            $f3->set( 'LANGUAGE', $attr[ 'language' ] );
        }

        $this->attr = $attr;

        $f3->set( 'curpodcast', $this );
    }

    /**
     * THEME: Config File
     *
     * @param $template
     */
    public function loadTemplateConfig( $template ) {

        //const f3
        $f3 = $this->f3;

        // finde template.cfg
        if ( ! file_exists( './app/themes/' . $template . '/template.cfg' ) )
        {
            return;
        }

        // go to templet vars
        $templatevars = $f3->get( 'templatevars' );

        // read template.cfg
        $fh = fopen( './app/themes/' . $template . '/template.cfg', 'r' );

        // pars #: lines
        while ( ! feof( $fh ) )
        {
            $line = trim( fgets( $fh ) );

            if ( $line == "" || substr( $line, 0, 2 ) == "#:" )
            {
                continue;
            }

            $varline = explode( ' ', $line );
            $var = $varline[ 0 ];
            $value = substr( $line, strpos( $line, ' ' ) + 1 );

            if ( ! isset( $templatevars[ $var ] ) )
            {
                $templatevars[ $var ] = $value;
            }

        }

        // set template in vars
        $f3->set( 'templatevars', $templatevars );
    }

    /**
     * PRELOADING: SLUG
     */
    public function preloadPodcasts() {

        $f3 = $this->f3;
        $maxPubDate = "";
        $realSlugs = array();

        /*
         * Just a simple PreLoad to determine, which slugs are really
         * needed for pageing mode to reduce load
         */
        switch ( $this->attr[ 'auphonic-mode' ] )
        {
            /*
             * standard mode. just load all .node files
             */
            case "off":

            case "":

                foreach ( $this->episode_slugs as $slug )
                {
                    $realSlugs[] = $slug;
                }
                break;

            /*
             * full mode: load auphonic and node files.
             * if there's an node file whith the same name as an auphonic file,
             * the data in node file overwrites attributes from auphonic.
             */
            case "full":

                foreach ( $this->auphonic_slugs as $slug )
                {
                    $realSlugs[] = $slug;
                }
                foreach ( $this->episode_slugs as $slug )
                {
                    if ( ! in_array( $slug, $this->auphonic_slugs ) )
                    {
                        $realSlugs[] = $slug;
                    }
                }
                break;

            /*
             * exclusive mode: only auphonic files are read, node ignored
             */
            case "exclusive":

                foreach ( $this->auphonic_slugs as $slug )
                {
                    $realSlugs[] = $slug;
                }
                break;

            /*
             * episode mode: like full, but only auphonic episodes are loaded,
             * that also exist as episode files. node data overwrites auphonic attributes
             * this is the standard mode, if auphonic is in remote mode
             */
            case "episodes":

                foreach ( $this->episode_slugs as $slug )
                {
                    if ( in_array( $slug, $this->auphonic_slugs ) )
                    {
                        $realSlugs[] = $slug;
                    }
                }
                break;
        }

        $this->real_slugs = $realSlugs;

    }

    /**
     * LOAD PODCAST:
     *
     * @param array $slug
     */
    public function loadPodcasts( $slug = array() ) {

        $f3 = $this->f3;
        $maxPubDate = "";
        if ( sizeof( $slug ) != 0 )
        {
            /*
             * reduce slugs array to this one episode
             * happens for /$podcast/directory/$episodeslug/
             * and /$podcast/directory/pager/$page
             */
            if ( ! is_array( $slug ) )
            {
                $this->episode_slugs = array_intersect( array( 0 => $slug ), $this->episode_slugs );
                $this->auphonic_slugs = array_intersect( array( 0 => $slug ), $this->auphonic_slugs );
            }
            else
            {
                $this->episode_slugs = array_intersect( $slug, $this->episode_slugs );
                $this->auphonic_slugs = array_intersect( $slug, $this->auphonic_slugs );
            }

        }

        /* handle loading of episodes depending on auphonic mode */
        switch ( $this->attr[ 'auphonic-mode' ] )
        {
            /* standard mode. just load all .node files */
            case "off":
            case "":
                foreach ( $this->episode_slugs as $slug )
                {
                    $episode = new podcastpage( $f3, $this->podcastDir . "/" . $slug . ".node", $this->attr, $slug );
                    if ( $episode->item )
                    {
                        $this->episodes[ $episode->item[ 'slug' ] ] = $episode;
                    }
                }
                break;

            /* full mode: load auphonic and node files.
                if there's an node file whith the same name as an auphonic file,
                the data in node file overwrites attributes from auphonic.
            */
            case "full":

                foreach ( $this->auphonic_slugs as $slug )
                {
                    $episode =
                        new podcastpage( $f3, $this->attr[ 'auphonic-path' ] . "/" . $slug . ".json", $this->attr,
                            $slug, true
                        );
                    if ( $episode->item )
                    {
                        $this->episodes[ $episode->item[ 'slug' ] ] = $episode;
                    }
                }

                foreach ( $this->episode_slugs as $slug )
                {

                    if ( ! in_array( $slug, $this->auphonic_slugs ) )
                    {

                        /* exclusive .node */
                        $episode =
                            new podcastpage( $f3, $this->podcastDir . "/" . $slug . ".node", $this->attr, $slug, false
                            );
                        if ( $episode->item )
                        {
                            $this->episodes[ $episode->item[ 'slug' ] ] = $episode;
                        }

                    }
                    else
                    {
                        /* auphonic with same slug exists. take values from node to overwrite args in auphonic episode */
                        $old_episode = $this->episodes[ $slug ];

                        $episode =
                            new podcastpage( $f3, $this->podcastDir . "/" . $slug . ".node", $this->attr, $slug, false,
                                $old_episode->item
                            );

                        /* parse additional .node */
                        /* maybe the node decided to invalidate the episode (future pubdate...)? then $episode->destroy is true */
                        if ( $episode->destroy === true )
                        {
                            unset( $this->episodes[ $slug ] );
                            continue;
                        }

                        if ( $episode->item )
                        {
                            foreach ( $episode->item as $key => $val )
                            {
                                if ( $val != "" && sizeof( $val ) != 0 )
                                {
                                    $old_episode->item[ $key ] = $val;
                                }
                            }
                        }

                    }
                }

                break;

            /* exclusive mode: only auphonic files are read, node ignored */
            case "exclusive":

                foreach ( $this->auphonic_slugs as $slug )
                {
                    $episode =
                        new podcastpage( $f3, $this->attr[ 'auphonic-path' ] . "/" . $slug . ".json", $this->attr,
                            $slug, true
                        );
                    if ( $episode->item )
                    {
                        $this->episodes[ $episode->item[ 'slug' ] ] = $episode;
                    }
                }
                break;

            /*	episode mode: like full, but only auphonic episodes are loaded,
                that also exist as episode files. node data overwrites auphonic attributes
                this is the standard mode, if auphonic is in remote mode
            */
            case "episode":

                foreach ( $this->episode_slugs as $slug )
                {
                    if ( in_array( $slug, $this->auphonic_slugs ) )
                    {
                        $episode =
                            new podcastpage( $f3, $this->attr[ 'auphonic-path' ] . "/" . $slug . ".json", $this->attr,
                                $slug, true
                            );

                        if ( $episode->item )
                        {
                            $this->episodes[ $episode->item[ 'slug' ] ] = $episode;
                        }

                        /*
                         * take values from node to overwrite args in auphonic episode
                         */
                        $epi_episode =
                            new podcastpage( $f3, $this->podcastDir . "/" . $slug . ".node", $this->attr, $slug, false,
                                $episode->item
                            );

                        /*
                         * parse additional .node
                         * maybe the node decided to invalidate the episode (future pubdate...)?
                         * then $episode->destroy is true
                         */
                        if ( $epi_episode->destroy === true )
                        {
                            unset( $this->episodes[ $slug ] );
                            continue;
                        }

                        if ( $epi_episode->item )
                        {
                            foreach ( $epi_episode->item as $key => $val )
                            {
                                if ( $val != "" && sizeof( $val ) != 0 )
                                {
                                    $episode->item[ $key ] = $val;
                                }
                            }
                        }


                    }
                }

                break;
        }

        /* Sort episodes by pubDate */
        uasort( $this->episodes, 'sortByPubDate' );

        /* find the latest episode to fill in data in rss and atom podcasts (<updated>) */
        $lastupdate = 0;
        $firtz = $f3->get( 'firtz' );

        /* Gehe zur Suche */
        foreach ( $this->episodes as $key => $episode )
        {
            if ( $f3->get( 'search' ) != "" )
            {
                if ( ! in_array( trim( $f3->get( 'search' ) ), explode( ",", $episode->item[ 'keywords' ] ) ) &&
                     strpos( strtolower( $episode->item[ 'title' ] ), strtolower( $f3->get( 'search' ) ) ) === false
                )
                {
                    unset( $this->episodes[ $key ] );
                    continue;
                }
            }

            # find last update time. cache stuff and podcast info
            $update = strtotime( $episode->item[ 'pubDate' ] );
            if ( $update > $lastupdate )
            {
                $lastupdate = $update;
            }

            # no podcast image? take the first found episode image...
            if ( $this->attr[ 'image' ] == "" && $episode->item[ 'image' ] != "" )
            {
                $this->attr[ 'image' ] = $episode->item[ 'image' ];
            }

            $episode->item[ 'article' ] = $this->markdown->text( $episode->item[ 'article' ] );
            $episode->item[ 'description' ] = strip_article( $this->markdown->text( $episode->item[ 'description' ] ) );

            foreach ( $firtz->extensions as $extslug => $ext )
            {
                #if ($ext->type!="content") continue;
                $efunc = $extslug . "_episode";

                if ( function_exists( $efunc ) )
                {
                    $item = $efunc( $episode->item );
                    if ( $item !== false )
                    {
                        $episode->item = $item;
                    }
                }
            }
        }

        $this->attr[ 'lastupdate' ] = date( 'c', $lastupdate );
        $realSlugs = array();

        foreach ( $this->episodes as $slug => $episode )
        {
            $realSlugs[] = $slug;
        }

        $this->real_slugs = $realSlugs;
    }

    /**
     * SEARCH: Finde den Podcast
     */
    public function findPodcasts() {

        /*	find all auphonic and node files
            collect slugs and save them.
            no loading, just finding to reduce load in case, not all episodes have to be displayed (web page single mode/pageing mode)
        */
        if ( $this->attr[ 'auphonic-path' ] != "" && file_exists( $this->attr[ 'auphonic-path' ] ) &&
             $this->attr[ 'auphonic-mode' ] != "" && $this->attr[ 'auphonic-mode' ] != "off"
        )
        {

            /* get local auphonic files */
            $auphonic_episodes = glob( $this->attr[ 'auphonic-path' ] . "/" . $this->attr[ 'auphonic-glob' ] );

            foreach ( $auphonic_episodes as $json )
            {

                $slug = basename( $json, '.json' );
                $this->auphonic_slugs[] = $slug;
            }
        }

        /* find local node files if not in auphonic exclusive mode */
        if ( $this->attr[ 'auphonic-mode' ] != 'exclusive' )
        {
            $itemfiles = glob( $this->podcastDir . '/*.node' );
            $this->episode_slugs = array();

            foreach ( $itemfiles as $EPISODEFILE )
            {
                $slug = basename( $EPISODEFILE, '.node' );
                if ( $this->attr[ 'auphonic-mode' ] == "episode" )
                {
                    /* auphonic episode mode. if there's no identically names auphonic episode, keep hands off */
                    if ( in_array( $slug, $this->auphonic_slugs ) )
                    {
                        $this->episode_slugs[] = $slug;
                    }
                }
                else
                {
                    /* auphonic off, full */
                    $this->episode_slugs[] = $slug;
                }

            }

        }

    }

    /**
     * EXTENSION: RUN
     *
     * @param $f3
     * @param $extension
     */
    public function runExt( $f3, $extension ) {

        /* execute template plugin */
        $f3 = $this->f3;

        $this->attr[ 'self' ] =
            $f3->get( 'BASEURL' ) . $this->attr[ 'slug' ] . "/" . $extension->slug . "/" . $f3->get( 'audio' );

        $audioformat = ( $f3->get( 'audio' ) ? : $this->attr[ 'audioformats' ][ 0 ] );

        $this->attr[ 'audioformat' ] = $audioformat;

        $f3->set( 'podcastattr', $this->attr );

        /*
         * collect episodes
         */
        $items = array();

        foreach ( $this->episodes as $episode )
        {
            $item = $episode->item;
            if ( isset( $item[ $audioformat ] ) )
            {
                $item[ 'enclosure' ] = $item[ $audioformat ];
            }
            $items[] = $item;
        }

        $f3->set( 'items', $items );

        $this->setOpenGraph();

        /*
         * render plugins template
         */
        $extension->run();

        echo Template::instance()->render( $extension->template[ 'file' ], $extension->template[ 'type' ] );
    }

    /**
     * Meta Data for Podcasts
     */
    public function setOpenGraph() {

        $f3 = $this->f3;
        $og = array();
        $og[ 'url' ] = $f3->get( 'BASEURL' ) . $this->attr[ 'slug' ] . '/directory';

        if ( sizeof( $this->episodes ) == 1 )
        {
            $episode = reset( $this->episodes );
            $og[ 'title' ] = $episode->item[ 'title' ];
            $og[ 'url' ] .= '/' . $episode->item[ 'slug' ];
        }
        else
        {
            $og[ 'title' ] = $this->attr[ 'title' ];
        }

        $og[ 'audio' ] = array();

        foreach ( $this->episodes as $episode )
        {
            if ( sizeof( $episode->item[ 'audiofiles' ] ) == 0 )
            {
                continue;
            }
            $format = $this->attr[ 'audioformats' ][ 0 ];

            if ( ! isset( $episode->item[ 'audiofiles' ][ $format ][ 'type' ] ) )
            {
                continue;
            }

            $og[ 'audio' ][ 'typename' ] = substr( $episode->item[ 'audiofiles' ][ $format ][ 'type' ], 0, 5 );
            $og[ 'audio' ][ 'type' ] = $episode->item[ 'audiofiles' ][ $format ][ 'type' ];
            $og[ 'audio' ][ 'url' ] = $episode->item[ 'audiofiles' ][ $format ][ 'link' ];

        }

        $f3->set( 'og', $og );

    }


    //---------------------------- Rendering XML, HTML, EMBED

    /**
     * RENDER: MAPS
     *
     * @param bool|false $ret
     * @param bool|false $kml
     *
     * @return string
     */
    public function renderMap( $ret = false, $kml = false ) {

        /*
         * render rss2 template
         */
        $f3 = $this->f3;
        $f3->set( 'podcastattr', $this->attr );

        /*
         * collect episodes
         */
        $items = array();

        foreach ( $this->episodes as $episode )
        {
            $item = $episode->item;
            $items[] = $item;
        }

        $f3->set( 'items', $items );

        /*
         * render or return template
         * return rendered data will be used in clone mode, which will be used for static site clones
         */
        if ( $kml == false )
        {
            if ( $ret === false )
            {
                echo Template::instance()->render( 'map.html' );
            }
            else
            {
                return Template::instance()->render( 'map.html' );
            }
        }
        else
        {
            if ( $ret === false )
            {
                echo Template::instance()->render( 'map.xml', 'application/xml' );
            }
            else
            {
                return Template::instance()->render( 'map.xml' );
            }
        }
    }

    /**
     * RENDER RSS (XML)
     *
     * @param string     $audioformat
     * @param bool|false $ret
     *
     * @return string
     */
    public function renderRSS2( $audioformat = '', $ret = false ) {

        /*
         * render rss2 template
         */
        $f3 = $this->f3;

        $this->attr[ 'self' ] = $f3->get( 'BASEURL' ) . $this->attr[ 'slug' ] . "/" . $audioformat;

        if ( $audioformat == '' )
        {
            $audioformat = $this->attr[ 'audioformats' ][ 0 ];
        }
        $this->attr[ 'audioformat' ] = $audioformat;

        $f3->set( 'podcastattr', $this->attr );

        /*
         * collect episodes
         */
        $items = array();

        foreach ( $this->episodes as $episode )
        {
            $item = $episode->item;
            if ( isset( $item[ $audioformat ] ) )
            {
                $item[ 'enclosure' ] = $item[ $audioformat ];
            }

            if ( $item[ 'chapters' ] != "" )
            {
                foreach ( $item[ 'chapters' ] as $key => $chapter )
                {
                    if ( $chapter[ 'title' ] != "" )
                    {
                        $item[ 'chapters' ][ $key ][ 'title' ] = $item[ 'chapters' ][ $key ][ 'title' ] =
                            str_replace( array( "&", "\"" ), array( "&amp;amp;", "&amp;quot;" ), $chapter[ 'title' ] );
                    }
                }
            }

            $items[] = $item;
        }

        $f3->set( 'items', $items );

        /*
         * render or return template
         * return rendered data will be used in clone mode, which will be used for static site clones
        */
        if ( $ret === false )
        {
            echo Template::instance()->render( 'rss2.xml', 'application/xml' );
        }
        else
        {
            return Template::instance()->render( 'rss2.xml' );
        }

    }

    /**
     * RENDER: HTML
     *
     * @param bool|false $ret
     * @param string     $pagename
     *
     * @return string
     */
    public function renderHTML( $ret = false, $pagename = "" ) {

        /* render standard html template */
        $f3 = $this->f3;
        $f3->set( 'podcastattr', $this->attr );

        /* single page from pages template? */
        if ( $pagename != "" )
        {
            $f3->set( 'showpage', 'pages/' . $pagename . '.html' );
        }

        /* collect episodes */
        $items = array();
        if ( $f3->exists( 'node' ) && $f3->get( 'node' ) != "" )
        {
            $items = array( $this->episodes[ $f3->get( 'node' ) ]->item );
        }
        else
        {
            foreach ( $this->episodes as $episode )
            {
                $items[] = $episode->item;
            }
        }

        $f3->set( 'items', $items );

        $this->setOpenGraph();

        /*
         * render or return template
         * return rendered data will be used in clone mode,
         * which will be used for static site clones
         */
        if ( $ret === false )
        {
            echo Template::instance()->render( $this->htmltemplate );
        }
        else
        {
            return Template::instance()->render( $this->htmltemplate );
        }
    }





    /**
     * RENDER HTML (BARE)
     *
     * @param bool|false $ret
     * @param string     $pagename
     *
     * @return string
     */
    public function renderHTMLbare( $ret = false, $pagename = "" ) {

        /* render standard html template */
        $f3 = $this->f3;
        $f3->set( 'podcastattr', $this->attr );

        /* collect episodes */
        $items = array();

        if ( $f3->exists( 'node' ) && $f3->get( 'node' ) != "" )
        {
            $items = array( $this->episodes[ $f3->get( 'node' ) ]->item );
        }
        else
        {
            foreach ( $this->episodes as $episode )
            {
                $items[] = $episode->item;
            }
        }

        $f3->set( 'items', $items );

        $this->setOpenGraph();

        /*
         * render or return template
         * return rendered data will be used in clone mode,
         * which will be used for static site clones
        */
        if ( $ret === false )
        {
            echo Template::instance()->render( "embed.html" );
        }
        else
        {
            return Template::instance()->render( "embed.html" );
        }
    }

    /**
     * RENDER RAW
     *
     * @param bool|false $ret
     * @param string     $pagename
     */
    public function renderRaw( $ret = false, $pagename = "" ) {

        /* render standard html template */
        $f3 = $this->f3;
        $f3->set( 'podcastattr', $this->attr );

        /* single page from pages template? */
        if ( $pagename != "" )
        {
            $f3->set( 'showpage', 'pages/' . $pagename . '.html' );
        }

        /* collect episodes */
        $items = array();
        if ( $f3->exists( 'node' ) && $f3->get( 'node' ) != "" )
        {
            $items = array( $this->episodes[ $f3->get( 'node' ) ]->item );
        }
        else
        {
            foreach ( $this->episodes as $episode )
            {
                $items[] = $episode->item;
            }
        }

        $f3->set( 'items', $items );

        $this->setOpenGraph();

        /*
         * render or return template
         * return rendered data will be used in clone mode,
         * which will be used for static site clones
        */
        echo Template::instance()->render( "raw.html" );
    }

}


?>