<?php $this->Html->addCrumb(__('Smilies'), '/admin/smilies'); ?>
<div class="users index">
	<h2><?php echo __('Users');?></h2>
	<?php echo $this->Html->link(__('Add User'), array( 'action' => 'add' ), array( 'class' => 'btn' )); ?>
	<hr/>
	<table id="usertable" class="table table-striped table-bordered">
		<thead>
			<?php
				$tableHeaders = array(
						__('username_marking'),
						__('user_type'),
						__('user_email'),
						__("registered"),
				);
				if (Configure::read('Saito.Settings.block_user_ui')) :
					$tableHeaders[] = __('user_lock');
				endif;
				echo $this->Html->tableHeaders($tableHeaders);
			?>
		</thead>
		<tbody>
			<?php foreach ($users as $user) : ?>
					<?php
					$tableCells = array(
							'<strong>'
							. $this->Html->link(
									$user['User']['username'],
									array(
									'controller' => 'users',
									'action'		 => 'view',
									'admin'			 => false,
									$user['User']['id'])
							)
							. '</strong>',
							$this->UserH->type($user['User']['user_type']),
							$this->Html->link($user['User']['user_email'],
									$user['User']['user_email']),
							// ouput date format sortable by datatable JS plugin
							date('Y-m-d H:i', strtotime($user['User']['registered'])),
					);
					if (Configure::read('Saito.Settings.block_user_ui')) :
						// without the &nbsp; the JS-sorting with the datatables plugin doesn't work
						$tableCells[] = $this->UserH->banned($user['User']['user_lock']) . '&nbsp;';
					endif;
					echo $this->Html->tableCells(
							array($tableCells), array('class' => 'a'), array('class' => 'b')
					);
					?>
	<?php endforeach; ?>
		</tbody>
	</table>
</div>
<?php echo $this->Html->script('lib/datatables/media/js/jquery.dataTables.min.js'); ?>
<?php
	$this->Js->buffer(<<<EOF
$.extend( $.fn.dataTableExt.oStdClasses, {
    "sWrapper": "dataTables_wrapper form-inline"
});
$('#usertable').dataTable({
	 "sDom": "<'row'<'span4'l><'span6'f>r>t<'row'<'span4'i><'span6'p>>",
	 "iDisplayLength": 25,
	 "sPaginationType": "bootstrap"
	});
EOF
	);

	$this->Html->block
?>