<?php
	$this->start('headerSubnavLeft');
	echo $this->Layout->navbarItem(
		'<i class="fa fa-arrow-left"></i> ' . __('back_to_forum_linkname'), '/',
		['escape' => false]);
	$this->end();
?>
<div class="panel">
	<?= $this->Layout->panelHeading(__('Bookmarks'), ['pageHeading' => true]) ?>
	<div class="panel-content">
		<div id="bookmarks">
			<?php if ($bookmarks): ?>
					<div class="l-bookmarks-container">
						<div class="l-bookmarks-row bookmarks-row-header">
							<div class="l-bookmarks-cell bookmarks-cell" style="width: 4%">
							</div>
							<div class="l-bookmarks-cell bookmarks-cell" style="width: 60%">
								<?php echo __('Subject'); ?>
							</div>
							<div class="l-bookmarks-cell bookmarks-cell" style="width: 4%; padding-left: 0">
							</div>
							<div class="l-bookmarks-cell bookmarks-cell" style="width: 30%">
								<?php echo __('Comment'); ?>
							</div>
						</div>
						<?php
						foreach ($bookmarks as $bookmark) {
							$entry_sub = array(
									'Entry'		 => $bookmark['Entry'],
									'Category' => $bookmark['Entry']['Category'],
									'User'		 => $bookmark['Entry']['User'],
							);
							?>
							<div class="l-bookmarks-row js-bookmark" data-id="<?php echo $bookmark['Bookmark']['id']; ?>">
								<div class="l-bookmarks-cell bookmarks-cell" style="width: 4%">
									<?php
									echo $this->Html->link(
											'<i class="fa fa-trash-o fa-lg"></i>',
											'#',
											array(
													'class'		 => 'btn-bookmark-delete',
													'escape'	 => false,
													'title'		 => __('Delete'),
											)
									);
									?>
								</div>
								<div id="<?= $bookmark['Entry']['id'] ?>"
										 class="l-bookmarks-cell bookmarks-cell" style="width: 60%">
									<?= $this->EntryH->threadCached($entry_sub, $CurrentUser); ?>
								</div>
								<div class="l-bookmarks-cell bookmarks-cell" style="width: 4%; padding-left: 0">
									<?php
									echo $this->Html->link(
											'<i class="fa fa-edit fa-lg"></i>',
											array(
											'controller' => 'bookmarks',
											'action'		 => 'edit',
											$bookmark['Bookmark']['id']
											),
											array(
											'escape' => false,
											'title'	 => __('btn-comment-title'),
											)
									);
									?>
								</div>
								<div class="l-bookmarks-cell bookmarks-cell" style="width: 30%">
									<?= h($bookmark['Bookmark']['comment']) ?>
								</div>
							</div>
						<?php } ?>
					<?php else: ?>
						<?php
						echo $this->element('generic/no-content-yet',
								array(
								'message' => __('No bookmarks created yet.')));
						?>
				<?php endif; ?>
			</div>
		</div>
	</div>
</div>
