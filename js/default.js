/*
 * SimpleModal Login
 * Theme: default
 * Copyright (c) 2012 Eric Martin http://www.ericmmartin.com
 */
jQuery(function ($) {
	var SimpleModalLogin = {
		init: function () {
			var s = this;
			s.error = [];

			$('.simplemodal-login, .simplemodal-register, .simplemodal-forgotpw').live('click.simplemodal-login', function (e) {
				s.login = $('#loginform'),
					s.lostpw = $('#lostpasswordform'),
					s.register = $('#registerform');

				if ($(this).hasClass('simplemodal-login')) {
					s.form = '#loginform';
					s.login.show(); s.lostpw.hide(); s.register.hide();
				}
				else if ($(this).hasClass('simplemodal-register')) {
					s.form = '#registerform';
					s.register.show(); s.login.hide(); s.lostpw.hide();
				}
				else {
					s.form = '#lostpasswordform';
					s.lostpw.show(); s.login.hide(); s.register.hide();
				}
				s.url = this.href;

				if (!$('#simplemodal-login-container').length) {
					$('#simplemodal-login-form').modal({
						overlayId: 'simplemodal-login-overlay',
						containerId: 'simplemodal-login-container',
						opacity:85,
						onShow: SimpleModalLogin.show,
						position: ['15%', null],
						zIndex:10000
					});
				}
				else {
					SimpleModalLogin.show();
				}
				return false;
			});

			if (SimpleModalLoginL10n['shortcut'] === "true") {
				$(document).bind('keydown.simplemodal-login', SimpleModalLogin.keydown);
			}
		},
		show: function (obj) {
			var s = SimpleModalLogin;
			s.dialog = obj || s.dialog;
			s.modal = s.modal || this;
			var form = $(s.form, s.dialog.data[0]),
				fields = $('.simplemodal-login-fields', form[0]),
				activity = $('.simplemodal-login-activity', form[0]);

			// update and focus dialog
			s.dialog.container.css({height:'auto'});

			// remove any existing errors or messages
			s.clear(s.dialog.container[0]);

			form.unbind('submit.simplemodal-login').bind('submit.simplemodal-login', function (e) {
				e.preventDefault();

				// remove any existing errors or messages
				s.clear(s.dialog.container[0]);

				if (s.isValid(form)) {
					fields.hide(); activity.show();

					if (s.url && s.url.indexOf('redirect_to') !== -1) {
						var p = s.url.split('=');
						form.append($('<input type="hidden" name="redirect_to">').val(unescape(p[1])));
					}

					$.ajax({
						url: form[0].action,
						data: form.serialize(),
						type: 'POST',
						cache: false,
						success: function (resp) {
							var data = $(document.createElement('div')).html(resp),
								redirect = $('#simplemodal-login-redirect', data[0]);

							if (redirect.length) {
								var href = location.href;
								if (redirect.html().length) {
									href = redirect.html();
								}
								window.location = href;
							}
							else {
								var error = $('#login_error', data[0]),
								message = $('.message', data[0]),
								loginform = $(s.form, data[0]);

								if (error.length) {
									error.find('a').addClass('simplemodal-forgotpw');
									$('p:first', form[0]).before(error);
									activity.hide(); fields.show();
								}
								else if (message.length) {
									if (s.form === '#lostpasswordform' || s.form === '#registerform') {
										form = s.login;
										s.lostpw.hide(); s.register.hide();
										s.login.show();
									}
									$('p:first', form[0]).before(message);
									activity.hide(); fields.show();
								}
								else if (loginform.length) {
									s.showError(form, ['empty_all']);
									activity.hide(); fields.show();
								}
							}
						},
						error: function (xhr) {
							$('p:first', form[0]).before(
								$(document.createElement('div'))
									.html('<strong>ERROR</strong>: ' + xhr.statusText)
									.attr('id', 'login_error')
							);
							activity.hide(); fields.show();
						}
					});
				}
				else {
					s.showError(form, s.error);
				}
			});
		},
		/* utility functions */
		clear: function (context) {
			$('#login_error, .message', context).remove();
		},
		isValid: function (form) {
			var log = $('.user_login', form[0]),
				pass = $('.user_pass', form[0]),
				email = $('.user_email', form[0]),
				fields = $(':text, :password', form[0]),
				valid = true;

			SimpleModalLogin.error = [];

			if (log.length && !$.trim(log.val())) {
				SimpleModalLogin.error.push('empty_username');
				valid = false;
			}
			else if (pass.length && !$.trim(pass.val())) {
				SimpleModalLogin.error.push('empty_password');
				valid = false;
			}
			else if (email.length && !$.trim(email.val())) {
				SimpleModalLogin.error.push('empty_email');
				valid = false;
			}

			var empty_count = 0;
			fields.each(function () {
				if (!$.trim(this.value)) {
					empty_count++;
				}
			});
			if (fields.length > 1 && empty_count === fields.length) {
				SimpleModalLogin.error = ['empty_all'];
				valid = false;
			}

			return valid;
		},
		keydown: function (e) {
			if (e.altKey && e.ctrlKey && e.keyCode === 76) {
				if (SimpleModalLoginL10n['logged_in'] === "true") {
					window.location = SimpleModalLoginL10n['admin_url'];
				}
				else {
					$('.simplemodal-login').trigger('click.simplemodal-login');
				}
			}
		},
		message: function (key) {
			return SimpleModalLoginL10n[key] ?
				SimpleModalLoginL10n[key].replace(/&gt;/g, '>').replace(/&lt;/g, '<') :
				key;
		},
		showError: function (form, keys) {
			keys = $.map(keys, function (key) {
				return SimpleModalLogin.message(key);
			});
			$('p:first', form[0])
				.before($('<div id="login_error"></div>').html(
					keys.join('<br/>')
				));
		}
	};

	SimpleModalLogin.init();
});