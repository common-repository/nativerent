<?php

namespace NativeRent\Common\Articles;

class Article {
	/** @var int|string|null */
	public $id;

	/** @var string|null */
	public $permalink;

	/**
	 * @param  array{id?: string|int, permalink?: string} $data
	 */
	public function __construct( $data = [] ) {
		$this->id        = isset( $data['id'] ) ? $data['id'] : null;
		$this->permalink = isset( $data['permalink'] ) ? $data['permalink'] : null;
	}
}
