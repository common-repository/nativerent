<?php

namespace NativeRent\Common\Articles;

interface RepositoryInterface {
	/**
	 * @param  int $page     Number of page.
	 * @param  int $perPage  Page size.
	 *
	 * @return array<array-key, Article>
	 */
	public function getPublishedArticles( $page = 1, $perPage = 20 );
}
