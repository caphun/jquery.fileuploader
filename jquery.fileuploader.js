/*
 * jQuery uploader
 *
 * Copyright (c) 2009 Ca-Phun Ung <caphun at yelotofu dot com>
 * Dual licensed under the MIT (MIT-LICENSE.txt)
 * and GPL (GPL-LICENSE.txt) licenses.
 *
 * http://yelotofu.com/labs/jquery/snippets/uploader/
 *
 * File upload using an iframe with an indeterminate progress bar.
 */

(function($) {
	
	$.fn.uploader = function(options) {
		
		var options = $.extend({
			'successLabel': 'OK',
			'url': './vendor/php/backend.php',
			'indicator': '<img src="./theme/indicator.gif" width="16" height="16" class="spinner" style="display:none" />',
			'trash': '<img src="./theme/trash_on.gif" alt="remove" width="10" height="11" border="0" />',
			'data': 'upload_dir=/uploads/'
		}, options);
		
		// append an iframe to the body for later use
		$('body').append('<iframe id="iframeUploadFile" name="iframeUploadFile" width="400" height="100" style="display:none"></iframe>');
		
		return $.each(this, function() {
			var self = $(this),
				form = self.wrap('<form enctype="multipart/form-data" method="post" action="'+ options.url +'" target="iframeUploadFile"></form>').parent();
			
				var params = {};

				// loop through data for params
				$.each(options.data && options.data.split('&'), function(i,n) {
					var item = n.split('=');
					params[item[0].toLowerCase()] = item[1];
				});

				// loop through hidden field params
				$('input:hidden[name]').each(function(i,n) {
					params[$(this).attr('name').toLowerCase()] = $(this).attr('value');
				});

				// generate hidden data
				for (var i in params) {
					form.append('<input type="hidden" name="options['+i+']" value="'+params[i]+'" />');
				};
			
			self
				.bind('change', function() {
					form.find('.spinner').show();
					form.submit();
					return false;
				})
				.closest('form')
					.append(options.indicator).find('.spinner').hide();
					
			$('#iframeUploadFile')
				.bind('load', function() {
					var json = eval($(this)[0].contentWindow.document.body.innerHTML);
					if (json && json.length > 0) {
						var response = json[0].response;
						if (json[0].status == options.successLabel) {

							form.hide();
							$('<span style="display:none"><a target="_blank" class="file"></a> <a href="#" class="remove">'+options.trash+'</a> <input type="hidden" /></span>')
								.insertAfter(form)
								.find('a.remove')
									.bind('click', function() {
										$(this).parent().remove();
										$.post(options.url, response, function(data) {
											// TODO: handle success or failure notification
										});
										form.show();
										return false;
									})
								.end()
								.find('a.file')
									.attr('href', response.url)
									.html(response.filename)
								.end()
								.find('input:hidden')
									.attr('name', self[0].name)
									.attr('value', response.filename)
								.end()
								.show();

						} else if ( response ) {
							$('<span class="error">'+ response +'</span><br />').insertBefore(form);
						}
					}
					form.find('.spinner').hide().end().find('input:file').val('');
				});
		});
	}
	
	
})(jQuery);