<?php
/**
 * Adminpage
 * User: McCouman
 * Date: 25.12.15
 * Time: 15:52
 */

//global vars
global $datum;

//func. vars
$serverPath = 'http://localhost/test/server.php';
$datum = date( "d.m.Y" );

/**
 * SQLite Functions
 *
 * @return array
 */
require_once( "admin/sqlite.php" );

//-------------------------- Adminpage: UpdatePage ----------------------------

/**
 * Page: Update - view
 *
 * @param $temp_path
 */
function get_adminpageUpdate( $temp_path ) {
    // ------------------------------------------------------------------- Header
    echo Template::instance()->render( $temp_path . '/header.html' );
    echo Template::instance()->render( $temp_path . '/temps/left.html' );
    // -------------------------------------------------------------------

    get_refeshButton();

    // ------------------------------------------------------------------- Middle
    echo Template::instance()->render( $temp_path . '/temps/middle.html' );
    // -------------------------------------------------------------------

    check_UpdateData();

    // ------------------------------------------------------------------- footer
    echo Template::instance()->render( $temp_path . '/temps/right.html' );
    echo Template::instance()->render( $temp_path . '/footer.html' );
    // -------------------------------------------------------------------
}

/*
 * Function: Right sidbare
 *
 * @info ist DB vorhanden ? lese DB aus : Infomeldung wenn nicht vorhanden
 */
function check_UpdateData() {

    //DB ist vorhanden
    $existDB = isDBExists();
    if ( $existDB == true )
    {
        $db = schalterDB( true );

        readDB( $db );
    }

    //Keine DB vorhanden
    else
    {
        echo '<article class="firtz-article firtz-handy-artikel" data-permalink="{{@BASEURL}}">
                <div class="firtz-grid">
                    <div class="firtz-width-12">
                        <p>Es wurden noch keine Daten geladen!</p>
                    </div>
                </div>
          </article>';
    }
}

/*
 * Function: left-side from adminpage
 *
 * @echo design Theme Data
 */
function get_refeshButton() {
    ?>
    <div style="padding: 25px 30px;">
        <div class="firtz-grid" data-firtz-grid-margin="">
            <div class="firtz-width-1-2">
                <p>Abfrage neuer Podcasts</p>
            </div>
            <div class="firtz-width-5-10">
                <a id="admin-refresh-button"
                   href="login?intern=update&up=load-new-data"
                   class="firtz-icon-button firtz-icon-refresh"></a>
            </div>
        </div><!--grid-->
    </div><!--pedding-->

    <div style="padding: 25px 30px; border-top:1px solid #eee;">
        <div class="firtz-grid" data-firtz-grid-margin="">
            <div class="firtz-width-12">
                <a href="login?intern=update&up=create-new-data"
                   class="firtz-button firtz-width-1-1 firtz-button-success firtz-margin-small-bottom"
                   style="text-align: center;">
                    Daten Herunterladen
                </a>
            </div>
        </div><!--grid-->
    </div><!--pedding-->
    <?php
}

/*
 * Function: right-side from adminpage
 *
 * @return string Json Data
 */
function get_podcastList() {
    $loginData = new controller();
    $loginData->userData();
    list( $username, $password, $category ) = $loginData->userData();
    $jsonout = set_readNewPodcasts( set_loginPodbe( $username, $password ), $category );

    return $jsonout;
}

//------------ Adminpage: UpdatePage - action update new Podcasts -------------

/**
 * Page: Update - action
 *
 * @param $admin_redirect
 */
function get_adminpageCheckNewPodcasts( $admin_redirect ) {
    $admin_url = $admin_redirect;
    create_newPodcasts_inDB( $admin_url );
}

/*
 * Function: lade Daten aus Json und trage diese in die Datenbank
 *
 * @param $f3
 * @param $datum
 */
function create_newPodcasts_inDB( $admin_redirect ) {

    //vars
    global $datum;

    $json = get_podcastList(); //Json Daten vorhanden

    //Existiert die DB ?
    $existDB = isDBExists();
    if ( $existDB == true )
    {
        //lösche DB
        $is_delete = schalterDB( false );
        if ( $is_delete == false )
        {
            //ist gelöscht => dann: erstelle neue Datenbank
            $db = schalterDB( true );

            //schreibe tabelle in db
            createDB( $db );

            //erstelle Inhalt
            $is_write = writeDB( $json, $db, $datum );
            if ( $is_write == true )
            {
                //mache weiterleitung ohne cache
                header( "location: " . $admin_redirect . "?intern=updatepage" );
                header( "Cache-Control: post-check=0, pre-check=0", false );
                header( "Last-Modified: " . gmdate( "D, d M Y H:i:s" ) . " GMT" );
                header( "Pragma: no-cache" );
            }
        }
    }
    else
    {
        //erstelle neue Datenbank
        $db = schalterDB( true );

        //schreibe tabelle in db
        createDB( $db );

        //erstelle Inhalt
        $is_write = writeDB( $json, $db, $datum );
        if ( $is_write == true )
        {
            //mache weiterleitung ohne cache
            header( "location: " . $admin_redirect . "?intern=updatepage" );
            header( "Cache-Control: post-check=0, pre-check=0", false );
            header( "Last-Modified: " . gmdate( "D, d M Y H:i:s" ) . " GMT" );
            header( "Pragma: no-cache" );
        }
    }

}

/*
 * Function: oAuth (Login Podbe)
 *
 * @param $user
 * @param $id
 *
 * @return string
 */
function set_loginPodbe( $user, $id ) {

    global $serverPath;

    // Daten, die gesendet werden sollen
    $postdata = 'user=' . $user . '&id=' . $id;

    // send init curl data
    $ch = curl_init( $serverPath );
    curl_setopt( $ch, CURLOPT_POST, true );
    curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
    curl_setopt( $ch, CURLOPT_POSTFIELDS, $postdata );
    $data = curl_exec( $ch );
    $get_token = json_decode( $data, true );
    curl_close( $ch );

    #echo "<pre>";
    #var_dump( $get_token );
    #echo "</pre>";

    //token ist nicht leer
    if ( $get_token[ 'token' ] != null )
    {
        //speichere Token in Var
        $saveToken = $get_token[ 'token' ];

        //schreibe token in datei
        #$datei = fopen( "app/assets/db/data.db", "w" );
        #$writeToken = fwrite( $datei, $saveToken, 100 );
        #fclose( $datei );

        //-- neu! prüfe ob node.db vorhanden
        $nodeDBisOK = isNodeDBExists();
        if ( $nodeDBisOK == true )
        {
            // lösche
            #schalterNodeDB( false );

            // lese DB aus
            $db = schalterNodeDB( true );

            // erstelle tabellen in der DB
            createNodeDB( $db );

            $testFirstDatainDB = testNodeDB( $db, 1 );

            #var_dump($testFirstDatainDB);

            if ( $testFirstDatainDB == false )
            {
                //erstelle neue <null> DB Eintrag
                $db->exec( "INSERT INTO node ( 'token', 'lasttime' )
                            VALUES ( 'first', 'first number' )"
                );

                $db->exec( 'UPDATE node
                            SET token="' . $saveToken . '"
                            WHERE id=1'
                );
            }
            else
            {
                //-- neu! schreibe token in node.db
                $db->exec( 'UPDATE node
                            SET token="' . $saveToken . '"
                            WHERE id=1'
                );
            }

            //-- neu! schreibe token in node.db
            #writeTokenInNodeDB( $db, $saveToken );

            $auth = 'ok';
        }
        else
        {
            $db = schalterNodeDB( true );

            // erstelle tabellen in der DB
            createNodeDB( $db );

            //-- neu! schreibe token in node.db
            #writeTokenInNodeDB( $db, $saveToken );
            $db->exec( 'UPDATE node SET token="1234" WHERE id=1' );

            $giveTokenAsString = (string) $writeToken;
            $auth = 'Error: Datein hat falschen Token ' . $giveTokenAsString . '!';
        }

    }
    else
    {
        $auth = 'Error: Request zum Endpunkt nicht möglich!';
    }

    return $auth;
}

/**
 * Function: lese Json aus nach Login
 *
 * @param $userToken
 * @param $category
 *
 * @return mixed|string
 */
function set_readNewPodcasts( $userToken, $category ) {

    global $datum;
    global $serverPath;
    $saveToken = $userToken;

    //ist token gespeichert in datei?
    if ( $saveToken == 'ok' )
    {
        //lese Token aus Datei
        #$datei = fopen( "app/assets/db/data.db", "r" );
        #$myToken = fgets( $datei, 60 );
        #fclose( $datei );

        $nodeDBisOK = isNodeDBExists();
        if ( $nodeDBisOK == true )
        {
            // lese DB nach Token aus
            $db = schalterNodeDB( true );
            $results = $db->query( "SELECT token FROM node" );
            $myToken = $results->fetchArray()[ "token" ];
        }
        else
        {
            $myToken = 'no token';
        }

        //prüfe Datei auf konsistens des tokens & Anzahl bestehender Zeichen
        if ( $myToken != false && strlen( $myToken ) >= 40 )
        {
            //frage kategorie mit token ab
            $postdata = 'category=' . $category . '&t=' . $myToken;

            //send curl Abfrage
            $ch = curl_init( $serverPath );
            curl_setopt( $ch, CURLOPT_POST, true );
            curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
            curl_setopt( $ch, CURLOPT_POSTFIELDS, $postdata );
            $data = curl_exec( $ch );
            $goTo = json_decode( $data, true );
            curl_close( $ch );

            //------------------

            // prüfe auf podbe
            if ( $goTo[ 'podbe' ] == 'node' )
            {
                $get_podbe = true;
            }
            else
            {
                $get_podbe = false;
            }

            // prüfe auf category
            if ( $goTo[ 'category' ] == $category )
            {
                $get_category = true;
            }
            else
            {
                $get_category = false;
            }

            // prüfe auf category
            if ( $goTo[ "timestamp" ] == $datum )
            {
                $get_date = true;
            }
            else
            {
                $get_date = false;
            }

            // gebe json aus => wenn : login = ok
            if ( $get_podbe == true && $get_category = true && $get_date = true )
            {
                $goToJson = $goTo;
            }

            //------------------

        }
        else
        {
            $goToJson = 'Error: Fehler beim auslesen des Tokens!';
        }

    }
    else
    {
        $goToJson = 'Error: oAuth nicht möglich!';
    }

    return $goToJson;

}

//----------------------------- Adminpage: Home -------------------------------

function get_adminpageHome( $temp_path ) {
    // ------------------------------------------------------------------- Header
    echo Template::instance()->render( $temp_path . '/header.html' );
    echo Template::instance()->render( $temp_path . '/temps/left.html' );
    // -------------------------------------------------------------------

    echo 'Home';

    // ------------------------------------------------------------------- Middle
    echo Template::instance()->render( $temp_path . '/temps/middle.html' );
    // -------------------------------------------------------------------

    echo 'infos in Arbeit';

    // ------------------------------------------------------------------- footer
    echo Template::instance()->render( $temp_path . '/temps/right.html' );
    echo Template::instance()->render( $temp_path . '/footer.html' );
    // -------------------------------------------------------------------

}

//--------------------------- Adminpage: Settings -----------------------------

function get_adminpageSettings( $temp_path ) {
    // ------------------------------------------------------------------- Header
    echo Template::instance()->render( $temp_path . '/header.html' );
    echo Template::instance()->render( $temp_path . '/temps/left.html' );
    // -------------------------------------------------------------------

    echo 'Settings';

    // ------------------------------------------------------------------- Middle
    echo Template::instance()->render( $temp_path . '/temps/middle.html' );
    // -------------------------------------------------------------------

    echo 'Einstellungen kommen später!';

    // ------------------------------------------------------------------- footer
    echo Template::instance()->render( $temp_path . '/temps/right.html' );
    echo Template::instance()->render( $temp_path . '/footer.html' );
    // -------------------------------------------------------------------

}

//------------------------ Adminpage: Lösche Podcast --------------------------

function get_adminpageDeletePodcast( $delete_id, $admin_url ) {
    //lese DB aus
    $db = schalterDB( true );
    //lösche einen Eintrag
    deleteDB( $db, $delete_id );
    //mache weiterleitung
    header( "location: " . $admin_url . "?intern=updatepage" );
}

//----------------- Adminpage: Erstelle Podcast (<$slug>.node) ----------------

function get_adminpageCreatePodcast( $admin_url ) {
    // lösche
    #schalterNodeDB( false );

    // lese DB aus
    #$db = schalterNodeDB( true );

    // erstelle tabellen in der DB
    #createNodeDB( $db );

    //schreibe datum in tabelle
    #writeNewNode( $db );

    //überschreiben
    #writeLastDataInNodeDB( $db );

    //schreibe last podbe time
    #$db->exec('UPDATE system SET lasttime="laber" WHERE id=1');

    //schreibe token
    #$db->exec('UPDATE system SET token="1234" WHERE id=1');

    #var_dump($query);

    //mache weiterleitung
    #header( "location: " . $admin_url . "?intern=updatepage" );
}


