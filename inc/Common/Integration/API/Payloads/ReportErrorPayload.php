<?php

namespace NativeRent\Common\Integration\API\Payloads;

use Exception;

use function gethostname;
use function is_string;
use function php_uname;
use function phpversion;

final class ReportErrorPayload {
	use AuthorizedPayload;

	/**
	 * Arrayed exception.
	 *
	 * @var array{name: string, message: string, file: string, line: int, trace: string}
	 */
	private $error;

	/**
	 * Some additional data.
	 *
	 * @var array
	 */
	private $extra;

	/**
	 * Env tags.
	 *
	 * @var array{release: string, environment: string, server_name: string}
	 */
	private $tags;

	/**
	 * Context info.
	 *
	 * @var array{os: string, cms: string, runtime: string, url: string}
	 */
	private $context;

	/**
	 * @param string                                                           $siteID Current site ID.
	 * @param Exception|\Throwable                                             $error  Exception.
	 * @param array{release: string, environment: string, server_name: string} $tags
	 * @param array{os: string, cms: string, runtime: string, url: string}     $context
	 * @param array                                                            $extra  Extra data.
	 */
	public function __construct( $siteID, $error, $tags = [], $context = [], $extra = [] ) {
		$this->siteID = $siteID;
		$this->extra  = $extra;
		$this->setError( $error );
		$this->setTags( $tags );
		$this->setContext( $context );
	}

	/**
	 * @param Exception|\Throwable $error
	 *
	 * @return void
	 */
	private function setError( $error ) {
		$this->error = [
			'name'    => get_class( $error ),
			'message' => $error->getMessage(),
			'file'    => $error->getFile(),
			'line'    => $error->getLine(),
			'trace'   => json_encode( $error->getTrace() ),
		];
	}

	private function setTags( $tags ) {
		$hostname   = @gethostname();
		$this->tags = [
			'release'     => isset( $tags['release'] ) ? $tags['release'] : 'undefined',
			'environment' => isset( $tags['environment'] ) ? $tags['environment'] : 'production',
			'server_name' => isset( $tags['environment'] )
				? $tags['environment']
				: ( is_string( $hostname ) ? $hostname : '' ),
		];
	}

	private function setContext( $context ) {
		$this->context = [
			'os'      => isset( $context['os'] ) ? $context['os'] : @php_uname(),
			'cms'     => isset( $context['cms'] ) ? $context['cms'] : '',
			'runtime' => isset( $context['runtime'] ) ? $context['runtime'] : ( 'PHP ' . @phpversion() ),
			'url'     => isset( $context['url'] )
				? $context['url']
				: ( isset( $_SERVER['REQUEST_URI'] ) ? $_SERVER['REQUEST_URI'] : '' ), // phpcs:ignore
		];
	}

	/**
	 * @return array
	 */
	public function getError() {
		return $this->error;
	}

	/**
	 * @return array
	 */
	public function getExtra() {
		return $this->extra;
	}

	/**
	 * @return array
	 */
	public function getTags() {
		return $this->tags;
	}

	/**
	 * @return array
	 */
	public function getContext() {
		return $this->context;
	}
}
