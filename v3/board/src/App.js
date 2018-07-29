var Board = require('./Board')
var Lane = require('./Lane')
var Card = require('./Card')
var Field = require('./Field')
// var fieldTypes = require('./Field/index')
var Fieldvalue = require('./Fieldvalue')
var User = require('./User')
var Usergroup = require('./Usergroup')
var Comment = require('./Comment')
var App_Modal = require('./App/Modal')
var functions = require('./functions')
require('at.js')
var Dropzone = require('dropzone')
var MediumEditor = require('medium-editor')
var Notify = require('notifyjs')

Dropzone.autoDiscover = false;

function App(record) {

	var _self = {};
	_self.record = record;
	_self.allowedFields = ['current_board', 'do_live_updates_check'];

	_self.usersAsArray = null;

	_self.options = {
		site_takeover: false
	};

	this.colors = [

		[
			'#000000',
			'#2a2a2a',
			'#555555',
			'#7f7f7f',
			'#aaaaaa',
			'#d4d4d4',
		],
		[
			'#cc0033',
			'#d42a55',
			'#dd5577',
			'#e57f99',
			'#eeaabb',
			'#f6d4dd',
		],
		[
			'#ff6600',
			'#ff7f2a',
			'#ff9955',
			'#ffb27f',
			'#ffccaa',
			'#ffe5d4',
		],
		[
			'#fdc710',
			'#fdd037',
			'#fdd95f',
			'#fee387',
			'#feecaf',
			'#fef5d7',
		],
		[
			'#cccc00',
			'#d4d42a',
			'#dddd55',
			'#e5e57f',
			'#eeeeaa',
			'#f6f6d4',
		],
		[
			'#339933',
			'#55aa55',
			'#77bb77',
			'#99cc99',
			'#bbddbb',
			'#ddeedd',
		],
		[
			'#339999',
			'#55aaaa',
			'#77bbbb',
			'#99cccc',
			'#bbdddd',
			'#ddeeee',
		],
		[
			'#14acde',
			'#3bb9e3',
			'#62c7e9',
			'#89d5ee',
			'#b0e3f4',
			'#d7f1f9',
		],
		[
			'#0066cc',
			'#2a7fd4',
			'#5599dd',
			'#7fb2e5',
			'#aaccee',
			'#d4e5f6',
		],
		[
			'#663399',
			'#7f55aa',
			'#9977bb',
			'#b299cc',
			'#ccbbdd',
			'#e5ddee',
		],
		[
			'#990066',
			'#aa2a7f',
			'#bb5599',
			'#cc7fb2',
			'#ddaacc',
			'#eed4e5',
		],
		[
			'#cc0066',
			'#d42a7f',
			'#dd5599',
			'#e57fb2',
			'#eeaacc',
			'#f6d4e5',
		],
	];

	this.emojis = ['100', '1234', 'bowtie', 'smile', 'laughing', 'blush', 'smiley', 'relaxed', 'smirk', 'heart_eyes', 'kissing_heart', 'kissing_closed_eyes', 'flushed', 'relieved', 'satisfied', 'no_entry_sign', 'underage', 'no_mobile_phones', 'do_not_litter', 'non-potable_water', 'no_bicycles', 'no_pedestrians', 'children_crossing', 'no_entry', 'eight_spoked_asterisk', 'eight_pointed_black_star', 'heart_decoration', 'vs', 'vibration_mode', 'mobile_phone_off', 'chart', 'currency_exchange', 'aries', 'taurus', 'gemini', 'cancer', 'leo', 'virgo', 'libra', 'scorpius', 'sagittarius', 'capricorn', 'aquarius', 'pisces', 'ophiuchus', 'six_pointed_star', 'negative_squared_cross_mark', 'a', 'b', 'ab', 'o2', 'diamond_shape_with_a_dot_inside', 'recycle', 'end', 'on', 'soon', 'clock1', 'clock130', 'clock10', 'clock1030', 'clock11', 'clock1130', 'clock12', 'clock1230', 'clock2', 'clock230', 'clock3', 'clock330', 'clock4', 'clock430', 'clock5', 'clock530', 'clock6', 'clock630', 'clock7', 'clock730', 'clock8', 'clock830', 'clock9', 'clock930', 'heavy_dollar_sign', 'copyright', 'registered', 'tm', 'x', 'heavy_exclamation_mark', 'bangbang', 'interrobang', 'o', 'heavy_multiplication_x', 'heavy_plus_sign', 'heavy_minus_sign', 'heavy_division_sign', 'white_flower', 'heavy_check_mark', 'ballot_box_with_check', 'radio_button', 'link', 'curly_loop', 'wavy_dash', 'part_alternation_mark', 'trident', 'black_square', 'white_check_mark', 'black_square_button', 'white_square_button', 'black_circle', 'white_circle', 'red_circle', 'large_blue_circle', 'large_blue_diamond', 'large_orange_diamond', 'small_blue_diamond', 'small_orange_diamond', 'small_red_triangle', 'small_red_triangle_down', 'shipit', 'grin', 'wink', 'stuck_out_tongue_winking_eye', 'stuck_out_tongue_closed_eyes', 'grinning', 'kissing', 'kissing_smiling_eyes', 'stuck_out_tongue', 'sleeping', 'worried', 'frowning', 'anguished', 'open_mouth', 'grimacing', 'confused', 'hushed', 'expressionless', 'unamused', 'sweat_smile', 'sweat', 'disappointed_relieved', 'weary', 'pensive', 'disappointed', 'confounded', 'fearful', 'cold_sweat', 'persevere', 'cry', 'sob', 'joy', 'astonished', 'scream', 'neckbeard', 'tired_face', 'angry', 'rage', 'triumph', 'sleepy', 'yum', 'mask', 'sunglasses', 'dizzy_face', 'imp', 'smiling_imp', 'neutral_face', 'no_mouth', 'innocent', 'alien', 'yellow_heart', 'blue_heart', 'purple_heart', 'heart', 'green_heart', 'broken_heart', 'heartbeat', 'heartpulse', 'two_hearts', 'revolving_hearts', 'cupid', 'sparkling_heart', 'sparkles', 'star', 'star2', 'dizzy', 'boom', 'collision', 'anger', 'exclamation', 'question', 'grey_exclamation', 'grey_question', 'zzz', 'dash', 'sweat_drops', 'notes', 'musical_note', 'fire', 'hankey', 'poop', 'shit', '+1', 'thumbsup', '-1', 'thumbsdown', 'ok_hand', 'punch', 'facepunch', 'fist', 'v', 'wave', 'hand', 'raised_hand', 'open_hands', 'point_up', 'point_down', 'point_left', 'point_right', 'raised_hands', 'pray', 'point_up_2', 'clap', 'muscle', 'metal', 'fu', 'walking', 'runner', 'running', 'couple', 'family', 'two_men_holding_hands', 'two_women_holding_hands', 'dancer', 'dancers', 'ok_woman', 'no_good', 'information_desk_person', 'raising_hand', 'bride_with_veil', 'person_with_pouting_face', 'person_frowning', 'bow', 'couplekiss', 'couple_with_heart', 'massage', 'haircut', 'nail_care', 'boy', 'girl', 'woman', 'man', 'baby', 'older_woman', 'older_man', 'person_with_blond_hair', 'man_with_gua_pi_mao', 'man_with_turban', 'construction_worker', 'cop', 'angel', 'princess', 'smiley_cat', 'smile_cat', 'heart_eyes_cat', 'kissing_cat', 'smirk_cat', 'scream_cat', 'crying_cat_face', 'joy_cat', 'pouting_cat', 'japanese_ogre', 'japanese_goblin', 'see_no_evil', 'hear_no_evil', 'speak_no_evil', 'guardsman', 'skull', 'feet', 'lips', 'kiss', 'droplet', 'ear', 'eyes', 'nose', 'tongue', 'love_letter', 'bust_in_silhouette', 'busts_in_silhouette', 'speech_balloon', 'thought_balloon', 'feelsgood', 'finnadie', 'goberserk', 'godmode', 'hurtrealbad', 'rage1', 'rage2', 'rage3', 'rage4', 'suspect', 'trollface', 'sunny', 'umbrella', 'cloud', 'snowflake', 'snowman', 'zap', 'cyclone', 'foggy', 'ocean', 'cat', 'dog', 'mouse', 'hamster', 'rabbit', 'wolf', 'frog', 'tiger', 'koala', 'bear', 'pig', 'pig_nose', 'cow', 'boar', 'monkey_face', 'monkey', 'horse', 'racehorse', 'camel', 'sheep', 'elephant', 'panda_face', 'snake', 'bird', 'baby_chick', 'hatched_chick', 'hatching_chick', 'chicken', 'penguin', 'turtle', 'bug', 'honeybee', 'ant', 'beetle', 'snail', 'octopus', 'tropical_fish', 'fish', 'whale', 'whale2', 'dolphin', 'cow2', 'ram', 'rat', 'water_buffalo', 'tiger2', 'rabbit2', 'dragon', 'goat', 'rooster', 'dog2', 'pig2', 'mouse2', 'ox', 'dragon_face', 'blowfish', 'crocodile', 'dromedary_camel', 'leopard', 'cat2', 'poodle', 'paw_prints', 'bouquet', 'cherry_blossom', 'tulip', 'four_leaf_clover', 'rose', 'sunflower', 'hibiscus', 'maple_leaf', 'leaves', 'fallen_leaf', 'herb', 'mushroom', 'cactus', 'palm_tree', 'evergreen_tree', 'deciduous_tree', 'chestnut', 'seedling', 'blossom', 'ear_of_rice', 'shell', 'globe_with_meridians', 'sun_with_face', 'full_moon_with_face', 'new_moon_with_face', 'new_moon', 'waxing_crescent_moon', 'first_quarter_moon', 'waxing_gibbous_moon', 'full_moon', 'waning_gibbous_moon', 'last_quarter_moon', 'waning_crescent_moon', 'last_quarter_moon_with_face', 'first_quarter_moon_with_face', 'moon', 'earth_africa', 'earth_americas', 'earth_asia', 'volcano', 'milky_way', 'partly_sunny', 'octocat', 'squirrel', 'bamboo', 'gift_heart', 'dolls', 'school_satchel', 'mortar_board', 'flags', 'fireworks', 'sparkler', 'wind_chime', 'rice_scene', 'jack_o_lantern', 'ghost', 'santa', 'christmas_tree', 'gift', 'bell', 'no_bell', 'tanabata_tree', 'tada', 'confetti_ball', 'balloon', 'crystal_ball', 'cd', 'dvd', 'floppy_disk', 'camera', 'video_camera', 'movie_camera', 'computer', 'tv', 'iphone', 'phone', 'telephone', 'telephone_receiver', 'pager', 'fax', 'minidisc', 'vhs', 'sound', 'speaker', 'mute', 'loudspeaker', 'mega', 'hourglass', 'hourglass_flowing_sand', 'alarm_clock', 'watch', 'radio', 'satellite', 'loop', 'mag', 'mag_right', 'unlock', 'lock', 'lock_with_ink_pen', 'closed_lock_with_key', 'key', 'bulb', 'flashlight', 'high_brightness', 'low_brightness', 'electric_plug', 'battery', 'calling', 'email', 'mailbox', 'postbox', 'bath', 'bathtub', 'shower', 'toilet', 'wrench', 'nut_and_bolt', 'hammer', 'seat', 'moneybag', 'yen', 'dollar', 'pound', 'euro', 'credit_card', 'money_with_wings', 'e-mail', 'inbox_tray', 'outbox_tray', 'envelope', 'incoming_envelope', 'postal_horn', 'mailbox_closed', 'mailbox_with_mail', 'mailbox_with_no_mail', 'door', 'smoking', 'bomb', 'gun', 'hocho', 'pill', 'syringe', 'page_facing_up', 'page_with_curl', 'bookmark_tabs', 'bar_chart', 'chart_with_upwards_trend', 'chart_with_downwards_trend', 'scroll', 'clipboard', 'calendar', 'date', 'card_index', 'file_folder', 'open_file_folder', 'scissors', 'pushpin', 'paperclip', 'black_nib', 'pencil2', 'straight_ruler', 'triangular_ruler', 'closed_book', 'green_book', 'blue_book', 'orange_book', 'notebook', 'notebook_with_decorative_cover', 'ledger', 'books', 'bookmark', 'name_badge', 'microscope', 'telescope', 'newspaper', 'football', 'basketball', 'soccer', 'baseball', 'tennis', '8ball', 'rugby_football', 'bowling', 'golf', 'mountain_bicyclist', 'bicyclist', 'horse_racing', 'snowboarder', 'swimmer', 'surfer', 'ski', 'spades', 'hearts', 'clubs', 'diamonds', 'gem', 'ring', 'trophy', 'musical_score', 'musical_keyboard', 'violin', 'space_invader', 'video_game', 'black_joker', 'flower_playing_cards', 'game_die', 'dart', 'mahjong', 'clapper', 'memo', 'pencil', 'book', 'art', 'microphone', 'headphones', 'trumpet', 'saxophone', 'guitar', 'shoe', 'sandal', 'high_heel', 'lipstick', 'boot', 'shirt', 'tshirt', 'necktie', 'womans_clothes', 'dress', 'running_shirt_with_sash', 'jeans', 'kimono', 'bikini', 'ribbon', 'tophat', 'crown', 'womans_hat', 'mans_shoe', 'closed_umbrella', 'briefcase', 'handbag', 'pouch', 'purse', 'eyeglasses', 'fishing_pole_and_fish', 'coffee', 'tea', 'sake', 'baby_bottle', 'beer', 'beers', 'cocktail', 'tropical_drink', 'wine_glass', 'fork_and_knife', 'pizza', 'hamburger', 'fries', 'poultry_leg', 'meat_on_bone', 'spaghetti', 'curry', 'fried_shrimp', 'bento', 'sushi', 'fish_cake', 'rice_ball', 'rice_cracker', 'rice', 'ramen', 'stew', 'oden', 'dango', 'egg', 'bread', 'doughnut', 'custard', 'icecream', 'ice_cream', 'shaved_ice', 'birthday', 'cake', 'cookie', 'chocolate_bar', 'candy', 'lollipop', 'honey_pot', 'apple', 'green_apple', 'tangerine', 'lemon', 'cherries', 'grapes', 'watermelon', 'strawberry', 'peach', 'melon', 'banana', 'pear', 'pineapple', 'sweet_potato', 'eggplant', 'tomato', 'corn', 'house', 'house_with_garden', 'school', 'office', 'post_office', 'hospital', 'bank', 'convenience_store', 'love_hotel', 'hotel', 'wedding', 'church', 'department_store', 'european_post_office', 'city_sunrise', 'city_sunset', 'japanese_castle', 'european_castle', 'tent', 'factory', 'tokyo_tower', 'japan', 'mount_fuji', 'sunrise_over_mountains', 'sunrise', 'stars', 'statue_of_liberty', 'bridge_at_night', 'carousel_horse', 'rainbow', 'ferris_wheel', 'fountain', 'roller_coaster', 'ship', 'speedboat', 'boat', 'sailboat', 'rowboat', 'anchor', 'rocket', 'airplane', 'helicopter', 'steam_locomotive', 'tram', 'mountain_railway', 'bike', 'aerial_tramway', 'suspension_railway', 'mountain_cableway', 'tractor', 'blue_car', 'oncoming_automobile', 'car', 'red_car', 'taxi', 'oncoming_taxi', 'articulated_lorry', 'bus', 'oncoming_bus', 'rotating_light', 'police_car', 'oncoming_police_car', 'fire_engine', 'ambulance', 'minibus', 'truck', 'train', 'station', 'train2', 'bullettrain_front', 'bullettrain_side', 'light_rail', 'monorail', 'railway_car', 'trolleybus', 'ticket', 'fuelpump', 'vertical_traffic_light', 'traffic_light', 'warning', 'construction', 'beginner', 'atm', 'slot_machine', 'busstop', 'barber', 'hotsprings', 'checkered_flag', 'crossed_flags', 'izakaya_lantern', 'moyai', 'circus_tent', 'performing_arts', 'round_pushpin', 'triangular_flag_on_post', 'jp', 'kr', 'cn', 'us', 'fr', 'es', 'it', 'ru', 'gb', 'uk', 'de', 'one', 'two', 'three', 'four', 'five', 'six', 'seven', 'eight', 'nine', 'keycap_ten', 'zero', 'hash', 'symbols', 'arrow_backward', 'arrow_down', 'arrow_forward', 'arrow_left', 'capital_abcd', 'abcd', 'abc', 'arrow_lower_left', 'arrow_lower_right', 'arrow_right', 'arrow_up', 'arrow_upper_left', 'arrow_upper_right', 'arrow_double_down', 'arrow_double_up', 'arrow_down_small', 'arrow_heading_down', 'arrow_heading_up', 'leftwards_arrow_with_hook', 'arrow_right_hook', 'left_right_arrow', 'arrow_up_down', 'arrow_up_small', 'arrows_clockwise', 'arrows_counterclockwise', 'rewind', 'fast_forward', 'information_source', 'ok', 'twisted_rightwards_arrows', 'repeat', 'repeat_one', 'new', 'top', 'up', 'cool', 'free', 'ng', 'cinema', 'koko', 'signal_strength', 'u5272', 'u5408', 'u55b6', 'u6307', 'u6708', 'u6709', 'u6e80', 'u7121', 'u7533', 'u7a7a', 'u7981', 'sa', 'restroom', 'mens', 'womens', 'baby_symbol', 'no_smoking', 'parking', 'wheelchair', 'metro', 'baggage_claim', 'accept', 'wc', 'potable_water', 'put_litter_in_its_place', 'secret', 'congratulations', 'm', 'passport_control', 'left_luggage', 'customs', 'ideograph_advantage', 'cl', 'sos', 'id'];

	// _self.options.medium = {
	// 	placeholder: {
	// 		text: '',
	// 		hideOnClick: true
	// 	},
	// 	targetBlank: true,
	// 	autoLink: false,
	// 	disableExtraSpaces: true,
	// 	toolbar: false,
	// 	// {
	// 	// 	buttons: [
	// 	// 		{
	// 	// 			name: 'bold',
	// 	// 			contentDefault: '<b>B</b>'
	// 	// 		}, {
	// 	// 			name: 'italic',
	// 	// 			contentDefault: '<i>I</i>'
	// 	// 		}, {
	// 	// 			name: 'underline',
	// 	// 			contentDefault: '<span style="text-decoration: underline;">U</span>'
	// 	// 		}, {
	// 	// 			name: 'anchor',
	// 	// 			contentDefault: '<i class=" ei ei-link"></i>'
	// 	// 		}, {
	// 	// 			name: 'unorderedlist',
	// 	// 			contentDefault: '<i class=" ei ei-ul"></i>'
	// 	// 		}, {
	// 	// 			name: 'orderedlist',
	// 	// 			contentDefault: '<i class=" ei ei-ol"></i>'
	// 	// 		}
	// 	// 	]
	// 	// },
	// 	anchor: {
	// 		placeholderText: 'Paste or type a link',
	// 	}
	// };

	_self.caps = {
		'admin': {
			'label': 'Admin',
			'description': 'Change anything in the app',
			'is_title': true
		},
		'admin-board-create': {
			'label': 'Create boards',
			'description': 'Create new boards'
		},
		'admin-board-view-all': {
			'label': 'View all boards',
			'description': 'View all boards regardless of board-specific permissions'
		},
		'admin-users': {
			'label': 'Edit users',
			'description': 'Add, manage and remove app-level users'
		}
	};

	_self.timers = {};

	_self.updates = {
		lastCheck: new Date().getTime(),
		interval: 10000
	};

	this.modal = new App_Modal(this);

	this.caps = function () {
		return $.extend(true, {}, _self.caps);
	}; // caps

	this.record = function () {
		return functions.cloneObject(_self.record);
	}; // record

	this.id = function () {
		return functions.cloneNumber(_self.record.id);
	}; // id

	this.allowedFields = function () {
		return functions.cloneArray(_self.allowedFields);
	}; // allowedFields

	this.options = function () {
		return $.extend(true, {}, _self.options, _self.record.options);
	}; // options

	this.timers = function () {
		return _self.timers;
	}; // timers

	this.updates = function () {
		return _self.updates;
	}; // updates

	var urlParts = window.location.href.split('?');
	var urlParams = {};

	if ('undefined' !== typeof urlParts[1]) {
		var hashes = urlParts[1].split('&');
		for (var i = 0; i < hashes.length; i++) {
			hash = hashes[i].split('=');

			if ('undefined' !== typeof hash[1]) {
				hash[1] = hash[1].replace('#', '');
			}

			if (hash[0] == '' || hash[1] == '') {
				continue;
			}

			urlParams[hash[0]] = hash[1];
		}
	}

	_self.url = {
		host: urlParts[0].split('#').shift().replace(/\/$/, ""), // remove # and trailing /
		params: urlParams
	};

	this.url = function () {
		return _self.url;
	}; // url

	this.render = function () {

		var self = this;

		// Add boards to app.
		var boardTabsHtml = '';
		var boardTabsDropdownHtml = '';

		var boardsCount = 0;
		for (var i in self.record().boards) {

			var boardId = self.record().boards[i];

			if ('undefined' === typeof kanban.boards[boardId]) {
				// @todo remove board.
				continue;
			}

			var board = kanban.boards[boardId];

			if ( boardsCount < 5 || boardId == self.current_board_id() ) {
				boardTabsHtml += kanban.templates['header-board-tab'].render({
					board: board.record()
				});
			} else {
				boardTabsDropdownHtml += kanban.templates['header-board-tab'].render({
					board: board.record()
				});
			}

			boardsCount++;
		}

		var headerHtml = kanban.templates['header'].render({
			boardTabsHtml: boardTabsHtml,
			boardTabsDropdownHtml: boardTabsDropdownHtml,
			isUserLoggedIn: self.current_user_id() > 0 ? true : false,
			currentUser: 'undefined' === typeof self.current_user() ? null : self.current_user().record(),
			isAddBoard: self.current_user().hasCap('admin-board-create')
		});

		$('#header').html(headerHtml);

		var footerMenuHtml = kanban.templates['footer-menu'].render({
			isSeeBoardModal: kanban.app.current_user().hasCap('board-users') || kanban.app.current_user().hasCap('admin-board-create') ? true : false,
		});

		var footerHtml = kanban.templates['footer'].render({
			footerMenuHtml: footerMenuHtml
		});

		$('#footer').html(footerHtml);

		if ('undefined' === typeof kanban.boards[kanban.app.current_user().currentBoardId()]) {
			var $placeholder = $('#board-placeholder');
			if ($placeholder.length > 0) {
				var placeholderHtml = kanban.templates['board-placeholder'].render({
					isCreateBoards: kanban.app.current_user().hasCap('admin-board-create'),
					isLoggedIn: kanban.app.current_user_id() > 0 ? true : false,
					isAdmin: kanban.app.current_user().record().capabilities.admin.length > 0
				});

				$placeholder.html(placeholderHtml);
			}
		} else {
			// First board is always loaded.
			kanban.app.current_user().currentBoard().setLanesLoaded();
			kanban.app.current_user().currentBoard().show();
		}


	}; // render


	this.replace = function (data) {
		var self = this;

		// Removed fields that aren't allowed.
		for (var field in data) {
			if (self.allowedFields().indexOf(field) == -1) {
				delete data[field];
			}
		}

		// Update the record.
		_self.record = $.extend(_self.record, data);

		// var ajaxDate = {
		// 	type: 'app',
		// 	action: 'replace',
		// 	id: self.id()
		// };
		//
		// // Only send the data that was updated.
		// ajaxDate = $.extend(data, ajaxDate);

		// $.ajax({
		// 	data: ajaxDate
		// });

	}; // replace

	/**
	 * Process from smallest to largest to manage dependencies.
	 * @param rerender
	 */
	this.processNewData = function (newData, rerender) {

		var self = this;

		if ('undefined' === typeof newData) {
			return false;
		}

		// rerender = rerender === true ? true : false;

		var rerun = false;

		if ('undefined' !== typeof newData.users) {
			for (var userId in newData.users) {
				// console.log('processNewData.users');

				var userRecord = newData.users[userId];

				// If the record exists but is not updated, remove from new data and continue.
				if (('undefined' === typeof userRecord.is_updated || userRecord.is_updated !== true) && 'undefined' !== typeof kanban.users[userId]) {
					delete newData.users[userId];
					continue;
				}

				// If the user was deleted, remove them and kick them out.
				if ('undefined' !== typeof userRecord.is_active && commentRecord.is_active !== true) {

					if ('undefined' !== typeof kanban.users[userId]) {
						delete kanban.users[userId];
					}

					self.browserReload();
					return false;
				}

				// Update the user.
				var user = kanban.users[userId] = new User(userRecord);

				// If the user is updated, offer to refresh the app.
				if ('undefined' !== typeof userRecord.is_updated && userRecord.is_updated === true) {
					self.showUpdatedNotice();
				}

				delete newData.users[userId];
			}

			// Check if any were not added.
			if (Object.size(newData.users) > 0) {
				rerun = true;
			} else {
				// Rebuild users as array.
				kanban.app.getUsers(null, true); // rebuild
			}
		} // newData.users


		if ('undefined' !== typeof newData.comments) {
			for (var commentId in newData.comments) {
				// console.log('processNewData.comments');

				// Get the new record.
				var commentRecord = newData.comments[commentId];

				// Determine if the record is new (already exists).
				var isNew = false;
				if ('undefined' === typeof kanban.comments[commentId]) {
					isNew = true;
				}

				// Companion records are included, just in case. If the record is not updated and already exists, remove from new data and continue.
				if (('undefined' === typeof commentRecord.is_updated || commentRecord.is_updated !== true) && 'undefined' !== typeof kanban.comments[commentId]) {
					delete newData.comments[commentId];
					continue;
				}

				// Update the comment.
				var comment = kanban.comments[commentId] = new Comment(commentRecord);

				// If the record was deleted, remove it.
				if ('undefined' !== typeof commentRecord.is_active && commentRecord.is_active !== true) {
					var success = comment.remove();
					if (success !== false) {
						delete newData.comments[commentId];
					}
					continue;
				}

				// If the record is new, add it to the card.
				if (isNew) {
					var card = kanban.cards[comment.card_id()];

					if ('undefined' !== typeof comment.comment_type && comment.comment_type == 'user') {
						card.commentUpdateCount(+1);
					}
				}

				// if the card modal isn't open, we don't need to do anything with the dom.
				if ($('#card-modal').length == 0) {
					delete newData.comments[commentId];
					continue;
				}

				// If the record is new, add it to the dom.
				if (isNew) {
					var success = card.modal.commentRerenderAll();
					if (success !== false) {
						delete newData.comments[commentId];
					}
				}
				// If the record is updated, rerender the dom element.
				else {
					var success = comment.rerender();
					if (success !== false) {
						delete newData.comments[commentId];
					}
				}
			}

			// Check if any were not added.
			if (Object.size(newData.comments) > 0) {
				rerun = true;
			}
		} // newData.comments


		if ('undefined' !== typeof newData.fieldvalues) {
			for (var fieldvalueId in newData.fieldvalues) {
				// console.log('processNewData.fieldvalue');

				var fieldvalueRecord = newData.fieldvalues[fieldvalueId];

				// Companion records are included, just in case. If the record is not updated and already exists, remove from new data and continue.
				if (('undefined' === typeof fieldvalueRecord.is_updated || fieldvalueRecord.is_updated !== true) && 'undefined' !== typeof kanban.fieldvalues[fieldvalueId]) {
					delete newData.fieldvalues[fieldvalueId];
					continue;
				}

				// NOTE: Fieldvalues are never deleted, so skipping delete functionality.

				// Update the fieldvalue.
				var fieldvalue = kanban.fieldvalues[fieldvalueId] = new Fieldvalue(fieldvalueRecord);

				// If field doesn't exist yet, no point in rerendering it.
				if ( 'undefined' === typeof kanban.fields[fieldvalue.fieldId()] ) {
					delete newData.fieldvalues[fieldvalueId];
					continue;
				}

				var success = fieldvalue.rerenderField();
				if (success !== false) {
					delete newData.fieldvalues[fieldvalueId];
				}
			}

			// Check if any were not added.
			if (Object.size(newData.fieldvalues) > 0) {
				rerun = true;
			}
		} // newData.fieldvalues


		if ('undefined' !== typeof newData.fields) {
			for (var fieldId in newData.fields) {
				// console.log('processNewData.fields');
				var fieldRecord = newData.fields[fieldId];

				// Companion records are included, just in case. If the record is not updated and already exists, remove from new data and continue.
				if (('undefined' === typeof fieldRecord.is_updated || fieldRecord.is_updated !== true) && 'undefined' !== typeof kanban.fields[fieldId]) {
					delete newData.fields[fieldId];
					continue;
				}

				// See if field type has it's own class.
				var fieldClass = 'Field';

				if ('undefined' !== typeof fieldRecord.field_type && fieldRecord.field_type !== null) {
					// Build a "class" name to search for e.g. Field_Tags
					var fieldType = 'Field_' + fieldRecord.field_type.charAt(0).toUpperCase() + fieldRecord.field_type.slice(1);

					// See if "class" exists.
					if (typeof kanban.fieldTypes[fieldType] === 'function') {
						fieldClass = fieldType;
					}
				}

				// Create field using "class" based on type.
				var field = kanban.fields[fieldId] = new kanban.fieldTypes[fieldClass](fieldRecord);

				// If the record was deleted, remove it.
				if ('undefined' !== typeof fieldRecord.is_active && fieldRecord.is_active !== true) {

					// Don't delete it, or there will be errors if they try to update it.
					// delete kanban.fields[fieldId];
				}

				// If the record is updated, do action.
				if ('undefined' !== typeof fieldRecord.is_updated && fieldRecord.is_updated === true) {
					self.showUpdatedNotice();
				}

				delete newData.fields[fieldId];
			}
		} // fields

		if ('undefined' !== typeof newData.cards) {
			for (var cardId in newData.cards) {
				// console.log('processNewData.cards');
				var cardRecord = newData.cards[cardId];

				// Determine if the record is new (already exists).
				var isNew = false;
				if ('undefined' === typeof kanban.cards[cardId]) {
					isNew = true;
				}

				// Companion records are included, just in case. If the record is not updated and already exists, remove from new data and continue.
				if (('undefined' === typeof cardRecord.is_updated || cardRecord.is_updated !== true) && 'undefined' !== typeof kanban.cards[cardId]) {
					delete newData.cards[cardId];
					continue;
				}

				// Update the card.
				var card = kanban.cards[cardId] = new Card(cardRecord);

				// If the record was deleted, remove it.
				if ('undefined' !== typeof cardRecord.is_active && cardRecord.is_active !== true) {

					// Remove the card from the UI.
					var success = card.remove();

					if (success !== false) {
						delete newData.cards[cardId];
					}

					continue;
				}

				if (isNew) {

					// Do nothing, because it will be added by the updated lane records (cards_order).
					delete newData.cards[cardId];
					continue;
				}

				// If not new, rerender it.
				// If the record is updated, do action.
				if ('undefined' !== typeof cardRecord.is_updated && cardRecord.is_updated === true) {
					var success = card.rerender();
				}

				if (success !== false) {
					delete newData.cards[cardId];
				}
			}

			// Check if any were not added.
			if (Object.size(newData.cards) > 0) {
				rerun = true;
			}

		} // cards


		if ('undefined' !== typeof newData.lanes) {
			for (var laneId in newData.lanes) {
				// console.log('processNewData.lanes');

				var laneRecord = newData.lanes[laneId];

				// Determine if the record is new (already exists).
				var isNew = false;
				if ('undefined' === typeof kanban.lanes[laneId]) {
					isNew = true;
				}

				// Companion records are included, just in case. If the record is not updated and already exists, remove from new data and continue.
				if (('undefined' === typeof laneRecord.is_updated || laneRecord.is_updated !== true) && 'undefined' !== typeof kanban.lanes[laneId]) {
					delete newData.lanes[laneId];
					continue;
				}

				// Update the lane.
				var lane = kanban.lanes[laneId] = new Lane(laneRecord);

				// If the record was deleted, remove it.
				if ('undefined' !== typeof laneRecord.is_active && laneRecord.is_active !== true) {

					// Don't delete it, or there will be errors if they try to update it.
					// delete kanban.lanes[laneId];

					// Tell them to refresh.
					self.showUpdatedNotice();
					delete newData.lanes[laneId];
					continue;
				}

				if (isNew) {
					// Tell them to refresh.
					// If the record is updated, do action. If not, it's a companion to board, which will render it below.
					if ('undefined' !== typeof laneRecord.is_updated && laneRecord.is_updated === true) {
						self.showUpdatedNotice();
					}
					delete newData.lanes[laneId];
					continue;
				}

				// Lastly, if it's just updated, rerender it.
				// If the record is updated, do action.
				if ('undefined' !== typeof laneRecord.is_updated && laneRecord.is_updated === true) {
					var success = lane.rerender();
				}

				if (success !== false) {
					delete newData.lanes[laneId];
				}
			}

			// Check if any were not added.
			if (Object.size(newData.lanes) > 0) {
				rerun = true;
			}
		} // lanes

		if ('undefined' !== typeof newData.boards) {
			for (var boardId in newData.boards) {
				// console.log('processNewData.boards');
				var boardRecord = newData.boards[boardId];

				// Determine if the record is new (already exists).
				var isNew = false;
				if ('undefined' === typeof kanban.boards[boardId]) {
					isNew = true;
				}

				// Companion records are included, just in case. If the record is not updated and already exists, remove from new data and continue.
				if (('undefined' === typeof boardRecord.is_updated || boardRecord.is_updated !== true) && 'undefined' !== typeof kanban.boards[boardId]) {
					delete newData.boards[boardId];
					continue;
				}

				// Create board using "class" based on type.
				var board = kanban.boards[boardId] = new Board(boardRecord);

				// If the record was deleted, remove it.
				if ('undefined' !== typeof boardRecord.is_active && boardRecord.is_active !== true) {

					// Don't delete it, or there will be errors if they try to update it.
					// delete kanban.boards[boardId];

					// Tell them to refresh.
					self.showUpdatedNotice();
					delete newData.boards[boardId];
					continue;
				}

				if (isNew) {
					// Tell them to refresh.
					// If the record is updated, do action.
					if ('undefined' !== typeof boardRecord.is_updated && boardRecord.is_updated === true) {
						self.showUpdatedNotice();
					}

					delete newData.boards[boardId];
					continue;
				}

				// Lastly, if it's just updated, rerender it.
				// If the record is updated, do action.
				if ('undefined' !== typeof boardRecord.is_updated && boardRecord.is_updated === true) {
					var success = board.rerender();
				}

				if (success !== false) {
					delete newData.boards[boardId];
				}
			}

			// Check if any were not added.
			if (Object.size(newData.boards) > 0) {
				rerun = true;
			}
		} // boards

		// If there's any data left, schedule it to run again.
		if (rerun === true) {
			setTimeout(function () {
				// console.log('setTimeout', newData);
				self.processNewData(newData, rerender);
			}, 5000);
		}

	}; // processNewData

	this.showUpdatedNotice = function () {
		var html = kanban.templates['app-update-notice'].render();
		$(html).appendTo('#footer');
	}; // showUpdatedNotice

	this.showOfflineNotice = function () {
		if ($('#app-offline-notice').length == 0) {
			var html = kanban.templates['app-offline-notice'].render();
			$(html).appendTo('#footer');
		}
	}; // showOfflineNotice

	this.hideOfflineNotice = function () {
		if ($('#app-offline-notice').length > 0) {
			$('#app-offline-notice').remove();
		}
	}; // hideOfflineNotice

	this.browserReload = function () {
		location.reload();
	}; // browserReload

	this.current_user_id = function () {
		var self = this;

		return _self.record.current_user;
	}; // current_user_id

	this.current_user = function () {
		var self = this;

		return kanban.users[_self.record.current_user];
	}; // current_user_id

	this.current_board_id = function () {
		var self = this;

		return self.current_user().currentBoardId();
	}; // current_board

	this.current_board = function () {
		var self = this;

		return self.current_user().currentBoard();
	}; // current_board

	this.currentBoardModalShow = function (el) {
		if (null === kanban.app.current_board()) {
			$(el).hide();
			return false;
		}

		kanban.app.current_board().modal.show();
	}; // currentBoardModalShow

	this.filterModalToggle = function (el) {

		var self = this;

		if (null === kanban.app.current_board()) {
			$(el).hide();
			return false;
		}

		self.current_board().toggleFilterModal();

	}; // filterModalToggle

	this.applyFilters = function () {

		//helper function
		var stringToFloat = function (s) {
			if ( isNaN(s) || s == "" ) {
				s = 0;
			}
	
			return parseFloat(s);
		}; // stringToFloat

		var self = this;

		if (null === kanban.app.current_board()) {
			self.modal.close();
			return false;
		}

		var filters = [];
		$('#modal .field-filter').not('.field-filter-time, .field-filter-colorpicker').each(function(){
			var fieldId = $(this).attr('data-id');
			var operator = $(this).find('select').find(':selected').val();

			var $valueInput = $(this).find('input');
			if ($valueInput.hasClass('date-filter-value')) {
				var value = $valueInput.datepicker('getUTCDate');	
			}
			else if ($valueInput.hasClass('users-filter-value')) {
				var value =  $valueInput.get(0).selectize.getValue();
			} else {
				var value = $valueInput.val();
			}			

			filters.push({
				fieldId, operator, value
			})
		})

		//add time filters - handled separately than other filter fields because it contains two inputs
		$('#modal .field-filter-time').each(function(){
			var fieldId = $(this).attr('data-id');
			var operator = $(this).find('select').find(':selected').val();
			var $field = $(this);

			var $hours = $('.form-control-hours', $field);
			var $estimate = $('.form-control-estimate', $field);

			var hours = $hours.val();
			hours = stringToFloat(hours);

			var estimate = 0;
			if ( $estimate.length == 1 ) {
				var estimate = $estimate.val();
				estimate = stringToFloat(estimate);
			}

			var returnValue = {
				hours,
				estimate
			}
			if ($hours.val() == "" && $estimate.val() == "") {
				returnValue = ""
			}

			filters.push({
				fieldId, 
				operator, 
				value: returnValue
			});			
		})

		//add colorpicker filters
		$('#modal .field-filter-colorpicker').each(function(){
			var fieldId = $(this).attr('data-id');
			var operator = $(this).find('select').find(':selected').val();
			var $field = $(this);

			var returnValue = "";
			if ($field.find('button.btn-color').attr('data-color')) {
				returnValue = $field.find('button.btn-color').attr('data-color');
			}

			filters.push({
				fieldId, 
				operator, 
				value: returnValue
			});	
		});
		

		self.current_board().applyFilters(filters);

		self.modal.close();

	}; // applyFilters

	this.clearFilters = function() {
		kanban.app.current_board().showAllCards();
		
		$('#modal').modal('hide').empty();
	}

	this.viewToggleCompact = function (el) {

		if (null === kanban.app.current_board()) {
			return false;
		}

		kanban.app.current_board().viewToggleCompact(el);

		//apply the search if search param exists in url
		var urlSearchParam = kanban.app.urlParamGet('search');
		if (urlSearchParam) {
			$('#footer input[type=search]').val(urlSearchParam);
			kanban.app.searchCurrentBoard($('#footer input[type=search]'));
		}
	}; // viewToggleCompact

	this.viewToggleAllLanes = function (el) {

		if (null === kanban.app.current_board()) {
			return false;
		}

		kanban.app.current_board().viewToggleAllLanes(el);

		//apply the search if search param exists in url
		var urlSearchParam = kanban.app.urlParamGet('search');
		if (urlSearchParam) {
			$('#footer input[type=search]').val(urlSearchParam);
			kanban.app.searchCurrentBoard($('#footer input[type=search]'));
		}
	}; // viewToggleAllLanes

	this.viewToggleFullScreen = function (el) {

		var $el = $('#footer-menu-board-view-full-screen');

		var full_screen_element = document.fullscreenElement || document.webkitFullscreenElement || document.mozFullScreenElement || document.msFullscreenElement || null;

		var element = document.documentElement;

		// If not full screen.
		if (full_screen_element === null) {

			$el.addClass('active');

			// Go full screen.
			if (element.requestFullscreen) {
				element.requestFullscreen();
			}
			else if (element.mozRequestFullScreen) {
				element.mozRequestFullScreen();
			}
			else if (element.webkitRequestFullscreen) {
				element.webkitRequestFullscreen();
			}
			else if (element.msRequestFullscreen) {
				element.msRequestFullscreen();
			}
		}
		else {

			$el.removeClass('active');

			// If full screen, exit.
			if (document.exitFullscreen) {
				document.exitFullscreen();
			}
			else if (document.mozCancelFullScreen) {
				document.mozCancelFullScreen();
			}
			else if (document.webkitExitFullscreen) {
				document.webkitExitFullscreen();
			}
			else if (document.msExitFullscreen) {
				document.msExitFullscreen();
			}
		}

	}; // viewToggleFullScreen

	this.optionUpdate = function (option, value) {

		var self = this;

		if (!kanban.app.current_user().hasCap('admin')) {
			return false;
		}

		_self.record.options[option] = value;

		if (Array.isArray(value) && value.length == 0) {
			value = '';
		}

		$.ajax({
			data: {
				type: 'app_option',
				action: 'replace',
				option: option,
				value: value
			}
		});

	}; // optionUpdate


	this.toggleLane = function (el) {
		var $btn = $(el);
		var direction = $btn.attr('data-direction');

		// Get the current board, and lanes.
		var $board = $('#board-' + kanban.app.current_board_id());
		var $lanes = $('.lane', $board);

		// Get the active lane.
		var $lane = $('.lane.active', $board);

		// If no lane is found, than make the first one active, and stop.
		if ($lane.length == 0) {
			$lane = $('.lane:first', $board).addClass('active');
			return false;
		}

		// Get the index of the current board.
		var index = $lanes.index($lane);

		// Increment the index.
		index = direction == 'left' ? index - 1 : index + 1;

		// If the index is out of bounds, move it.
		if (index < 0 || index >= $lanes.length) {
			index = direction == 'left' ? $lanes.length - 1 : 0;
		}

		$lane.removeClass('active');
		$lanes.eq(index).addClass('active');

		return false;
	}; // toggleLane

	// this.resizeLaneHeights = function () {
	// @link https://stackoverflow.com/a/1147768/38241
	// var body = document.body,
	// 	html = document.documentElement;
	//
	// var height = Math.max( body.scrollHeight, body.offsetHeight,
	// 	html.clientHeight, html.scrollHeight, html.offsetHeight );
	// height = height - (15*20);
	// $('.wrapper-cards').css('min-height', height + 'px');

	// }; // resizeLaneHeights

	this.addBoard = function (el) {
		var self = this;

		if (!kanban.app.current_user().hasCap('admin-board-create')) {
			return false;
		}

		var ajaxDate = {
			type: 'board',
			action: 'add'
		};

		$.ajax({
			data: ajaxDate
		})
		.done(function (response) {

			if ( 'undefined' === typeof response.data ||'undefined' === typeof response.data.id ) {
				kanban.app.notify(kanban.strings.board.added_error);
				return false;
			}

			// Add boards to app.
			var boardId = response.data.id;
			var boardRecord = response.data;
			var board = kanban.boards[boardId] = new Board(boardRecord);

			var headerTabsHtml = kanban.templates['header-board-tab'].render({
				board: board.record()
			});

			$(headerTabsHtml).appendTo('#header-board-tabs');

			board.show();

			// Give it time to show, and update user.
			setTimeout(function () {
				// Show the modal for editing.
				kanban.boards[boardId].modal.show();
			}, 500);

		});

	}; // addBoard

	this.addGrowl = function (message, type) {

		if ('undefined' === typeof message || message == '') {
			return false;
		}

		var alerts = ['success', 'info', 'warning', 'danger'];

		var index = alerts.indexOf(type);
		if (index == -1) {
			type = 'info';
		}

		var html = kanban.templates['app-growl'].render({
			type: type,
			message: message
		});

		var $html = $(html).appendTo('#app-alert');

		var wordCount = message.split(" ").length;

		wordCount = wordCount < 3 ? 3 : wordCount;

		setTimeout(function () {
			$html.slideUp('fast', function () {
				$html.remove();
			});

		}, wordCount * 500);
	}; // addGrowl

	this.notifyDo = function (message) {

		new Notify(
			kanban.strings.notify.title,
			{
				body: message,
				icon: kanban.strings.notify.icon,
				tag: new Date().valueOf() + (Math.floor((1 + Math.random()) * 0x10000))
				// notifyShow: onNotifyShow
			}
		).show();
	}; // notifyDo

	this.notifyOnPermissionGranted = function(message, type) {
		// console.log('Permission has been granted by the user');
		var self = this;
		self.notifyDo(message);
	}; // notifyOnPermissionGranted

	this.notifyOnPermissionDenied = function(message, type) {
		// console.warn('Permission has been denied by the user');
		var self = this;
		self.addGrowl(message, type);
	}; // notifyOnPermissionDenied


	this.notify = function (message, type) {
		var self = this;

		if ('undefined' === typeof message || '' === message || null === message || false == message) {
			return false;
		}

		if (!Notify.needsPermission) {
			return self.notifyDo(message);
		} else if (Notify.isSupported()) {
			return Notify.requestPermission(
				self.notifyOnPermissionGranted.bind(self, message, type),
				self.notifyOnPermissionDenied.bind(self, message, type)
			);
		}

		self.addGrowl(message, type);

	}; // notify

	this.growl_response_message = function (response) {
		var self = this;

		try {
			self.notify(response.data.message);
		}
		catch (err) {
		}
	}; // growl_response_message

	this.getUsers = function (format, rebuild) {

		if (_self.usersAsArray == null || rebuild === true) {
			_self.usersAsArray = [];
			for (var userId in kanban.users) {
				var user = kanban.users[userId];

				_self.usersAsArray.push(user.record());
			}
		}

		if (format == 'array') {
			return _self.usersAsArray;
		}

		return kanban.users;
	};

	this.prepareContenteditable = function ($field) {
		var $formControl = $('.form-control[contenteditable="true"]', $field);

		var editor = new MediumEditor($formControl, {
			targetBlank: true,
			autoLink: false,
			imageDragging: false,
			// disableExtraSpaces: true,
			// disableDoubleReturn: true,
			placeholder: {
				text: $formControl.attr('data-placeholder'),
				hideOnClick: false
			},
			paste: {
				forcePlainText: true,
				cleanPastedHTML: true,
			},
			toolbar: false
			// buttons: ['bold', 'italic', 'anchor', 'h2', 'h3', 'quote']
		});

		var users = kanban.app.current_board().usersListMention('array');

		$formControl
		.atwho({
			at: "@",
			data: users,
			searchKey: "display_name",
			displayTpl: '<li>${display_name} (${user_email})</li>',
			insertTpl: '<b class="mention" contenteditable="false" data-mention="${id}" data-id="${id}">${atwho-at}${display_name}</b>',
		})
		.atwho({
			at: ":",
			data: kanban.app.emojis,
			displayTpl: '<li><img src="//a248.e.akamai.net/assets.github.com/images/icons/emoji/${name}.png" style="height: 1em;"> ${name} </li>',
			insertTpl: '<img src="//a248.e.akamai.net/assets.github.com/images/icons/emoji/${name}.png" data-emoji="${name}" class="emoji">'
		});

		var $dropzone = $('.dropzone:first', $field);

		// Dropzone.autoDiscover = false;

		if ($dropzone.length > 0) {

			var myDropzone = new Dropzone(
				$dropzone.get(0),
				{
					createImageThumbnails: true,
					thumbnailWidth: 15,
					thumbnailHeight: 15,
					thumbnailMethod: 'crop',
					parallelUploads: 1,
					paramName: 'kanban-file',
					url: kanban.ajax.url(),
					params: {
						type: 'comment',
						action: 'upload',
						'kanban_nonce': kanban.ajax.nonce(),
						card_id: $formControl.attr('data-card-id')
					}
				}
			);

			myDropzone.on('dragenter', function (e) {
				var $target = $(e.target);

				// var h = $target.outerHeight();
				// $target.css({
				// 	minHeight: h
				// });

				// $('.wrapper-contenteditable', $target).css({
				// 	display: 'none'
				// });
			}); // dragstart

			myDropzone.on('dragleave', function (e) {
				var $target = $(e.target);

				// $target.css({
				// 	minHeight: '0'
				// });

				// $('.wrapper-contenteditable', $target).css({
				// 	display: 'block'
				// });
			}); // dragleave

			myDropzone.on('drop', function (e) {
				var $target = $(e.target);

				// $target.css({
				// 	minHeight: '0'
				// });
				//
				// $('.wrapper-contenteditable', $target).css({
				// 	display: 'block'
				// });
			}); // drop

			myDropzone.on('success', function (file, response) {
				// console.log(file, );
				// console.log(response);

				var type = file.type.slice(0, file.type.indexOf('/'));
				var fileName = file.name;

				switch (type) {
					case 'image' :

						$(' <p><img class="attachment attachment-img" data-href="' + response.data.url + '" src="' + response.data.url + '"></p>').appendTo($formControl);

						break;

					default :

						$(' <p><input type="button" class="attachment attachment-file" data-href="' + response.data.url + '" value="' + fileName + '"></p>').appendTo($formControl);

						break;
				}

				$('<p>&nbsp;</p>').appendTo($formControl);

				// Should trigger saving most places.
				$formControl.trigger('blur');

				$(file.previewElement).slideUp(function () {
					$(this).remove();
				});
			}); // success

		} // $dropzone

	}; // prepareContenteditable

	this.capsForUser = function (user) {
		var self = this;

		var userRecord = user.record();

		// Copy caps (Use jQuery for deep copy)
		var caps = kanban.app.caps();

		var isAdmin = false;
		var titleAdmin = false;

		for (var capName in caps) {
			var cap = caps[capName];

			var isChecked = userRecord.capabilities.admin.indexOf(capName);
			cap.is_checked = isChecked === -1 ? false : true;
			cap.is_self_admin = false;

			cap.classes = '';

			if (capName == 'admin' && cap.is_checked) {
				isAdmin = true;

				if (userRecord.id == kanban.app.current_user_id()) {
					cap.is_self_admin = true;
				}

				continue; // Don't apply the rest of the logic to the admin cap.
			}

			if (isAdmin) {
				cap.classes += ' hide-admin ';
			}

			if (cap.is_title) {
				titleAdmin = cap.is_checked ? true : false;
			}

			if (!cap.is_title && titleAdmin) {
				cap.classes += ' hide-section ';
			}
		}

		return caps;
	}; // capsForUser

	this.urlParamsUpdate = function (paramsObj) {
		_self.url.params = paramsObj;
	}

	this.urlParamUpdate = function (key, value) {
		_self.url.params[key] = value;
	}

	this.urlParamRemove = function (key) {
		delete _self.url.params[key];
	}

	this.urlParamGet = function (key) {
		return _self.url.params[key];
	}

	this.urlBuild = function (includeParams) {

		// If not explicitly set to false, assume true.
		if (includeParams !== false) {
			includeParams = true;
		}

		var url = _self.url.host;
		if (includeParams) {
			url += '?' + decodeURIComponent($.param(_self.url.params));
		}
		return url;
	}

	this.urlReplace = function () {

		// Prevent error in IE9.
		if ("undefined" === typeof history.pushState) {
			return;
		}
		var self = this;

		window.history.replaceState('', '', self.urlBuild());

		self.updatePageTitle();
	}

	this.urlClear = function () {
		var self = this;

		self.urlParamsUpdate({});
		self.urlReplace();
	}


	this.updatePageTitle = function () {
		// if ( typeof kanban.url_params.board_id !== 'undefined' ) {
		// 	var board = boards[kanban.url_params.board_id];
		//
		// 	if ( typeof board === 'undefined' ) {
		// 		return false;
		// 	}
		//
		// 	document.title = '{0} | {1}'.sprintf( board.record.title(), kanban.text.kanban );
		// }

	}; // updatePageTitle


	// $(document).trigger('/app/init/');

	this.updatesInit = function () {

		var self = this;

		var options = kanban.app.current_user().optionsApp();

		if (options.do_live_updates_check) {
			_self.timers.updates = setInterval(
				function () {
					self.updatesCheck();
				},
				options.live_updates_check_interval * 1000
			);
		}

	}; // updatesInit

	this.updatesCheck = function () {

		var self = this;

		if (!navigator.onLine) {

			self.showOfflineNotice();
			return false;
		}

		self.hideOfflineNotice();

		var ajaxDate = {
			type: 'app',
			action: 'updates_check',
			datetime: self.updates().lastCheck
		};

		// Store the next time we checked.
		var nextCheck = new Date().getTime();

		$.ajax({
			data: ajaxDate
		})
		.done(function (response) {

			// kanban.new_data = $.extend(true, kanban.new_data, response.data);

			kanban.app.processNewData(response.data, true);

			// Since it's a successful request, update the last time we checked.
			self.updates().lastCheck = nextCheck;

		});
	}; // updatesInit

	this.searchCurrentBoard = function (el) {
		var self = this;
		var $searchField = $(el);

		//reset timer
		clearTimeout(_self.timers.searchTimer);

		//start new timer
		_self.timers.searchTimer = setTimeout(function () {
			var val = $searchField.val().toLowerCase();

			if ('' != val) {
				var showCards = [];

				//check all the fieldvalues from all the cards of the current board's lanes for possible match
				var currentBoard = kanban.boards[self.current_board_id()];
				var currentLanes = currentBoard.lanesOrder();
				for(var i = 0; i < currentLanes.length; i++) {
					var laneId = currentLanes[i];

					// Make sure there's a corresponding lane record.
					if ('undefined' === typeof kanban.lanes[laneId]) {						
						continue;
					}
		
					var lane = kanban.lanes[laneId];
					var laneCards = lane.cardsOrder();
					for (var j = 0; j < laneCards.length; j++) {
						var cardId = laneCards[j];

						// Make sure there's a corresponding card record.
						if ( 'undefined' === typeof kanban.cards[cardId] ) {
							continue;
						}
						
						var card = kanban.cards[cardId];

						var fieldValues = card.fieldvalues();
						var fieldvaluesByField = card.fieldvaluesByField();
						for(var k = 0; k < fieldValues.length; k++) {							
							var fieldvalue = kanban.fieldvalues[fieldValues[k]];
							if ('undefined' === typeof kanban.fields[fieldvalue.fieldId()]) {
								continue;
							}
							var fieldContent = fieldvalue.field().formatContentForComment(fieldvalue.content());
							if (fieldContent.toLowerCase().indexOf(val) !== -1) {
								showCards.push(card.id());
								break;
							}
						}
					}
				}

				currentBoard.showSelectedCardsOnly(showCards);
			} else {
				kanban.app.current_board().showAllCards();
			}
		}, 1000);

		// Update the url.		
		if ($searchField.val()) {
			kanban.app.urlParamUpdate('search', $searchField.val());
		} else {
			kanban.app.urlParamRemove('search');
		}
		kanban.app.urlReplace();
	}

	this.toggleKeyboardShortcutsModal = function () {
		if ( $('#keyboard-shortcuts-modal').length > 0 ) {
			kanban.app.modal.close();
		} else {
			var html = kanban.templates['app-modal-keyboard-shortcuts'].render();

			$('#modal').html(html);

			$('#modal').modal({
				backdrop: 'static',
				keyboard: false,
				show: true
			});
		}
	}; // toggleKeyboardShortcutsModal

	this.presetsToggleModal = function (el) {

		var self = this;

		var $el = $(el);
		var className = $el.attr('data-class');
		var add = $el.attr('data-add');

		if ( 'undefined' === typeof add || '' == add ) {
			add = 'board, lanes and fields';
		}

		if ( !self.current_user().hasCap('admin-board-create') ) {
			return false;
		}

		var html = kanban.templates['presets-modal'].render({
			add: add
		});

		$('#modal').html(html);

		$('#modal').modal({
			backdrop: 'static',
			keyboard: false,
			show: true
		});

		$.ajax({
			data: {
				type: 'board_preset',
				action: 'get_presets_data'
			}
		})
		.done(function (response) {

			var presetsHtml = '';
			for (var i in response.data) {

				var preset = response.data[i];
				presetsHtml += kanban.templates['presets-modal-preset'].render({
					preset: preset,
					add: add
				});
			}

			$('#presets-modal-accordion').html(presetsHtml);
		}); // done

	}; // presetsToggleModal

	this.presetAdd = function (el) {
		var self = this;

		if ( !self.current_user().hasCap('admin-board-create') ) {
			return false;
		}

		var $el = $(el);
		var className = $el.attr('data-class');
		var add = $el.attr('data-add');

		$.ajax({
			data: {
				type: className,
				action: 'add',
				board_id: 'undefined' !== typeof self.current_board_id() ? self.current_board_id() : '',
				add: add
			}
		})
		.done(function (response) {

			if ( 'undefined' === typeof response.data ||'undefined' === typeof response.data.id ) {
				kanban.app.notify(kanban.strings.preset.added_error);
				return false;
			}

			// Add boards to app.
			var boardId = response.data.id;

			var boardExists = 'undefined' === typeof kanban.boards[boardId] ? false : true;

			var boardRecord = response.data;
			var board = kanban.boards[boardId] = new Board(boardRecord);

			if ( !boardExists ) {
				var headerTabsHtml = kanban.templates['header-board-tab'].render({
					board: board.record()
				});

				$(headerTabsHtml).appendTo('#header-board-tabs');
			}

			board.show();

			kanban.app.modal.close();
		});

	}; // presetAdd

	this.getColorPicker = function () {
		var self = this;

		if ( $('#app-color-picker').length == 0 ) {
			var $cp = $('<div id="app-color-picker" class="row" width="234" height="477"></div>').appendTo('body');

			for (var i in self.colors ) {
				var row = self.colors[i];

				var $row = $('<div class="row"></div>').appendTo($cp);

				for (var j in row ) {

					if ( typeof row[j] !== 'string' ) {
						continue;
					}

					$('<div class="col col-sm-2"><b data-color="' + row[j] + '" style="background: ' + row[j] + ';"></b></div>').appendTo($row);
				}
			}
		}

		return $('#app-color-picker');
	}; // getColorPicker

	// this.colorPickerOnclick = function (e) {
	// 	var ctx = e.target.getContext('2d');
	// 	var canvasOffset = $(e.target).offset();
	// 	var canvasX = Math.floor(e.pageX - canvasOffset.left);
	// 	var canvasY = Math.floor(e.pageY - canvasOffset.top);
	//
	// 	var imageData = ctx.getImageData(canvasX, canvasY, 1, 1);
	// 	var pixel = imageData.data;
	// 	var dColor = pixel[2] + 256 * pixel[1] + 65536 * pixel[0];
	// 	if ( dColor == 0 ) {
	// 		dColor = '000000';
	// 	}
	// 	return '#' + dColor.toString(16);
	//
	// }; // colorPickerOnclick
} // App


module.exports = App