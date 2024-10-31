<?php

namespace NativeRent\Core\Notices;

use Generator;
use SplPriorityQueue;

class NoticesRegistry {

	/** @var SplPriorityQueue */
	private $queue;

	public function __construct() {
		$this->queue = new SplPriorityQueue();
		$this->queue->setExtractFlags( SplPriorityQueue::EXTR_BOTH );
	}

	/**
	 * Adding notice to registry.
	 *
	 * @param  NoticeInterface $notice
	 * @param  int             $priority
	 *
	 * @return self
	 */
	public function addNotice( NoticeInterface $notice, $priority = 10 ) {
		$this->queue->insert( $notice, $priority * - 1 );

		return $this;
	}

	/**
	 * Extracting all notices.
	 *
	 * @return Generator<int, array{NoticeInterface, int}>
	 * @see SplPriorityQueue::extract()
	 */
	public function extractNotices() {
		while ( ! $this->queue->isEmpty() ) {
			$i = $this->queue->extract();
			yield [ $i['data'], $i['priority'] * - 1 ];
		}
	}

	/** @return bool */
	public function isEmpty() {
		return $this->queue->isEmpty();
	}
}
