var User = require('../User')
var functions = require('../functions')

App_Modal = function (app) {

	var _self = {};
	_self.app = app;

	// this.app = function () {
	// 	return _self.app;
	// }; // app


	this.show = function (el, tab) {

		var self = this;

		// if ( !kanban.app.current_user().hasCap('admin-users') ) {
		// 	kanban.app.urlParamRemove('modal');
		// 	kanban.app.urlReplace();
		// 	return false;
		// }

		var optionsApp = functions.optionsFormat(kanban.app.options());
		var optionsUser = functions.optionsFormat(kanban.app.current_user().optionsApp());

		var appModalHtml = kanban.templates['app-modal'].render({
			currentUserIsAdmin: kanban.app.current_user().hasCap('admin-users'),
			optionsUser: optionsUser,
			optionsApp: optionsApp
		});

		$('#modal').html(appModalHtml);

		$('#modal').modal({
			backdrop: 'static',
			keyboard: false,
			show: true
		});

		// Attempt to reload tab
		if ( 'undefined' !== typeof tab && '' != tab ) {
			$('#modal-tab-' + tab).trigger('click');
		}
		else if ('undefined' !== typeof kanban.app.url().params['tab']) {
			$('#modal-tab-' + kanban.app.url().params['tab']).trigger('click');
		}

		if ( kanban.app.current_user().hasCap('admin-users') ) {
			// Get all users for searching
			$.when($.ajax({
				data: {
					type: 'user',
					action: 'get_admin'
				}
			}), $.ajax({
				data: {
					type: 'user',
					action: 'get_wp_users'
				}
			}))
			.done(function (response_admin_users, response_wp_users) {

				if ('undefined' === typeof response_admin_users || 'undefined' === typeof response_admin_users[0] || 'undefined' === typeof response_admin_users[0].data) {
					return false;
				}

				if ('undefined' === typeof response_wp_users || 'undefined' === typeof response_wp_users[0] || 'undefined' === typeof response_wp_users[0].data) {
					return false;
				}

				var response_admin_users = response_admin_users[0].data;
				var response_wp_users = response_wp_users[0].data;

				// Build admin users html.
				var usersHtml = '';
				for (var userId in response_admin_users) {

					var userRecord = response_admin_users[userId];

					// Replace to ensure they have caps.
					var user = kanban.users[userId] = new User(userRecord);

					usersHtml += self.userRender(user);
				}

				// Populate user users html.
				$('#app-modal-users-accordion').html(usersHtml);


				var values = [];
				for (var userId in response_wp_users) {

					// Skip users already added.
					if ('undefined' !== typeof response_admin_users[userId]) {
						continue;
					}

					if ('undefined' === typeof kanban.users[userId]) {
						kanban.users[userId] = response_wp_users[userId];
					}

					values.push(response_wp_users[userId]);
				}

				$('#app-modal-user-find-control').selectize({
					placeholder: 'Enter a name or email address to find a user',
					options: values,
					valueField: 'id',
					searchField: ['display_name', 'user_email'],
					closeAfterSelect: true,
					render: {
						option: function (item, escape) {
							var label = item.display_name || item.user_email;
							var caption = item.display_name ? item.user_email : null;
							return '<div>' +
								'<span class="b">' + escape(label) + '</span> ' +
								(caption ? '(' + escape(caption) + ')' : '') +
								'</div>';
						}
					},
					onChange: function (value) {
						self.userAdd(value);
						this.removeOption(value, true);
						this.clear(true);
					}
				});

				$('#modal-tab-pane-users').removeClass('loading');

			});
		}
		
		kanban.app.urlParamUpdate('modal', 'app');
		kanban.app.urlReplace();

		return false;
	}; // modalShow

	this.optionOnChange = function (el) {

		var self = this;

		if ( !kanban.app.current_user().hasCap('admin') ) {
			return false;
		}

		var $el = $(el);
		var key = $el.attr('data-name');
		var value = $el.val();

		kanban.app.optionUpdate(key, value);

		kanban.app.current_board().rerender();

	}; // optionUpdate

	this.optionUserOnChange = function (el) {

		var self = this;

		// No cap check, because users can always update their own options.

		var $el = $(el);
		var key = $el.attr('data-name');
		var value = $el.val();

		kanban.app.current_user().optionAppUpdate(key, value);

		// Give option time to save.
		setTimeout(function () {
			kanban.app.current_board().rerender();
		}, 1000);

	}; // optionUserUpdate

	this.optionOnfocus = function (el) {
		// console.log('modalTitleOnfocus');

		var self = this;

		var $el = $(el);

		// Save the current value for restoring.
		var value = $el.val();
		$el.data('prevValue', value);

	}; // optionOnfocus

	this.optionOnkeydown = function (el, e) {
		var self = this;

		var $el = $(el);

		var keyCode = e.keyCode;

		switch (keyCode) {
			case 13: // enter

				// Save it.
				el.blur();

				return false;

				break;

			case 27: // escape
				var prevValue = $el.data('prevValue');
				$el.val(prevValue);

				el.blur();

				break;
		}
	}; // optionOnkeydown

	this.optionOnblur = function (el) {
		// console.log('modalLaneOnblur');
		var self = this;

		var $el = $(el);
		var key = $el.attr('data-name');
		var value = $el.val();

		kanban.app.optionUpdate(key, value);

		kanban.app.current_board().rerender();


	}; // optionOnblur

	this.userAdd = function (userId) {
		var self = this;

		$.ajax({
			data: {
				type: 'user_cap',
				action: 'add_admin',
				user_id: userId
			}
		})
		.done(function (response) {

			if ( 'undefined' === typeof response.data ||'undefined' === typeof response.data.id ) {
				kanban.app.notify(kanban.strings.user.updated_error);
				return false;
			}

			var userId = response.data.id;
			var userRecord = response.data;
			var user = kanban.users[userId] = new User(userRecord);

			var userHtml = self.userRender(user);
			$(userHtml).appendTo('#app-modal-users-accordion').find('.panel-title').trigger('click');
		});
	}; // userAdd

	this.userSectionToggle = function (el, action) {

		var self = this;

		var $el = $(el);
		var $formGroup = $el.closest('.form-group');
		var $wrapperFormGroup = $el.closest('.wrapper-form-group');

		var cap = $formGroup.attr('data-cap');

		if ( cap == 'admin' ) {
			var $formGroups = $('.form-group', $wrapperFormGroup).not($formGroup);

			if ( action == 'show' ) {
				$formGroups.removeClass('hide-admin');
			}

			if ( action == 'hide' ) {
				$formGroups.addClass('hide-admin');
			}
		} else {
			var $formGroups = $('.form-group[data-cap^="' + cap + '-"]', $wrapperFormGroup);

			if ( action == 'show' ) {
				$formGroups.removeClass('hide-section');
			}

			if ( action == 'hide' ) {
				$formGroups.addClass('hide-section');
			}
		}

	}; // userSectionToggle


	this.userSave = function (el) {

		var self = this;

		var $el = $(el);
		var $panel = $el.closest('.panel');
		var userId = $panel.attr('data-user-id');

		var capsArr = $('[data-name="capabilities"]:checked', $panel).map(function () {
			return $(this).val();
		}).get();

		// Ajax won't send empty array, so send empty string instead.
		if ( capsArr.length == 0 ) {
			capsArr = '';
		}

		$.ajax({
			data: {
				type: 'user_cap',
				action: 'replace',
				user_id: userId,
				capabilities: capsArr
			}
		});

	}; // userSave

	this.userDelete = function (el) {
		var self = this;
		var confirmed = confirm('Are you sure you want to delete this user?');

		if (confirmed) {
			var userId = $(el).attr('data-user-id');
			var ajaxDate = {
				type: 'user_cap',
				action: 'delete_admin',
				user_id: userId
			};

			$.ajax({
				data: ajaxDate
			})
				.done(function (response) {
					$(el).closest('div[data-user-id=' + userId + ']').remove();
				});

			//add back deleted user to selectize options
			if ( 'undefined' !== typeof kanban.users[userId] ) {
				$('#app-modal-user-find-control')[0].selectize.addOption(kanban.users[userId].record());
			}
		}
	}; // userDelete

	this.userSelectAll = function (el) {

		$('#app-modal .app-modal-user-checkbox').prop('checked', true);
	}; // userSelectall

	this.userSelectNone = function (el) {

		$('#app-modal .app-modal-user-checkbox').prop('checked', false);
	}; // userSelectNone

	this.userRender = function (user) {

		var self = this;

		var usersHtml = '';

		// Copy caps (Use jQuery for deep copy)
		var caps = kanban.app.capsForUser(user);

		return kanban.templates['app-modal-user'].render({
			caps: caps,
			user: user.record(),
			allowDelete: caps.admin.is_checked && user.id() == kanban.app.current_user_id() ? false : true
		});

	}; // userRender

	// this.usergroupAdd = function (el) {
	// 	var self = this;
	//
	// 	var ajaxDate = {
	// 		type: 'usergroup',
	// 		action: 'replace',
	// 		label: '',
	// 	};
	//
	// 	$.ajax({
	// 		data: ajaxDate
	// 	})
	// 		.done(function (response) {
	//
	// 			var usergroupId = response.data.id;
	// 			var usergroupRecord = response.data;
	// 			var usergroup = kanban.usergroups[usergroupId] = new Usergroup(usergroupRecord);
	//
	// 			// Copy caps (Use jQuery for deep copy)
	// 			var caps = kanban.app.caps();
	//
	// 			// Denote caps from usergroup.
	// 			for (var i in usergroup.record().capabilities) {
	// 				var cap = usergroup.record().capabilities[i];
	// 				caps[cap].is_checked = true;
	// 			}
	//
	// 			var usergroupModalHtml = kanban.templates['app-modal-usergroup'].render({
	// 				caps: caps,
	// 				usergroup: usergroup.record()
	// 			});
	//
	// 			$(usergroupModalHtml).appendTo('#app-modal-usergroups-accordion').find('.panel-title').trigger('click');
	// 		});
	//
	// }; // usergroupAdd
	//
	// this.usergroupSectionToggle = function (el, action) {
	//
	// 	var self = this;
	//
	// 	var $el = $(el);
	// 	var $formGroup = $el.closest('.form-group');
	// 	var $wrapperFormGroup = $el.closest('.wrapper-form-group');
	//
	// 	var cap = $formGroup.attr('data-cap');
	//
	// 	if ( cap == 'admin' ) {
	// 		var $formGroups = $('.form-group', $wrapperFormGroup).not($formGroup);
	//
	// 		if ( action == 'show' ) {
	// 			$formGroups.removeClass('hide-admin');
	// 		}
	//
	// 		if ( action == 'hide' ) {
	// 			$formGroups.addClass('hide-admin');
	// 		}
	// 	} else {
	// 		var $formGroups = $('.form-group[data-cap^="' + cap + '-"]', $wrapperFormGroup);
	//
	// 		if ( action == 'show' ) {
	// 			$formGroups.removeClass('hide-section');
	// 		}
	//
	// 		if ( action == 'hide' ) {
	// 			$formGroups.addClass('hide-section');
	// 		}
	// 	}
	//
	// }; // usergroupSectionToggle
	//
	// this.usergroupSave = function (el) {
	//
	// 	var self = this;
	//
	// 	var $el = $(el);
	// 	var $panel = $el.closest('.panel');
	// 	var usergroupId = $panel.attr('data-usergroup-id');
	//
	// 	// @todo make sure data has actually changed
	//
	// 	var data = {};
	//
	// 	$('[data-name]', $panel).each(function (n) {
	// 		var $input = $(this);
	//
	// 		if ($input.is('[type="radio"]') && !$input.is(':checked')) {
	// 			return true;
	// 		}
	//
	// 		var name = $input.attr('data-name');
	// 		var value = $input.val();
	// 		data[name] = value;
	// 	});
	//
	// 	var capsArr = $('[data-name="capabilities"]:checked', $panel).map(function () {
	// 		return $(this).val();
	// 	}).get();
	//
	// 	data['capabilities'] = capsArr;
	//
	// 	kanban.usergroups[usergroupId].replace(data);
	//
	// }; // modalLaneSave
	//
	// this.usergroupOnfocus = function (el) {
	// 	// console.log('modalLaneOnfocus');
	//
	// 	var self = this;
	//
	// 	var $el = $(el);
	//
	// 	// Save the current value for restoring.
	// 	var value = $el.val();
	// 	$el.data('prevValue', value);
	// }; // modalLaneOnfocus
	//
	// this.usergroupOnblur = function (el) {
	// 	// console.log('modalLaneOnblur');
	// 	var self = this;
	//
	// 	var $el = $(el);
	//
	// 	// Reset panel title
	// 	$el.trigger('keyup');
	//
	// 	self.usergroupSave(el);
	// }; // modalLaneOnblur
	//
	// this.usergroupOnkeyup = function (el) {
	//
	// 	var self = this;
	//
	// 	var $el = $(el);
	// 	var $panel = $el.closest('.panel');
	// 	var $panelTitle = $('.panel-title', $panel);
	//
	// 	var label = $.trim($el.val());
	//
	// 	if (label == '') {
	// 		label = '<span class="text-muted">New user group</span>';
	// 	}
	//
	// 	$panelTitle.html(label);
	//
	// }; // modalLaneOnkeyup
	//
	// this.usergroupOnkeydown = function (el, e) {
	// 	// console.log('modalLaneOnkeydown');
	//
	// 	var self = this;
	//
	// 	var $el = $(el);
	//
	// 	var keyCode = e.keyCode;
	//
	// 	switch (keyCode) {
	// 		case 13: // enter
	//
	// 			// Save it.
	// 			el.blur();
	//
	// 			return false;
	//
	// 			break;
	//
	// 		case 27: // escape
	// 			var prevValue = $el.data('prevValue');
	// 			$el.val(prevValue);
	//
	// 			el.blur();
	//
	// 			break;
	// 	}
	// }; // modalLaneOnkeydown
	//
	//
	// this.usergroupDelete = function (el) {
	// 	var self = this;
	//
	// 	var confirmed = confirm('Are you sure you want to delete this usergroup?');
	//
	// 	if (confirmed) {
	// 		var $el = $(el);
	// 		var $panel = $el.closest('.panel');
	// 		var usergroupId = $panel.attr('data-usergroup-id');
	//
	// 		$panel.slideUp('fast', function () {
	// 			$panel.remove();
	//
	// 			var ajaxDate = {
	// 				type: 'usergroup',
	// 				action: 'delete',
	// 				id: usergroupId
	// 			};
	//
	// 			$.ajax({
	// 				data: ajaxDate
	// 			});
	//
	// 		});
	//
	// 	}
	//
	// }; // usergroupDelete

	// this.settingsUpdate = function () {
	// 	var self = this;
	//
	// 	var $panel = $('#app-modal #modal-tab-pane-settings');
	//
	// 	var data = {};
	//
	// 	$('[data-name]', $panel).each(function (n) {
	// 		var $input =  $(this);
	//
	// 		if ( $input.is('[type="radio"]') && !$input.is(':checked') ) {
	// 			return true;
	// 		}
	//
	// 		var name = $input.attr('data-name');
	// 		var value = $input.val();
	//
	// 		if (($input.is('[type="radio"]') || $input.is('[type="checkbox"]')) && (value == "true" || value == "false")) {
	// 			value = value === "true" ? true : false;
	// 		}
	//
	// 		if ( name.substring(0, 7) == 'options' ) {
	// 			var nameParts = name.split(':');
	//
	// 			if ( nameParts.length != 2 ) {
	// 				return false;
	// 			}
	//
	// 			name = nameParts[1];
	//
	// 			if ( 'undefined' === typeof data.options ) {
	// 				data.options = {};
	// 			}
	//
	// 			// Set field value as part of options.
	// 			data.options[name] = value;
	//
	// 			// Then set value to all of options.
	// 			value = data.options;
	//
	// 			// Now set name at options.
	// 			name = 'options';
	//
	// 		}
	//
	// 		data[name] = value;
	// 	});
	//
	// 	kanban.app.current_user().optionsAppUpdate(data);
	// }; // settingsUpdate



	this.tabChange = function (el) {
		var self = this;

		var $el = $(el);
		var $li = $el.closest('li').addClass('active');
		$('#modal-navbar .nav .active').not($li).removeClass('active');

		var target = $el.attr('data-target');

		var $tabToShow = $('#modal-tab-pane-' + target).addClass('active');
		$('#modal .tab-pane.active').not($tabToShow).removeClass('active');

		$('#modal-header .navbar-collapse').removeClass('in');

		kanban.app.urlParamUpdate('tab', target);
		kanban.app.urlReplace();
	}; // tabChange

	this.close = function () {

		var self = this;

		kanban.app.urlParamRemove('modal');
		kanban.app.urlParamRemove('tab');

		kanban.app.urlReplace();

		$('#modal').modal('hide').empty();

	}; // modalClose

}; // modal

module.exports = App_Modal