/*
 * SimpleModal Login
 * Theme: default
 * Revision: $Id$
 * Copyright (c) 2009 Eric Martin http://www.ericmmartin.com
 */
jQuery(function ($) {
	var SimpleModalLogin = {
		init: function () {
			var s = this;
			s.error = null;

			$('.simplemodal-login').click(function (e) {
				e.preventDefault();

				s.url = this.href;

				$('#simplemodal-login-form').modal({
					overlayId: 'simplemodal-login-overlay',
					containerId: 'simplemodal-login-container',
					opacity:85,
					onShow: SimpleModalLogin.show,
					position: ['15%',]
				});
			});
		},
		show: function (obj) {
			var dialog = this,
				form = $('#loginform', obj.data[0]);

			// focus on username
			$('#user_login', form[0]).focus();

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
								var redirect = $('#redirect_to', form[0]).val(),
									href = location.href;

								if (redirect.length > 0) {
									if (SimpleModalLogin.url && SimpleModalLogin.url.indexOf("redirect_to") !== -1) {
										var p = SimpleModalLogin.url.split("=");
										href = unescape(p[1]);
									}
									else {
										href = redirect;
									}
								}
								window.location = href;
								dialog.close();
							}
						}
					});
				}
				else {
					SimpleModalLogin.showError(form, SimpleModalLogin.error);
				}
			});
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