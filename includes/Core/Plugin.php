<?php
namespace CustomCssJsRouter\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Plugin {
	private Container $container;

	public function __construct( Container $container ) {
		$this->container = $container;
	}

	public function boot(): void {
		$services = [
			'assets',
			'injector',
			'admin',
			'ajax',
		];

		foreach ( $services as $service_name ) {
			if ( $this->container->has( $service_name ) ) {
				$service = $this->container->get( $service_name );
				if ( $service instanceof Service ) {
					$service->register();
				}
			}
		}
	}
}
