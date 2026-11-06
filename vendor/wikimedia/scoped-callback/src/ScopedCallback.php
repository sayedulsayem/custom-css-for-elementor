<?php
declare( strict_types = 1 );

/**
 * This file deals with RAII style scoped callbacks.
 *
 * Copyright (C) 2016 Aaron Schulz <aschulz@wikimedia.org>
 *
 * @license GPL-2.0-or-later
 * @file
 */

namespace Wikimedia;

use UnexpectedValueException;

/**
 * Make a callback run when a dummy object leaves the scope.
 */
class ScopedCallback {
	/** @var callable|null */
	protected $callback;

	/**
	 * @param callable|null $callback
	 * @param array $params Callback arguments (since 1.0.0, MediaWiki 1.25)
	 */
	public function __construct(
		?callable $callback,
		protected readonly array $params = []
	) {
		$this->callback = $callback;
	}

	/**
	 * Trigger a scoped callback and destroy it.
	 * This is the same as just setting it to null.
	 */
	public static function consume( ?self &$sc ): void {
		$sc = null;
	}

	/**
	 * Destroy a scoped callback without triggering it.
	 */
	public static function cancel( ?self &$sc ): void {
		if ( $sc ) {
			$sc->callback = null;
		}
		$sc = null;
	}

	/**
	 * Make PHP ignore user aborts/disconnects until the returned
	 * value leaves scope. This returns null and does nothing in CLI mode.
	 *
	 * @since 3.0.0
	 *
	 * @codeCoverageIgnore CI is only run via CLI, so this will never be exercised.
	 * Also no benefit testing a function just returns null.
	 */
	#[\NoDiscard]
	public static function newScopedIgnoreUserAbort(): ?self {
		// ignore_user_abort previously caused an infinite loop on CLI
		// https://bugs.php.net/bug.php?id=47540
		if ( PHP_SAPI !== 'cli' ) {
			// avoid half-finished operations
			$old = ignore_user_abort( true );
			return new self( static function () use ( $old ): void {
				ignore_user_abort( (bool)$old );
			} );
		}

		return null;
	}

	/**
	 * Trigger the callback when it leaves scope.
	 */
	public function __destruct() {
		if ( $this->callback !== null ) {
			( $this->callback )( ...$this->params );
		}
	}

	/**
	 * Do not allow this class to be serialized
	 */
	public function __serialize(): never {
		throw new UnexpectedValueException( static::class . ' cannot be serialized' );
	}

	/**
	 * Protect the caller against arbitrary code execution
	 */
	public function __unserialize( array $data ): never {
		$this->callback = null;
		throw new UnexpectedValueException( static::class . ' cannot be unserialized' );
	}
}
