/*
 * SimpleModal Login
 * Theme: default
 * Revision: $Id$
 * Copyright (c) 2010 Eric Martin http://www.ericmmartin.com
 */
jQuery(function ($) {
	var SimpleModalLogin = {
		init: function () {
			var s = this;
			s.error = null;

			$('.simplemodal-login, .simplemodal-register, .simplemodal-forgotpw').click(function (e) {
				if ($(this).hasClass('simplemodal-login')) {
					s.form = '#loginform';
					$('#loginform').show();
					$('#lostpasswordform, #registerform').hide()
				}
				else if ($(this).hasClass('simplemodal-register')) {
					s.form = '#registerform';
					$('#registerform').show();
					$('#lostpasswordform, #loginform').hide()
				}
				else {
					s.form = '#lostpasswordform';
					$('#lostpasswordform').show();
					$('#loginform, #registerform').hide()
				}
				s.url = this.href;

				if (!$('#simplemodal-login-container').length) {
					$('#simplemodal-login-form').modal({
						overlayId: 'simplemodal-login-overlay',
						containerId: 'simplemodal-login-container',
						opacity:85,
						onShow: SimpleModalLogin.show,
						position: ['15%',]
					});
				}
				else {
					SimpleModalLogin.show();
				}
				return false;
			});
		},
		show: function (obj) {
			SimpleModalLogin.dialog = obj || SimpleModalLogin.dialog; 
			var form = $(SimpleModalLogin.form, SimpleModalLogin.dialog.data[0]);

			// focus on first element
			$(':input:visible:first', form[0]).focus();

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
								loginform = $(SimpleModalLogin.form, data[0]);

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
								$.modal.close();
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
			var log = $('.user_login', form[0]),
				pass = $('.user_pass', form[0]),
				email = $('.user_email', form[0]),
				fields = $(':text, :password', form[0]),
				valid = true;

			
			if (log && !$.trim(log.val())) {
				SimpleModalLogin.error = 'empty_username';
				valid = false;
			}
			else if (!pass && !$.trim(pass.val())) {
				SimpleModalLogin.error = 'empty_password';
				valid = false;
			}
			else if (!email && !$.trim(email.val())) {
				SimpleModalLogin.error = 'empty_email';
				valid = false;
			}
			
			var empty_count = 0;
			fields.each(function () {
				if (!$.trim(this.value)) {
					empty_count++;
				}
			});
			if (empty_count === fields.length) {
				SimpleModalLogin.error = 'empty_all';
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