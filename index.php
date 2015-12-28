<?php
/**
 * PODNODES DIRECTORY - firtz-knoten
 *
 * @info            PodNode ist ein auf firtz aufbauendes Podcastverzeichnis
 *                  Es wurde umgesetzt um als Knoten ...
 *
 * @version         1.0
 * @project         podnodes by podbe.de
 * @url             podbe.de
 *
 * @developer       by michael mccouman jr.
 * @design          by michael mccouman jr.
 * @date            last update 13.12.2015
 *
 * @firtz           firtz 2.0 by christian bednarek
 * @firtz-url       firtz.org
 * @firtz-design    quorx II by michael mccouman jr.
 * @framework       fat-free-framework
 */

$f3 = require( 'app/assets/lib/base.php' );
$f3 = Base::instance();

$f3->config( 'app/config.ini' );

$run = new controller();
$run->settings( $f3 );

$f3->run();

?>