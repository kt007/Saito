<div>
  <?php
    if ( Configure::read('debug') == 0 ):
      echo $this->Html->script('js.min');
    else:
      echo $this->Html->script('jquery.hoverIntent.minified');
      echo $this->Html->script('lib/jquery-ui/jquery-ui-1.8.22.custom.min');
      echo $this->Html->script('classes/thread.class');
      echo $this->Html->script('classes/thread_line.class');
      echo $this->Html->script('_app');
      echo $this->Html->script('lib/jquery.scrollTo/jquery.scrollTo');
    endif;
  ?>
  <?php echo $this->fetch('script'); ?>
  <?php echo $this->Js->writeBuffer(); ?>
  <div class='clearfix'></div>
  <?php
		if ($showStopwatchOutput) {
			echo $this->Html->tag('div', $this->Stopwatch->getResult(), array('style' => 'float: left;'));
			echo $this->Html->tag('div', $this->Stopwatch->plot(), array('style' => 'float: left; margin-left: 2em;'));
		}
  ?>
<?php echo $this->element('sql_dump'); ?>
</div>