	<?php
		// @todo
		$this->Blocks->set('css', '');

		echo $this->element('layout/html_header');

		echo $this->Html->css('stylesheets/static.css');
		echo $this->Html->css('../theme/Default/css/stylesheets/styles.css');
		echo $this->Html->css('stylesheets/theme');

		echo $this->Layout->appleTouchIcon([241], ['size' => false]);
		echo $this->Layout->appleTouchIcon([57, 76, 120, 152, 241]);
		echo $this->Layout->androidTouchIcon([241]);
		// keep classic favicon last, or Firefox will pick up shortcut icon
	?>
		<link rel="icon" type="image/vnd.microsoft.icon" href="/favicon.ico" />
	</head>
	<body>
		<?php if (!$CurrentUser->isLoggedIn() && $this->request->params['action'] != 'login') : ?>
			<?php echo $this->element('users/login_modal'); ?>
		<?php endif; ?>
	<div class="body">
		<div id="macnemo-support">
			<a id="macnemo-support-content" href="/wiki/Main/Unterst%c3%bctzen" title="Spenden" class="pill pill-top">
				Unterstützen
			</a>
		</div>
		<div class="l-top-menu-wrapper">
			<div class="l-top-menu top-menu">
				<?= $this->element('layout/header_login', ['divider' => '']); ?>
			</div>
		</div>
		<div id="top" class="l-top hero">
			<div class="l-top-right hero-text">
				<?php echo Stopwatch::start('header_search.ctp'); ?>
				<?php if ($CurrentUser->isLoggedIn()) {
					echo $this->element('layout/header_search', ['cache' => '+1 hour']);
				} ?>
				<?php echo Stopwatch::stop('header_search.ctp'); ?>
			</div>
			<div class="l-top-left hero-text">
				<?php
					echo $this->Html->link(
						$this->Html->image('forum_logo.svg', ['alt' => 'Logo']),
							'/' . (isset($markAsRead) ? '?mar' : ''),
						$options = [
							'id' => 'btn_header_logo',
							'escape' => false,
						]
					);
				?>
			</div>
		</div>
		<div id="topnav" class="navbar">
			<div class="navbar-content">
				<div class="navbar-left">
					<?php echo $this->fetch('headerSubnavLeft'); ?>
				</div>
				<div class="navbar-center">
					<?php
						if ($this->request->controller !== 'entries' ||
								!in_array($this->request->action, ['mix', 'view'])) {
							$_navCenter = $this->fetch('headerSubnavCenter');
							if (empty($_navCenter)) {
								$_navCenter = $title_for_page;
							}
							echo $_navCenter;
						}
					?>
				</div>
				<div class="navbar-right c_last_child">
					<?php echo $this->element('layout/header_subnav_right'); ?>
				</div>
			</div>
		</div>
		<?php echo $this->element('layout/slidetabs'); ?>
		<div id="content">
				<script type="text/javascript">
					if (!SaitoApp.request.isPreview) { $('#content').css('visibility', 'hidden'); }
				</script>
				<?php echo $this->fetch('content'); ?>
		</div>
		<div id="footer-pinned">
			<div id="bottomnav" class="navbar">
				<div class="navbar-content">
					<div class="navbar-left">
						<?php echo $this->fetch('headerSubnavLeft'); ?>
					</div>
					<div class="navbar-center">
						<a href="#" id="btn-scrollToTop" class="btn-hf-center">
							<i class="fa fa-arrow-up"></i>
						</a>
					</div>
					<div class="navbar-right c_last_child">
						<?php echo $this->element('layout/header_subnav_right'); ?>
					</div>
				</div>
			</div>
		</div>
	</div>
	<?php
		if (isset($showDisclaimer)) {
	?>
		<style>body > .body { margin-bottom: 374px; } </style>
		<div class="disclaimer" style="overflow:hidden;">
			<?php
					Stopwatch::start('layout/disclaimer.ctp');
					echo $this->element('layout/disclaimer');
					Stopwatch::stop('layout/disclaimer.ctp');
			?>
		</div>
		&nbsp;
	<?php
		}
	?>
  <?php echo $this->element('layout/html_footer'); ?>
	<div class="app-prerequisites-warnings">
		<noscript>
			<div class="app-prerequisites-warning">
				<?= __('This web-application depends on JavaScript. Please activate JavaScript in your browser.') ?>
			</div>
		</noscript>
	</div>
	</body>
</html>