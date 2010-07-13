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
				var login = $('#loginform'),
					lostpw = $('#lostpasswordform'),
					register = $('#registerform');

				if ($(this).hasClass('simplemodal-login')) {
					s.form = '#loginform';
					login.show(); lostpw.hide(); register.hide();
				}
				else if ($(this).hasClass('simplemodal-register')) {
					s.form = '#registerform';
					register.show(); login.hide(); lostpw.hide();
				}
				else {
					s.form = '#lostpasswordform';
					lostpw.show(); login.hide(); register.hide();
				}

				s.url = this.href;

				if (!$('#simplemodal-login-container').length) {
					$('#simplemodal-login-form').modal({
						overlayId: 'simplemodal-login-overlay-osx',
						containerId: 'simplemodal-login-container-osx',
						containerCss: {height:'40px'},
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
			var self = this;
			self.container = d.container[0];
			d.overlay.fadeIn('slow', function () {
				$('#simplemodal-login-osx-content', self.container).show();
				var title = $('#simplemodal-login-osx-title', self.container);
				title.show();
				d.container.slideDown('slow', function () {
					setTimeout(function () {
						var data = $('#simplemodal-login-form', self.container),
							h = data.height()
							+ title.height()
							+ 40; // padding
						d.container.animate(
							{height: h},
							200,
							function () {
								$('div.close', self.container).show();
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
			SimpleModalLogin.dialog = obj || SimpleModalLogin.dialog;
			var dialog = this,
				form = $(SimpleModalLogin.form, SimpleModalLogin.dialog.data[0]),
				fields = $('.simplemodal-login-fields', form[0]),
				activity = $('.simplemodal-login-activity', form[0]);

			// focus on first element
			$(':input:visible:first', form[0]).focus();

			form.unbind('submit.simplemodal').bind('submit.simplemodal', function (e) {
				e.preventDefault();

				// remove any existing errors
				$('#login_error', form[0]).remove();

				if (SimpleModalLogin.isValid(form)) {
					fields.hide(); activity.show();
					
					if (SimpleModalLogin.url && SimpleModalLogin.url.indexOf('redirect_to') !== -1) {
						var p = SimpleModalLogin.url.split('=');
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
					SimpleModalLogin.showError(form, SimpleModalLogin.error);
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
			var log = $.trim($('.user_login', form[0])),
				pass = $.trim($('.user_pass', form[0])),
				email = $.trim($('.user_email', form[0])),
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