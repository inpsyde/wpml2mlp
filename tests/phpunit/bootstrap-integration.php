<?php # -*- coding: utf-8 -*-

namespace W2M\Test;

use
	WpTestsStarter\WpTestsStarter;

$plugin_dir = dirname(
	dirname( // tests/
		__DIR__ // phpunit/
	)
);

$autoload = $plugin_dir . '/vendor/autoload.php';
// Todo: make this depending on the install type:
require_once $autoload;

$test_starter = new WpTestsStarter( $plugin_dir . '/vendor/inpsyde/wordpress-dev' );
$test_starter->defineAbspath( $plugin_dir . '/vendor/inpsyde/wordpress-dev/src/' );
$test_starter->setTablePrefix( TABLE_PREFIX );
$test_starter->defineDbName( DB_NAME );
$test_starter->defineDbUser( DB_USER );
$test_starter->defineDbPassword( DB_PASS );
$test_starter->defineDbHost( DB_HOST );

$test_starter->defineConst( 'WP_TESTS_MULTISITE', TRUE );

$test_starter->defineTestsDomain( 'example.org' );
$test_starter->defineTestsEmail( 'admin@example.org' );
$test_starter->defineTestsTitle( 'WPML 2 MLP Tests' );

$test_starter->defineConst( 'WP_PLUGIN_DIR', dirname( $plugin_dir ) );

$GLOBALS[ 'wp_tests_options' ] = array(
	'active_plugins' => array( basename( $plugin_dir ) . '/wpml2mlp.php' ),
);
$test_starter->bootstrap();