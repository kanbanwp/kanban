<link rel="stylesheet" href="<?php echo Kanban::get_instance()->settings->uri ?>/css/admin.css">



<div class="wrap">
	<h1>
		<?php echo sprintf( __( 'About %s', 'kanban' ), Kanban::get_instance()->settings->pretty_name ); ?>
	</h1>



	<?php if ( isset($_GET['activation']) ) : ?>
		<div class="updated notice is-dismissible kanban-welcome-notice">
			<p><?php echo __('Thanks for using Kanban for WordPress!', 'kanban') ?></p>
		</div>
		<script>
			jQuery( document.body ).on( 'click', '.kanban-welcome-notice .notice-dismiss', function() {
				window.history.replaceState('Object', 'Title', '<?php echo esc_url( admin_url( 'admin.php?page=kanban_welcome' ) ); ?>' );
			});
		</script>
	<?php endif ?>



<div id="poststuff">
			<div id="post-body" class="metabox-holder columns-2">

				<div id="post-body-content">
					<div class="meta-box-sortables ui-sortable">
						<div class="postbox">
							
							<div class="inside">
								<p>
									<a href="<?php echo sprintf( '%s/%s/board', home_url(), Kanban::$slug ); ?>" class="button-primary" target="_blank" id="btn-go-to-board" onclick="window.open('<?php echo sprintf( '%s/%s/board', home_url(), Kanban::$slug ); ?>', 'kanbanboard'); return false;">
										<?php echo __( 'Go to your board', 'kanban' ); ?>
									</a>
									<a href="http://kanbanwp.com/documentation" class="button" target="_blank">
										<?php echo __( 'Documentation', 'kanban' ); ?>
										<?php echo __( '(On kanbanwp.com)', 'kanban' ); ?>
									</a>
									<a href="<?php echo admin_url('admin.php?page=kanban_settings'); ?>" class="button"><?php echo __('Settings', 'kanban') ?></a>
									<a href="<?php echo admin_url('admin.php?page=kanban_contact'); ?>" class="button"><?php echo __('Contact us', 'kanban') ?></a>
								</p>
							</div>

							<h2><span>
								<?php echo __( 'Intro to Kanban for WordPress', 'kanban' ); ?>
							</span></h2>

							<div class="inside">
								<p><?php echo __('Get started with the plugin in 60 seconds.', 'kanban') ?></p>

								<div class="video-wrapper" style="max-width: 1000px;">
									<iframe src="https://player.vimeo.com/video/145274368?title=0&amp;byline=0&amp;portrait=0" frameborder="0" webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe>
								</div><!-- video-wrapper -->

							</div>
						</div>
					</div>
				</div>



				<div id="postbox-container-1" class="postbox-container">
					<div class="meta-box-sortables">
						<div class="postbox">

							<h2><span>
							<?php echo __( 'Become a Kanban master!', 'kanban' ); ?>
							</span></h2>

							<div class="inside">

								<p>
									<?php echo __('Sign up to receive our guide to "Project Management in WordPress" delivered to your inbox over the next week.', 'kanban') ?>
								</p>

								<form method="POST" action="https://kanbanforwordpress.activehosted.com/proc.php" id="_form_8_" class="_form _form_8 _inline-form  _dark" novalidate>
									<input type="hidden" name="u" value="8" />
									<input type="hidden" name="f" value="8" />
									<input type="hidden" name="s" />
									<input type="hidden" name="c" value="0" />
									<input type="hidden" name="m" value="0" />
									<input type="hidden" name="act" value="sub" />
									<input type="hidden" name="v" value="2" />

									<p>
										<input type="text" id="email"  class="large-text" name="email" placeholder="<?php echo __('Your email', 'kanban') ?>" required/>
									</p>


									<p>
										<button class="button-secondary" type="submit"><?php echo __('Sign me up!', 'kanban') ?></button>
									</p>

									<div class="_form-thank-you" style="display:none;">
									</div>
								</form><script type="text/javascript">
									window._show_thank_you = function(id, message, trackcmp_url) {
										var form = document.getElementById('_form_' + id + '_'), thank_you = form.querySelector('._form-thank-you');
										form.querySelector('._form-content').style.visibility = 'hidden';
										thank_you.innerHTML = message;
										thank_you.style.display = 'block';
										if (typeof(trackcmp_url) != 'undefined' && trackcmp_url) {
											// Site tracking URL to use after inline form submission.
											_load_script(trackcmp_url);
										}
										if (typeof window._form_callback !== 'undefined') window._form_callback(id);
									};
									window._show_error = function(id, message, html) {
										var form = document.getElementById('_form_' + id + '_'), err = document.createElement('div'), button = form.querySelector('button');
										err.innerHTML = message;
										err.className = '_error-inner _form_error _no_arrow';
										var wrapper = document.createElement('div');
										wrapper.className = '_form-inner';
										wrapper.appendChild(err);
										button.parentNode.insertBefore(wrapper, button);
										if (html) {
											var div = document.createElement('div');
											div.className = '_error-html';
											div.innerHTML = html;
											err.appendChild(div);
										}
									};
									window._load_script = function(url, callback) {
										var head = document.querySelector('head'), script = document.createElement('script'), r = false;
										script.type = 'text/javascript';
										script.charset = 'utf-8';
										script.src = url;
										if (callback) {
											script.onload = script.onreadystatechange = function() {
												if (!r && (!this.readyState || this.readyState == 'complete')) {
													r = true;
													callback();
												}
											};
										}
										head.appendChild(script);
									};
									(function() {
										var getCookie = function(name) {
											var match = document.cookie.match(new RegExp('(^|; )' + name + '=([^;]+)'));
											return match ? match[2] : null;
										}
										var setCookie = function(name, value) {
											var now = new Date();
											var time = now.getTime();
											var expireTime = time + 1000 * 60 * 60 * 24 * 365;
											now.setTime(expireTime);
											document.cookie = name + '=' + value + '; expires=' + now + ';path=/';
										}
										var addEvent = function(element, event, func) {
											if (element.addEventListener) {
												element.addEventListener(event, func);
											} else {
												var oldFunc = element['on' + event];
												element['on' + event] = function() {
													oldFunc.apply(this, arguments);
													func.apply(this, arguments);
												};
											}
										}
										var _removed = false;
										var form_to_submit = document.getElementById('_form_8_');
										var allInputs = form_to_submit.querySelectorAll('input, select, textarea'), tooltips = [], submitted = false;
										var remove_tooltips = function() {
											for (var i = 0; i < tooltips.length; i++) {
												tooltips[i].tip.parentNode.removeChild(tooltips[i].tip);
											}
											tooltips = [];
										};
										var remove_tooltip = function(elem) {
											for (var i = 0; i < tooltips.length; i++) {
												if (tooltips[i].elem === elem) {
													tooltips[i].tip.parentNode.removeChild(tooltips[i].tip);
													tooltips.splice(i, 1);
													return;
												}
											}
										};
										var create_tooltip = function(elem, text) {
											var tooltip = document.createElement('div'), arrow = document.createElement('div'), inner = document.createElement('div'), new_tooltip = {};
											if (elem.type != 'radio' && elem.type != 'checkbox') {
												tooltip.className = '_error';
												arrow.className = '_error-arrow';
												inner.className = '_error-inner';
												inner.innerHTML = text;
												tooltip.appendChild(arrow);
												tooltip.appendChild(inner);
												elem.parentNode.appendChild(tooltip);
											} else {
												tooltip.className = '_error-inner _no_arrow';
												tooltip.innerHTML = text;
												elem.parentNode.insertBefore(tooltip, elem);
												new_tooltip.no_arrow = true;
											}
											new_tooltip.tip = tooltip;
											new_tooltip.elem = elem;
											tooltips.push(new_tooltip);
											return new_tooltip;
										};
										var resize_tooltip = function(tooltip) {
											var rect = tooltip.elem.getBoundingClientRect();
											var doc = document.documentElement, scrollPosition = rect.top - ((window.pageYOffset || doc.scrollTop)  - (doc.clientTop || 0));
											if (scrollPosition < 40) {
												tooltip.tip.className = tooltip.tip.className.replace(/ ?(_above|_below) ?/g, '') + ' _below';
											} else {
												tooltip.tip.className = tooltip.tip.className.replace(/ ?(_above|_below) ?/g, '') + ' _above';
											}
										};
										var resize_tooltips = function() {
											if (_removed) return;
											for (var i = 0; i < tooltips.length; i++) {
												if (!tooltips[i].no_arrow) resize_tooltip(tooltips[i]);
											}
										};
										var validate_field = function(elem, remove) {
											var tooltip = null, value = elem.value, no_error = true;
											remove ? remove_tooltip(elem) : false;
											if (elem.type != 'checkbox') elem.className = elem.className.replace(/ ?_has_error ?/g, '');
											if (elem.getAttribute('required') !== null) {
												if (elem.type == 'radio' || (elem.type == 'checkbox' && /any/.test(elem.className))) {
													var elems = form_to_submit.elements[elem.name];
													no_error = false;
													for (var i = 0; i < elems.length; i++) {
														if (elems[i].checked) no_error = true;
													}
													if (!no_error) {
														tooltip = create_tooltip(elem, "Please select an option.");
													}
												} else if (elem.type =='checkbox') {
													var elems = form_to_submit.elements[elem.name], found = false, err = [];
													no_error = true;
													for (var i = 0; i < elems.length; i++) {
														if (elems[i].getAttribute('required') === null) continue;
														if (!found && elems[i] !== elem) return true;
														found = true;
														elems[i].className = elems[i].className.replace(/ ?_has_error ?/g, '');
														if (!elems[i].checked) {
															no_error = false;
															elems[i].className = elems[i].className + ' _has_error';
															err.push("Checking %s is required".replace("%s", elems[i].value));
														}
													}
													if (!no_error) {
														tooltip = create_tooltip(elem, err.join('<br/>'));
													}
												} else if (elem.tagName == 'SELECT') {
													var selected = true;
													if (elem.multiple) {
														selected = false;
														for (var i = 0; i < elem.options.length; i++) {
															if (elem.options[i].selected) {
																selected = true;
																break;
															}
														}
													} else {
														for (var i = 0; i < elem.options.length; i++) {
															if (elem.options[i].selected && !elem.options[i].value) {
																selected = false;
															}
														}
													}
													if (!selected) {
														no_error = false;
														tooltip = create_tooltip(elem, "Please select an option.");
													}
												} else if (value === undefined || value === null || value === '') {
													elem.className = elem.className + ' _has_error';
													no_error = false;
													tooltip = create_tooltip(elem, "This field is required.");
												}
											}
											if (no_error && elem.name == 'email') {
												if (!value.match(/^[\+_a-z0-9-'&=]+(\.[\+_a-z0-9-']+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,})$/i)) {
													elem.className = elem.className + ' _has_error';
													no_error = false;
													tooltip = create_tooltip(elem, "Enter a valid email address.");
												}
											}
											if (no_error && /date_field/.test(elem.className)) {
												if (!value.match(/^\d\d\d\d-\d\d-\d\d$/)) {
													elem.className = elem.className + ' _has_error';
													no_error = false;
													tooltip = create_tooltip(elem, "Enter a valid date.");
												}
											}
											tooltip ? resize_tooltip(tooltip) : false;
											return no_error;
										};
										var needs_validate = function(el) {
											return el.name == 'email' || el.getAttribute('required') !== null;
										};
										var validate_form = function(e) {
											var err = form_to_submit.querySelector('._form_error'), no_error = true;
											err ? err.parentNode.removeChild(err) : false;
											if (!submitted) {
												submitted = true;
												for (var i = 0, len = allInputs.length; i < len; i++) {
													var input = allInputs[i];
													if (needs_validate(input)) {
														if (input.type == 'text') {
															addEvent(input, 'input', function() {
																validate_field(this, true);
															});
														} else if (input.type == 'radio' || input.type == 'checkbox') {
															(function(el) {
																var radios = form_to_submit.elements[el.name];
																for (var i = 0; i < radios.length; i++) {
																	addEvent(radios[i], 'click', function() {
																		validate_field(el, true);
																	});
																}
															})(input);
														} else if (input.tagName == 'SELECT') {
															addEvent(input, 'change', function() {
																validate_field(input, true);
															});
														}
													}
												}
											}
											remove_tooltips();
											for (var i = 0, len = allInputs.length; i < len; i++) {
												var elem = allInputs[i];
												if (needs_validate(elem)) {
													validate_field(elem) ? true : no_error = false;
												}
											}
											if (!no_error && e) {
												e.preventDefault();
											}
											resize_tooltips();
											return no_error;
										};
										addEvent(window, 'resize', resize_tooltips);
										addEvent(window, 'scroll', resize_tooltips);
										var form_submit = function(e) {
											e.preventDefault();
											if (validate_form()) {
												var serialized = _form_serialize(document.getElementById('_form_8_'));
												_load_script('https://kanbanforwordpress.activehosted.com/proc.php?' + serialized + '&jsonp=true');
											}
											return false;
										};
										addEvent(form_to_submit, 'submit', form_submit);
										window._old_serialize = null;
										if (typeof serialize !== 'undefined') window._old_serialize = window.serialize;
										_load_script("//d3rxaij56vjege.cloudfront.net/form-serialize/0.3/serialize.min.js", function() {
											window._form_serialize = window.serialize;
											if (window._old_serialize) window.serialize = window._old_serialize;
										});
									})();
								</script>

<?php /*
								<form action="//gelform.us10.list-manage.com/subscribe/post?u=c69e4dc144a0f56c692d34515&amp;id=93b0fa6c8c" method="post" name="mc-embedded-subscribe-form" target="_blank">
									<p>
										<label for="mce-EMAIL">Subscribe to our mailing list for news and updates!
									</label>
									</p>

									<p>
										<input type="email" value="" name="EMAIL" class="large-text" id="mce-EMAIL" placeholder="Email address">
									</p>

									<p>
										<button class="button-secondary" type="submit">Subscribe</button>
									</p>
*/ ?>
								</form>
							</div>

						</div>
					</div>
				</div>

			</div>
			<br class="clear">
		</div>




</div><!-- wrap -->
