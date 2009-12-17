/*
 * SimpleModal Login
 * Theme: osx
 * Revision: $Id$
 * Copyright (c) 2009 Eric Martin http://www.ericmmartin.com
 */
jQuery(function ($) {
	var SimpleModalLogin = {
		container: null,
		init: function () {
			this.error = null;
			$('.simplemodal-login').click(function (e) {
				e.preventDefault();	
	
				$('#simplemodal-login-form').modal({
					overlayId: 'simplemodal-login-overlay-osx',
					containerId: 'simplemodal-login-container-osx',
					closeHTML: '<div class="close"><a href="#" class="simplemodal-close">x</a></div>',
					minHeight:80,
					opacity:65, 
					position:['0',],
					overlayClose:true,
					onOpen:SimpleModalLogin.open,
					onShow:SimpleModalLogin.show,
					onClose:SimpleModalLogin.close
				});
			});		
		},
		open: function (d) {
			var self = this;
			self.container = d.container[0];
			d.overlay.fadeIn('slow', function () {
				$("#simplemodal-login-osx-content", self.container).show();
				var title = $("#simplemodal-login-osx-title", self.container);
				title.show();
				d.container.slideDown('slow', function () {
					setTimeout(function () {
						var data = $("#simplemodal-login-form", self.container),
							h = data.height()
							+ title.height()
							+ 40; // padding
						d.container.animate(
							{height: h}, 
							200,
							function () {
								$("div.close", self.container).show();
								data.show();		

								// focus on username
								$('#user_login', self.container).focus();
							}
						);
					}, 300);
				});
			})
		},
		show: function (obj) {
			var dialog = this,
				form = $('#loginform', obj.data[0]);
			
			form.submit(function (e) {
				e.preventDefault();

				// remove any existing errors
				$('#login_error', form[0]).remove();
				
				if (SimpleModalLogin.isValid(form)) {
					$.ajax({
						url: form[0].action,
						data: form.serialize(),
						type: 'POST',
						cache: false,
						success: function (resp) {
							var data = $('<div></div>').append(resp),
								error = $('#login_error', data[0]),
								loginform = $('#loginform', data[0]);
							
							if (error.length > 0) {
								$('p:first', form[0]).before(error);
							}
							else if (loginform.length > 0) {
								SimpleModalLogin.showError(form, 'empty_both');
							}
							else {
								dialog.close();
								var redirect = $('#redirect_to', form[0]).val(),
									href = location.href;
	
								if (redirect.length > 0) {
									href = redirect;
								}
								setTimeout(function () {window.location = href;}, 500);
							}
						}	
					});
				}
				else {
					SimpleModalLogin.showError(form, SimpleModalLogin.error);
				}
			});
		},
		close: function (d) {
			var self = this;
			d.container.animate(
				{top:"-" + (d.container.height() + 20)},
				500,
				function () {
					self.close();
				}
			);
		},
		isValid: function (form) {
			var log = $.trim($('#user_login', form[0]).val()),
				pass = $.trim($('#user_pass', form[0]).val()),
				valid = true;
			
			if (!log && !pass) {
				SimpleModalLogin.error = 'empty_both';
				valid = false;
			}
			else if (!log) {
				SimpleModalLogin.error = 'empty_username';
				valid = false;
			}
			else if (!pass) {
				SimpleModalLogin.error = 'empty_password';
				valid = false;
			}
			
			return valid;
		},
		message: function (key) {
			return SimpleModalLoginL10n[key] ? 
				SimpleModalLoginL10n[key].replace(/&gt;/g, '>').replace(/&lt;/g, '<') :
				key;
		},
		showError: function (form, key) {
			$('p:first', form[0])
				.before($('<div id="login_error"></div>').html(
					SimpleModalLogin.message(key)
				));
		}
	};

	SimpleModalLogin.init();
});