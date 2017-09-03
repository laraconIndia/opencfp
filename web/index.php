<?php

require_once __DIR__ . '/../vendor/autoload.php';

use OpenCFP\Application;
use OpenCFP\Environment;

$basePath    = realpath( dirname( __DIR__ ) );
$environment = Environment::fromEnvironmentVariable();

if ( $environment->equals( Environment::production() ) )
{
	$authUser     = $_SERVER['PHP_AUTH_USER'] ?? '';
	$authPassword = $_SERVER['PHP_AUTH_PW'] ?? '';

	if ( $authUser !== 'PHPDD18' || $authPassword !== 'PreView' )
	{
		header( 'WWW-Authenticate: Basic realm="Login"' );
		header( 'HTTP/1.0 401 Unauthorized' );
		echo 'Unauthorized';
		exit;
	}
}

$app = new Application( $basePath, $environment );

$app->run();
