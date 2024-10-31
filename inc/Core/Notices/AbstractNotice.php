<?php

namespace NativeRent\Core\Notices;

abstract class AbstractNotice implements NoticeInterface {
	/** @var string */
	protected $content;

	/** @var string */
	protected $level;

	/** @var array<string, mixed> */
	protected $options;

	/**
	 * {@inheritDoc}
	 */
	public function getContent() {
		return $this->content;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getLevel() {
		return $this->level;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getOptions() {
		return $this->options;
	}
}
