/*
 * SimpleModal Login
 * Theme: osx
 * Revision: $Id$
 * Copyright (c) 2010 Eric Martin http://www.ericmmartin.com
 */
jQuery(function ($) {
	var SimpleModalLogin = {
		container: null,
		init: function () {
			var s = this;
			s.error = [];

			$('.simplemodal-login, .simplemodal-register, .simplemodal-forgotpw').live('click.simplemodal', function (e) {
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
						overlayId: 'simplemodal-login-overlay-osx',
						containerId: 'simplemodal-login-container-osx',
						containerCss: {height:'50px'},
						closeHTML: '<div class="close"><a href="#" class="simplemodal-close">x</a></div>',
						minHeight:80,
						opacity:65,
						position:['0',],
						overlayClose:true,
						onOpen:SimpleModalLogin.open,
						onShow:SimpleModalLogin.show,
						onClose:SimpleModalLogin.close
					});
				}
				else {
					SimpleModalLogin.show();
				}
				return false;
			});
		},
		open: function (d) {
			var s = this;
			s.container = d.container[0];
			d.overlay.fadeIn('slow', function () {
				$('#simplemodal-login-osx-content', s.container).show();
				var title = $('#simplemodal-login-osx-title', s.container);
				title.show();
				d.container.slideDown('slow', function () {
					setTimeout(function () {
						var data = $('#simplemodal-login-form', s.container),
							h = data.height()
							+ title.height();
						d.container.animate(
							{height: h},
							200,
							function () {
								$('div.close', s.container).show();
								data.show();

								// focus on username
								$('#user_login', s.container).focus();
								d.container.css({height:'auto'});
							}
						);
					}, 300);
				});
			});
		},
		show: function (obj) {
			var s = SimpleModalLogin;
			s.dialog = obj || s.dialog; 
			var dialog = this,
				form = $(s.form, s.dialog.data[0]),
				fields = $('.simplemodal-login-fields', form[0]),
				activity = $('.simplemodal-login-activity', form[0]);

			// clear values and focus on first element
			$('.input', form[0]).val('').first().focus();

			form.unbind('submit.simplemodal').bind('submit.simplemodal', function (e) {
				e.preventDefault();

				// remove any existing errors or messages
				$('#login_error, .message', s.dialog.container[0]).remove();

				if (s.isValid(form)) {
					fields.hide(); activity.show();
					
					if (s.url && s.url.indexOf('redirect_to') !== -1) {
						var p = s.url.split('=');
						$('#redirect_to', form[0]).val(unescape(p[1]));
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
								dialog.close();
								var href = location.href;
								if (redirect.length) {
									href = redirect.html();
								}
								setTimeout(function () {window.location = href;}, 500);
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
									s.showError(form, ['empty_both']);
									activity.hide(); fields.show();
								}
								$('.input', form[0]).first().focus();
							}
						}
					});
				}
				else {
					s.showError(form, s.error);
				}
			});
		},
		close: function (d) {
			var s = this;
			d.container.animate(
				{top:'-' + (d.container.height() + 20)},
				500,
				function () {
					s.close();
				}
			);
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