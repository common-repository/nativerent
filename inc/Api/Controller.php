<?php

namespace NativeRent\Api;

use NativeRent\Api\Handlers\UpdateAdUnitsConfig;
use NativeRent\Api\Request\SignatureVerifier;
use NativeRent\Common\Articles\Article;
use NativeRent\Common\Articles\RepositoryInterface;
use NativeRent\Common\Entities\CmsInfo;
use NativeRent\Common\Entities\Monetizations;
use NativeRent\Common\Entities\State;
use NativeRent\Common\Options;
use NativeRent\Core\Container\Exceptions\DependencyNotFound;

/**
 * Main API controller.
 */
class Controller {
	/**
	 * @var Options
	 */
	private $options;

	/**
	 * @var array|false|null
	 */
	private $requestBody;

	/**
	 * Init controller.
	 *
	 * @throws \NativeRent\Core\Container\Exceptions\DependencyNotFound
	 */
	public function __construct() {
		// Init props.
		$this->options     = nrentapp( Options::class );
		$this->requestBody = $this->decodeRequestBody();

		// Middlewares.
		$this->verifyRequest();
	}

	/**
	 * Getting current plugin state.
	 *
	 * @return void
	 * @api POST NativeRentApiV1=state
	 */
	public function state() {
		$this->jsonResponse(
			[
				'result' => 1,
				'state'  => new State( $this->options->getStateOptions(), CmsInfo::autoCreate() ),
			]
		);
	}

	/**
	 * Getting current installation status.
	 *
	 * @return void
	 * @api POST NativeRentApiV1=check
	 */
	public function check() {
		$this->jsonResponse(
			[
				'result' => (
					! empty( $this->requestBody['siteID'] )
					&& $this->options->getSiteID() === $this->requestBody['siteID']
				) ? 1 : 0,
			]
		);
	}

	/**
	 * Getting list of articles permalinks.
	 *
	 * @return void
	 * @throws DependencyNotFound
	 * @api POST NativeRentApiV1=articles
	 */
	public function articles() {
		$articles = nrentapp( RepositoryInterface::class )->getPublishedArticles(
			isset( $this->requestBody['page'] ) ? $this->requestBody['page'] : 1,
			isset( $this->requestBody['per_page'] ) ? $this->requestBody['per_page'] : 5
		);

		$this->jsonResponse(
			[
				'result' => 1,
				'articles' => array_map(
					function ( Article $a ) {
						return $a->permalink;
					},
					$articles
				),
			]
		);
	}

	/**
	 * Updating patterns for blocking 3rd-party advertisement.
	 *
	 * @return void
	 * @api POST NativeRentApiV1=updateAdvPatterns
	 */
	public function updateAdvPatterns() {
		$res = 0;
		if ( isset( $this->requestBody['patterns'] ) && is_array( $this->requestBody['patterns'] ) ) {
			$this->options->updateAdvPatterns( $this->requestBody['patterns'] );
			$res = 1;
		}

		$this->jsonResponse( [ 'result' => $res ] );
	}

	/**
	 * Updating monetizations statuses.
	 *
	 * @return void
	 * @api POST NativeRentApiV1=updateMonetizations
	 */
	public function updateMonetizations() {
		$updated = false;
		$data    = @$this->requestBody['monetizations'];
		if ( is_array( $data ) && isset( $data['regular'] ) && isset( $data['ntgb'] ) ) {
			$updated = $this->options->updateMonetizations( Monetizations::hydrate( $data ) );
		}

		$this->jsonResponse( [ 'result' => $updated ? 1 : 0 ] );
	}

	/**
	 * Method for updating ad-units configuration for Native Rent support.
	 *
	 * @return void
	 * @api POST /?NativeRentApiV1=updateAdUnitsConfig
	 */
	public function updateAdUnitsConfig() {
		$payload = @$this->requestBody['adUnitsConfig'];
		$handler = new UpdateAdUnitsConfig( $this->options );
		$res     = $handler( $payload );

		$this->jsonResponse( [ 'result' => $res ? 1 : 0 ] );
	}

	/**
	 * Decode request payload
	 *
	 * @return mixed
	 */
	private function decodeRequestBody() {
		return json_decode( trim( file_get_contents( 'php://input' ) ), true );
	}

	/**
	 * Request verification.
	 *
	 * @return void
	 */
	private function verifyRequest() {
		$verifier = new SignatureVerifier( $this->options->getSecretKey() );
		if ( ! $verifier->verify( $this->requestBody ) ) {
			$this->accessDeniedResponse();
		}
	}

	/**
	 * Send response.
	 *
	 * @param  array $body    Response body struct.
	 * @param  int   $status  Response status.
	 *
	 * @return void
	 */
	private function jsonResponse( $body, $status = 200 ) {
		header( 'Content-type: application/json', true, $status );
		if ( 204 !== $status ) {
			echo json_encode( $body, JSON_UNESCAPED_UNICODE );
		}
		exit( 0 );
	}

	/**
	 * Send 403 response.
	 *
	 * @return void
	 */
	private function accessDeniedResponse() {
		$this->jsonResponse(
			[
				'result'  => 0,
				'message' => 'Access denied',
			],
			403
		);
	}
}
