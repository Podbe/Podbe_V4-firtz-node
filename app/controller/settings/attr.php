<?php
/**
 * Created by Wikibyte.org
 * User: McCouman
 * Date: 20.12.15
 * Time: 15:42
 */

$f3->set( 'firtzattr_default', array(
        'podcastalias',
        'baseurlredirect'
    )
);

$f3->set( 'podcastattr_default', array(

        //base data
        'title',
        'description',
        'summary',
        'image',
        'keywords',
        'category',
        'language',
        'explicit',
        'formats',

        //impressum
        'author',
        'street',
        'plz',
        'city',
        'country',
        'email',

        //social
        'flattrid',
        'twitter',
        'adn',
        'adntoken',

        //license
        'licenseurl',
        'licensename',
        'licenseimage',

        //services
        'itunes',
        'itunesblock',
        'auphonic-path',
        'auphonic-glob',
        'auphonic-url',
        'auphonic-mode',
        'bitlove',

        //system data
        'baseurl',
        'redirect',
        'mediabaseurl',
        'mediabasepath',
        'redirect',
        'cloneurl',
        'clonepath',
        'rfc5005',
        'podcastalias',
        'articles-per-page',
        'template',
        'templatevars'
    )
);

$f3->set( 'itemattr', array(
        'title',
        'description',
        'link',
        'guid',
        'article',
        'payment',
        'chapters',
        'enclosure',
        'duration',
        'keywords',
        'image',
        'date',
        'noaudio',
        'location'
    )
);

$f3->set( 'extattr', array(
        //admin
        'admin',

        //default
        'slug',
        'template',
        'arguments',
        'prio',
        'script',
        'type',
        'settings',
        'episode-settings',
        'podcast-settings'
    )
);

$f3->set( 'mimetypes', array(
        'mp3'     => 'audio/mpeg',
        'torrent' => 'application/x-bittorrent',
        'mpg'     => 'video/mpeg',
        'm4a'     => 'audio/mp4',
        'm4v'     => 'video/mp4',
        'oga'     => 'audio/ogg',
        'ogg'     => 'audio/ogg',
        'ogv'     => 'video/ogg',
        'webma'   => 'audio/webm',
        'webm'    => 'video/webm',
        'flac'    => 'audio/flac',
        'opus'    => 'audio/ogg;codecs=opus',
        'mka'     => 'audio/x-matroska',
        'mkv'     => 'video/x-matroska',
        'pdf'     => 'application/pdf',
        'epub'    => 'application/epub+zip',
        'png'     => 'image/png',
        'jpg'     => 'image/jpeg',
        'mobi'    => 'application/x-mobipocket-ebook'
    )
);

?>