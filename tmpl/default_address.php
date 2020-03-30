<?php
/**
 * @package    JShopping - DaData suggestions
 * @version    __DEPLOY_VERSION__
 * @author     Artem Vasilev - webmasterskaya.xyz
 * @copyright  Copyright (c) 2018 - 2020 Webmasterskaya. All rights reserved.
 * @license    GNU/GPL license: https://www.gnu.org/copyleft/gpl.html
 * @link       https://webmasterskaya.xyz/
 */

use Joomla\CMS\Language\Text;

/** @var string $input_name */

defined('_JEXEC') or die;?>

<div class="control-group">
	<div class="control-label name"><?php echo Text::_('PLG_JSHOPPING_DADATA_SUGGESTIONS_ADDRESS_FIELD_NAME'); ?></div>
	<div class="controls">
		<input type="text" name="<?php echo $input_name; ?>>" id="<?php echo $input_name; ?>" value="" class="input" style="height: inherit;" />
	</div>
</div>
