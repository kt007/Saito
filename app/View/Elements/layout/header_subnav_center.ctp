<?php if (isset($this->Paginator) && $this->request->params['action'] == 'index') : ?>
	<?php
		if ($CurrentUser->isLoggedIn()) :
			echo $this->Html->link('&nbsp;<i class="icon-refresh"></i>&nbsp;', '/entries/update',
					array(
							'id'			=> 'btn_manualy_mark_as_read',
							'escape' => false,
							'style'	=> "width: 100px; display: inline-block; height: 20px;",
							));
		endif;
	?>
<?php endif; ?>