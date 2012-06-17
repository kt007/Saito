<?php
  $this->start('headerSubnavLeft');
  echo $this->Html->link(
      '<i class="icon-arrow-left"></i> ' . __('back_to_forum_linkname'),
      '/',
      array( 'class' => 'textlink', 'escape' => FALSE ));
  $this->end();
?>
<div id="user_view" class="user view">
	<?php 
		$linkToHistory = $this->Html->link(
												__('user_show_entries'),
												array(
														'controller' 	=> 'entries',
														'action'			=> 'search',	
														'name'				=> $user['User']['username'],
														'month'				=> strftime('%m', strtotime($user['User']['registered'])),
														'year'				=> strftime('%Y', strtotime($user['User']['registered'])),
														'adv'					=> 1,
														) ,
												array('escape' => false)
												);
		$table =
			array (
					array (
						__('username_marking'),
						$user['User']['username'] . " <span class='info_text'>({$this->UserH->type($user['User']['user_type'])})</span>", # @td user_type for mod and admin
					),
				);
		
		if ($user['User']['user_lock']) {
			$table[] = 	array (
							__('user_block'),
							$this->UserH->banned($user['User']['user_lock']),
						);
			}
		if (!empty($user['User']['user_real_name'])) {
			$table[] = 	array (
							__('user_real_name'),
							$this->UserH->minusIfEmpty($user['User']['user_real_name']),
						);
			}
		if (!empty($user['User']['user_email']) && $user['User']['personal_messages'] == TRUE) {
			$table[] = 	
					array (
						__('Contact'),
						$this->UserH->minusIfEmpty($this->UserH->contact($user['User'])),
					);
			}
		if ( $CurrentUser->isAdmin() ) {
			$table[] =
					array (
						__('userlist_email'),
						$this->Html->link($user['User']['user_email'], 'mailto:'.$user['User']['user_email']),
					);
			}
		if (!empty($user['User']['user_hp'])) {
			$table[] = 	
					array (
						__("user_hp"),
						$this->UserH->minusIfEmpty($this->UserH->homepage($user['User']['user_hp'])),
					);
			}
		if (!empty($user['User']['user_place'])) {
			$table[] = 	
					array (
							__('user_place'),
							$user['User']['user_place'],
					);
			}
		$table = array_merge($table,
			array(
					array (
							__('user_since'),
							strftime(__('date_short'), strtotime($user['User']['registered'])),
					),
					array (
							__('user_postings'),
								$user['User']['number_of_entries']
                . ( (Configure::read('Saito.Settings.userranks_show')) ? ' ('.  $this->UserH->userRank($user["User"]['number_of_entries']) . ')' : '' )
								. ' [' . $linkToHistory . ']',
					),
			));

		if (!empty($user['User']['profile'])) {
			$table[] = 	
					array (
							__('user_profile'),
							$this->Bbcode->parse($user['User']['profile']),
					);
			}

		if (!empty($user['User']['signature'])) {
			$table[] = 	
					array (
							__('user_signature'),
							$this->Bbcode->parse($user['User']['signature']),
					);
			}

			//* flattr Button
			if($user['User']['flattr_allow_user'] == TRUE && Configure::read('Saito.Settings.flattr_enabled') == TRUE) {
				$table[] =	array (
							__('flattr'),
							$this->Flattr->button('', 
									array( 
										'uid' => $user['User']['flattr_uid'],
										'language'	=> Configure::read('Saito.Settings.flattr_language'),
										'title' => '['.$_SERVER['HTTP_HOST'].'] '.$user['User']['username'] ,
										'description' => '['.$_SERVER['HTTP_HOST'].'] '.$user['User']['username'],
										'cat' => Configure::read('Saito.Settings.flattr_category'),
										'button' => 'compact',
									)
								),
					);
			}

	?>

	<div class="box-content">
		<div class="l-box-header box-header">
			<div>
				<div class='c_first_child'></div>
				<div><h1><?php echo $this->TextH->properize( $user['User']['username'] ) . ' ' . __('user_profile');?></h1> </div>
				<div class='c_last_child'></div>
			</div>
		</div>	
		<div class="content">

      <?php if ( $CurrentUser->isMod() ) : ?>
          <div class="button_mod_panel shp shp-left"
               data-title="<?php echo __('Help'); ?>"
               data-content="<?php echo __('button_mod_panel_shp'); ?>"
               >
            <div class="btn-group">
              <button class="btn dropdown-toggle btn-mini" data-toggle="dropdown">
                <i class="icon-wrench"></i>
                &nbsp;
                <i class="icon-caret-down"></i>
              </button>
              <ul class="dropdown-menu pull-right">
                <?php if ( $CurrentUser->isAdmin() || ($CurrentUser->isMod() && Configure::read('Saito.Settings.block_user_ui')) ) : ?>
                  <li>
                    <?php echo $this->Html->link(
                            '<i class="icon-ban-circle"></i> ' . (($user['User']['user_lock']) ?  __('Unlock') : __('Lock')),
                            array( 'controller' => 'users', 'action' => 'lock', $user['User']['id']),
                            array( 'escape' => FALSE )
                          );
                    ?>
                  </li>
                <?php endif; ?>
                <?php if ( $CurrentUser->isAdmin() ) : ?>
                  <li class="divider"></li>
                  <li>
                    <?php echo $this->Html->link(
                        '<i class="icon-trash"></i> ' . __('Delete'),
                        array( 'controller' => 'users', 'action' => 'delete', $user['User']['id'], 'admin' => TRUE ),
                        array('escape' => FALSE)
                        );
                    ?>
                  </li>
                <?php endif; ?>
              </ul>
            </div><!-- /btn-group -->
          </div>
        <?php endif; ?>

			<table class='table th-left elegant'>
			<?php echo 		$this->Html->tableCells($table); ?> 
			</table>
		</div>

		<?php  if (  $allowedToEditUserData ) : ?>
		<div  class="c_a_a_b">
			<div>
				<div class="c_a_a_b_a c_first_child">
						<?php echo  $this->Html->link(
													__('edit_userdata'),
													array( 'action' => 'edit', $user['User']['id'] ),
													array( 'id'	=> 'btn_user_edit', 'class' => 'btn btn-submit' )
										); ?>
				</div> <!-- c_a_a_b_a c_first_child -->
				<div class="c_a_a_b_b"> 
				</div><!-- c_a_a_b_b -->
<!--				<div class="c_a_a_b_c c_last_child">-->
<!--				</div>  c_a_a_b_c c_last_child -->
			</div>
		</div><!-- c_a_a_b -->
		<?php  endif; ?>
	</div>
	<br/>
	<br/>

	<div class="box-content">
		<div class="l-box-header box-header">
			<div>
				<div class='c_first_child'></div>
				<div><h1><?php echo $this->TextH->properize( $user['User']['username'] ) . ' ' . __('user_recentposts'); // @lo  ?>
						
					</h1> </div>
				<div class='c_last_child'></div>
			</div>
		</div>	
		<div class="content">
			<?php  if (isset($lastEntries) && !empty($lastEntries)) : ?>
			<ul>
				<?php  foreach ($lastEntries as $entry) : ?>
				<li>
					<?php echo  $this->element('entry/thread_cached', array ( 'entry_sub' => $entry, 'level' => 0 )); ?>
				</li>
				<?php  endforeach; ?>
			</ul>
		<?php  endif ; ?>
		</div>
	</div>


</div>