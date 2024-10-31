<?php

namespace NativeRent\Common\Articles;

use WP_Post;
use WP_Query;

use function get_permalink;
use function is_int;

final class WpPostsRepository implements RepositoryInterface {

	/**
	 * {@inheritDoc}
	 */
	public function getPublishedArticles( $page = 1, $perPage = 20 ) {
		$posts = ( new WP_Query() )->query(
			[
				'post_type'      => 'post',
				'post_status'    => [ 'publish' ],
				'orderby'        => 'ID',
				'order'          => 'DESC',
				'posts_per_page' => (int) $perPage,
				'paged'          => (int) ( $page > 0 ? $page : 1 ),
			]
		);

		$articles = [];
		foreach ( $posts as $post ) {
			$post = is_int( $post ) ? WP_Post::get_instance( $post ) : $post;
			if ( empty( $post ) ) {
				continue;
			}
			$articles[] = $this->mapPostToArticle( $post );
		}

		return $articles;
	}

	private function mapPostToArticle( WP_Post $post ) {
		return new Article(
			[
				'id'        => $post->ID,
				'permalink' => get_permalink( $post ),
			]
		);
	}
}
