<?php
namespace CustomCssJsRouter\Core;

use Closure;
use Exception;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Container {
	private array $services = [];
	private array $instances = [];

	public function bind( string $name, Closure $closure ): void {
		$this->services[ $name ] = $closure;
	}

	public function singleton( string $name, Closure $closure ): void {
		$this->services[ $name ] = function( $c ) use ( $closure ) {
			static $instance;
			if ( null === $instance ) {
				$instance = $closure( $c );
			}
			return $instance;
		};
	}

	public function get( string $name ) {
		if ( isset( $this->instances[ $name ] ) ) {
			return $this->instances[ $name ];
		}

		if ( ! isset( $this->services[ $name ] ) ) {
			throw new Exception( sprintf( 'Service %s is not defined in the container.', esc_html( $name ) ) );
		}

		$this->instances[ $name ] = call_user_func( $this->services[ $name ], $this );
		return $this->instances[ $name ];
	}

	public function has( string $name ): bool {
		return isset( $this->services[ $name ] ) || isset( $this->instances[ $name ] );
	}
}
