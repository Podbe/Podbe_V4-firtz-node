<?php
/**
 * SQLite funktions
 *
 * @info Grundfunktionen zum erstellen der (new podcasts) DB.
 * @by   Michael McCouman Jr.
 */

/**
 * Erstellen einer File-DB. / Löschen der File-DB.
 *
 * @param $schalter
 *
 * @return bool|SQLite3|string
 */
function schalterDB( $schalter ) {

    //SQLite anlegen
    if ( $schalter == true )
    {
        $db = new SQLite3( 'app/assets/db/new.db' );
    }
    // lösche Datenbank wieder
    elseif ( $schalter == false )
    {
        unlink( 'app/assets/db/new.db' );
        $db = false;
    }
    else
    {
        $db = '';
    }

    return $db;
}

/**
 * Prüfe new.db Datei ob vorhanden / erstellt
 *
 * @return bool
 */
function isDBExists() {
    $filename = 'app/assets/db/new.db';

    if ( file_exists( $filename ) )
    {
        $filestatus = true;
    }
    else
    {
        $filestatus = false;
    }

    return $filestatus;
}

/**
 * Erstelle neue DB (new.db) mit Daten der neuen Podcasts
 *
 * @param $db
 */
function createDB( $db ) {
    $db->exec( "CREATE TABLE IF NOT EXISTS
              new (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                podcast TEXT NOT NULL DEFAULT '0',
                slug TEXT NOT NULL DEFAULT '0',
                url TEXT NOT NULL DEFAULT '0',
                nodetime TEXT NOT NULL DEFAULT '0',
                podbetime TEXT NOT NULL DEFAULT '0',
                ok TEXT NOT NULL DEFAULT '0'
              )"
    );
}

/**
 * Schreibe neue DB-Einträge.
 *
 * @param $jsonData
 * @param $db
 * @param $datum
 *
 * @return bool (true|false)
 */
function writeDB( $jsonData, $db, $datum ) {

    $checkCount = count( $jsonData[ "podcasts" ] ) - 1;

    #echo '<pre>';
    #var_dump( $jsonData );
    #echo '</pre>';

    //Prüfe ob DB vorhanden und einen Eintrag hat.
    $newEntrysInDB = testDB( $db, $checkCount );

    #var_dump( $newEntrysInDB );
    if ( $newEntrysInDB == false )
    {
        $i = 0;
        // Lese Json aus und trage sie in DB ein.
        while ( $i <= count( $jsonData[ "podcasts" ] ) - 1 )
        {
            $db->exec( "INSERT INTO new
                ('podcast','slug','url','nodetime', 'podbetime', 'ok')
                VALUES
                ( '" . $jsonData[ "podcasts" ][ $i ][ "name" ] . "',
                  '" . $jsonData[ "podcasts" ][ $i ][ "slug" ] . "',
                  '" . $jsonData[ "podcasts" ][ $i ][ "url" ] . "',
                  '" . $datum . "',
                  '" . $jsonData[ "podcasts" ][ $i ][ "time" ] . "',
                  'i.A.'
                )"
            );
            $i ++;
        }

        $out = true;

    }
    else
    {
        $out = false;
    }

    return $out;
}

/**
 * Lese new.db Daten aus (Template: right)
 *
 * @param $db
 */
function readDB( $db ) {
    #$results = $db->query("SELECT * FROM new WHERE id='1'");
    if ( $db != '' )
    {
        $results = $db->query( "SELECT * FROM new" );
        while ( $row = $results->fetchArray() )
        {
            /*
            $results = $db->query( "SELECT * FROM new" );
            while ( $row = $results->fetchArray() )
            {
                echo $row[ 'id' ] . '<br>';
                echo $row[ 'podcast' ] . '<br>';
                echo $row[ 'slug' ] . '<br>';
                echo $row[ 'nodetime' ] . '<br>';
                echo $row[ 'podbetime' ] . '<br>';
                echo $row[ 'ok' ] . '<br>';
            }
            */
            ?>

            <article class="firtz-article firtz-handy-artikel" id="article-admin" data-permalink="{{@BASEURL}}">
                <div class="firtz-grid">

                    <div class="firtz-width-2-3">

                        <a href="#"
                           title="Erfolgreich geladen"
                           id="node-<?php echo $row[ 'id' ]; ?>"
                           class="admin-success-button">
                            <i class="firtz-icon-check-circle"></i>
                        </a>

                        <?php
                        $testb = false;
                        if ( $testb == true )
                        {
                            ?>
                            <a href="#"
                               title="Podcastdaten Laden"
                               id="node-<?php echo $row[ 'id' ]; ?>"
                               class="admin-nosuccess-button">
                                <i class="firtz-icon-check"></i>
                            </a>
                            <?php
                        }
                        ?>

                        <div class="admin-podcastname" style="">
                            <b style="font-size: 20px;" id="title"><?php echo $row[ 'podcast' ]; ?></b>
                        </div>
                    </div>

                    <div class="firtz-width-1-3">
                        <!-- delete -->
                        <a href="login?intern=update&delete=<?php echo $row[ 'id' ]; ?>"
                           title="Löschen"
                           class="admin-delete-button firtz-icon-times"></a>

                        <!-- view -->
                        <a style="float:right;"
                           href="<?php echo 'http://podbe.de/view/' . $row[ 'slug' ] . '/'; ?>"
                           class="admin-view-button firtz-icon-eye"></a>
                    </div>

                </div>
            </article>

            <?php
        }
    }
}

/**
 * Löche new.db Daten aus Tabelle über id
 *
 * @param $db
 * @param $id
 */
function deleteDB( $db, $id ) {
    // Löscht einen Eintrag über die ID
    $db->query( "DELETE FROM new WHERE id='" . $id . "'" );
}

/**
 * Prüfe ob Eintrag vorhanden
 *
 * @info count( $JsonData['podcast'] )
 *
 * @param $db
 * @param $id
 *
 * @return bool
 */
function testDB( $db, $id ) {
    $results = $db->query( "SELECT COUNT(id) FROM new WHERE id='" . $id . "'" );
    if ( $results->fetchArray()[ 0 ] == 1 )
    {
        $out = true;
    }
    else
    {
        $out = false;
    }

    return $out;
}

// ------------------------ node system ---------------------

/**
 * Lese & erstelle Datenbank: node.db
 *
 * @param $schalter
 *
 * @return bool|SQLite3|string
 */
function schalterNodeDB( $schalter ) {

    //SQLite anlegen
    if ( $schalter == true )
    {
        $db = new SQLite3( 'app/assets/db/node.db' );
    }
    // lösche Datenbank wieder
    elseif ( $schalter == false )
    {
        unlink( 'app/assets/db/node.db' );
        $db = false;
    }
    else
    {
        $db = '';
    }

    return $db;
}

/**
 * Erstelle neue DB (node.db) mit Daten des Node-System
 *
 * @param $db
 */
function createNodeDB( $db ) {
    $db->exec( "CREATE TABLE IF NOT EXISTS
              node (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                token TEXT NOT NULL DEFAULT '0',
                lasttime TEXT NOT NULL DEFAULT '0'
              )"
    );
}

//prüfe node db
function isNodeDBExists() {
    $filename = 'app/assets/db/node.db';

    if ( file_exists( $filename ) )
    {
        $filestatus = true;
    }
    else
    {
        $filestatus = false;
    }

    return $filestatus;
}

function writeNewNode( $db ) {
    //schreibe datum in tabelle
    $db->exec( "INSERT INTO system ( 'token', 'lasttime' )
                VALUES
                (
                '1',
                '12.12.2015'
                )"
    );
}

function writeLastDataInNodeDB( $db ) {
    $db->exec( "UPDATE
                node ('lasttime')
                SET (id='1')
                WHERE
                lasttime='00.00.0000'"
    );
}

function writeTokenInNodeDB( $db, $writeToken ) {
    $db->exec( "UPDATE
                node ('token')
                SET (id='1')
                WHERE lasttime=" . $writeToken . " "
    );
}


function testNodeDB( $db, $id ) {
    $results = $db->query( "SELECT COUNT(id) FROM node WHERE id='" . $id . "'" );
    if ( $results->fetchArray()[ 0 ] == 1 )
    {
        $out = true;
    }
    else
    {
        $out = false;
    }

    return $out;
}
?>