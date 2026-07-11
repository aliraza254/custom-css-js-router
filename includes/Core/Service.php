<?php
namespace CustomCssJsRouter\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

interface Service {
	public function register(): void;
}
