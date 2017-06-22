<div id="kanban-modal-wrapper">

<div id="kanban-modal-background"></div>

<div id="kanban-modal">
	<div id="kanban-modal-nav">
		<a href="#" id="kanban-modal-close-button" class="kanban-modal-hide">Close</a>
	</div><!--modal-nav-->

	<div id="kanban-modal-body-wrapper">
		<div id="kanban-modal-body">

			<form action="" method="post">

				<?php if ( isset($_GET['activation']) ) : ?>
					<p>
						<b style="font-size: 2.618em; line-height: 1;">
						<?php echo __('Welcome to Kanban for WordPress!') ?>
						</b>
					</p>
				<?php endif ?>

				<h1>Let's get started!</h1>


				<p><b>How do you plan to use your Kanban board?</b> Choose one below and we'll set up your first Kanban
					board for you (then you can customize it however you'd like).</p>

				<p class="kanban-lead"><b>Choose one:</b></p>

				<div id="kanban-onboard-use-cases">
					<input type="radio" name="kanban_preset" value="project_management"
					       id="kanban_preset_project_management">
					<label for="kanban_preset_project_management" class="kanban-onboard-use-case">
						<h3>Project management</h3>
						<svg width="50" height="50" viewBox="0 0 1792 1792" xmlns="http://www.w3.org/2000/svg">
							<path d="M1472 930v318q0 119-84.5 203.5t-203.5 84.5h-832q-119 0-203.5-84.5t-84.5-203.5v-832q0-119 84.5-203.5t203.5-84.5h832q63 0 117 25 15 7 18 23 3 17-9 29l-49 49q-10 10-23 10-3 0-9-2-23-6-45-6h-832q-66 0-113 47t-47 113v832q0 66 47 113t113 47h832q66 0 113-47t47-113v-254q0-13 9-22l64-64q10-10 23-10 6 0 12 3 20 8 20 29zm231-489l-814 814q-24 24-57 24t-57-24l-430-430q-24-24-24-57t24-57l110-110q24-24 57-24t57 24l263 263 647-647q24-24 57-24t57 24l110 110q24 24 24 57t-24 57z"/>
						</svg>
						<p class="kanban-lead">Track tasks across projects, assign tasks to users, see who's working on
							what.</p>
						<p><i>Statues: Backlog, Ready, In progress, QA, Done, Archive</i></p>
					</label><!--use-case-->
					<input type="radio" name="kanban_preset" value="editorial_cal" id="kanban_preset_editorial_cal">
					<label for="kanban_preset_editorial_cal" class="kanban-onboard-use-case">
						<h3>Editorial calendar</h3>
						<svg width="50" height="50" viewBox="0 0 1792 1792" xmlns="http://www.w3.org/2000/svg">
							<path d="M1303 964l-512 512q-10 9-23 9t-23-9l-288-288q-9-10-9-23t9-22l46-46q9-9 22-9t23 9l220 220 444-444q10-9 23-9t22 9l46 46q9 9 9 22t-9 23zm-1175 700h1408v-1024h-1408v1024zm384-1216v-288q0-14-9-23t-23-9h-64q-14 0-23 9t-9 23v288q0 14 9 23t23 9h64q14 0 23-9t9-23zm768 0v-288q0-14-9-23t-23-9h-64q-14 0-23 9t-9 23v288q0 14 9 23t23 9h64q14 0 23-9t9-23zm384-64v1280q0 52-38 90t-90 38h-1408q-52 0-90-38t-38-90v-1280q0-52 38-90t90-38h128v-96q0-66 47-113t113-47h64q66 0 113 47t47 113v96h384v-96q0-66 47-113t113-47h64q66 0 113 47t47 113v96h128q52 0 90 38t38 90z"/>
						</svg>
						<p class="kanban-lead">Use Kanban to track blog posts or articles, the authors who write them,
							and when they're published.</p>
						<p><i>Statues: Ideas, Assigned, In progress, Edit, To publish, Published</i></p>
					</label><!--use-case-->
					<input type="radio" name="kanban_preset" value="job_applicant" id="kanban_preset_job_applicant">
					<label for="kanban_preset_job_applicant" class="kanban-onboard-use-case">
						<h3>Job applicant tracking</h3>
						<svg width="50" height="50" viewBox="0 0 1792 1792" xmlns="http://www.w3.org/2000/svg">
							<path d="M640 256h512v-128h-512v128zm1152 640v480q0 66-47 113t-113 47h-1472q-66 0-113-47t-47-113v-480h672v160q0 26 19 45t45 19h320q26 0 45-19t19-45v-160h672zm-768 0v128h-256v-128h256zm768-480v384h-1792v-384q0-66 47-113t113-47h352v-160q0-40 28-68t68-28h576q40 0 68 28t28 68v160h352q66 0 113 47t47 113z"/>
						</svg>
						<p class="kanban-lead">Collect job applicants, move them through the interview process, and
							decide who gets the job.</p>
						<p><i>Statues: Applied, Interview 1, Interview 2, Offer made, Rejected</i></p>
					</label><!--use-case-->
					<input type="radio" name="kanban_preset" value="sales" id="kanban_preset_sales">
					<label for="kanban_preset_sales" class="kanban-onboard-use-case">
						<h3>Sales pipeline</h3>
						<svg width="50" height="50" viewBox="0 0 1792 1792" xmlns="http://www.w3.org/2000/svg">
							<path d="M1362 1185q0 153-99.5 263.5t-258.5 136.5v175q0 14-9 23t-23 9h-135q-13 0-22.5-9.5t-9.5-22.5v-175q-66-9-127.5-31t-101.5-44.5-74-48-46.5-37.5-17.5-18q-17-21-2-41l103-135q7-10 23-12 15-2 24 9l2 2q113 99 243 125 37 8 74 8 81 0 142.5-43t61.5-122q0-28-15-53t-33.5-42-58.5-37.5-66-32-80-32.5q-39-16-61.5-25t-61.5-26.5-62.5-31-56.5-35.5-53.5-42.5-43.5-49-35.5-58-21-66.5-8.5-78q0-138 98-242t255-134v-180q0-13 9.5-22.5t22.5-9.5h135q14 0 23 9t9 23v176q57 6 110.5 23t87 33.5 63.5 37.5 39 29 15 14q17 18 5 38l-81 146q-8 15-23 16-14 3-27-7-3-3-14.5-12t-39-26.5-58.5-32-74.5-26-85.5-11.5q-95 0-155 43t-60 111q0 26 8.5 48t29.5 41.5 39.5 33 56 31 60.5 27 70 27.5q53 20 81 31.5t76 35 75.5 42.5 62 50 53 63.5 31.5 76.5 13 94z"/>
						</svg>
						<p class="kanban-lead">Collect leads, Follow-up with proposals, close more deals.</p>
						<p><i>Statues: New lead, 1st contact, Proposal out, Proposal accepted, Rejected</i></p>
					</label><!--use-case-->
					<input type="radio" name="kanban_preset" value="basic" id="kanban_preset_basic">
					<label for="kanban_preset_basic" class="kanban-onboard-use-case">
						<h3>Basic</h3>
						<svg width="50" height="50" viewBox="0 0 1792 1792" xmlns="http://www.w3.org/2000/svg">
							<path d="M224 1536h608v-1152h-640v1120q0 13 9.5 22.5t22.5 9.5zm1376-32v-1120h-640v1152h608q13 0 22.5-9.5t9.5-22.5zm128-1216v1216q0 66-47 113t-113 47h-1344q-66 0-113-47t-47-113v-1216q0-66 47-113t113-47h1344q66 0 113 47t47 113z"/>
						</svg>
						<p class="kanban-lead">Track basic tasks as you do them.</p>
						<p><i>Statues: To do, Doing, Done</i></p>
					</label><!--use-case-->
					<input type="radio" name="kanban_preset" value="none" id="kanban_preset_none">
					<label for="kanban_preset_none" class="kanban-onboard-use-case">
						<h3>Custom</h3>
						<svg width="50" height="50" viewBox="0 0 1792 1792" xmlns="http://www.w3.org/2000/svg">
							<path d="M491 1536l91-91-235-235-91 91v107h128v128h107zm523-928q0-22-22-22-10 0-17 7l-542 542q-7 7-7 17 0 22 22 22 10 0 17-7l542-542q7-7 7-17zm-54-192l416 416-832 832h-416v-416zm683 96q0 53-37 90l-166 166-416-416 166-165q36-38 90-38 53 0 91 38l235 234q37 39 37 91z"/>
						</svg>
						<p class="kanban-lead">Set everything up yourself.</p>
						<p><i>Statues: None</i></p>
					</label><!--use-case-->
				</div><!--use-cases-->

				<p class="kanban-modal-button-wrapper">
					<button type="submit" class="button button-primary">
						Set it up!
					</button>
				</p>

				<?php if ( isset($_GET['board_id']) ) : ?>
					<input type="hidden" name="board_id" value="<?php echo (int) sanitize_text_field($_GET[ 'board_id' ]) ?>">
				<?php endif // $_GET['board_id'] ?>

				<?php wp_nonce_field('kanban-add-preset', Kanban_Utils::get_nonce()) ?>

			</form>

		</div><!--modal-body-->
	</div><!--modal-body-wrapper-->

</div><!--modal-->

</div><!--wrapper-->