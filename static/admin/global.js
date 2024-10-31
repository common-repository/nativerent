/**
 * Plugin deactivation alert.
 */
document.addEventListener('DOMContentLoaded', function () {
	var deactivationLinks = document.querySelectorAll('a[id*=deactivate-nativerent]');
	deactivationLinks.forEach(function (link) {
		link.addEventListener('click', function () {
			alert('Если на сайте настроено кэширование, то после деактивации сбросьте кэш.')
		})
	})
})

/**
 * Notice about cache.
 * (Uses jQuery)
 */
document.addEventListener('DOMContentLoaded', function () {
	// Clear cache button handler.
	var handler = function (data, onError) {
		var $noticeMeta = jQuery('input[name="nrent_cache_notice"]')
		var url = $noticeMeta.data('action')
		var method = $noticeMeta.data('action-method')

		jQuery.ajax({
			url: url,
			type: method,
			data: data,
			success: function (response) {
				$noticeMeta.parent('.notice').remove()
			},
			error: function (xhr, status, error) {
				if (onError) {
					onError(xhr, status, error)
				}
				console.log(status, error)
			}
		})
	}

	jQuery(document).on('submit', '.nrent-clear-cache-form', function () {
		var $this = jQuery(this)
		var formData = $this.serializeArray()
		handler(formData, function () {
			alert($this.data('error-message'))
		})

		return false
	})

	// Dismiss button handler.
	jQuery(document).on('click', 'input[name="nrent_cache_notice"] ~ button.notice-dismiss', function () {
		handler([])
	})
})

