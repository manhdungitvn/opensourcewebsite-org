<?php

use app\modules\bot\components\helpers\Emoji;

?>
<b><?= Emoji::AD_SEARCH . ' ' . Yii::t('bot', $sectionName) ?>: <?= $adSearch->title ?></b><br/>
<br/>
<?php if ($adSearch->description !== null) : ?>
<?= nl2br($adSearch->description); ?><br/>
<br/>
<?php endif; ?>
<?php if ($keywords != '') : ?>
# <i><?= $keywords ?></i><br/>
<br/>
<?php endif; ?>
<?php if ($user) : ?>
————<br/>
<br/>
<?php if ($user->provider_user_name) : ?>
<b><?= Yii::t('bot', 'Contact') ?>:</b> @<?= $user->provider_user_name ?>
<?php else : ?>
<b><?= Yii::t('bot', 'Contact') ?>:</b> <a href="tg://user?id=<?= $user->provider_user_id ?>"><?= $user->provider_user_first_name ?> <?= $user->provider_user_last_name ?></a>
<?php endif; ?>
<?php endif; ?>
