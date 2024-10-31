<?php

namespace NativeRent\Core\Notices;

interface NoticeInterface {
	const LEVEL_INFO = 'info';
	const LEVEL_SUCCESS = 'success';
	const LEVEL_WARNING = 'warning';
	const LEVEL_ERROR = 'error';

	/**
	 * Notice content getter.
	 *
	 * @return string HTML string
	 */
	public function getContent();

	/**
	 * Notice level value getter.
	 *
	 * @return string
	 */
	public function getLevel();

	/**
	 * Getter of additional options.
	 *
	 * @return array<string, mixed>
	 */
	public function getOptions();
}
