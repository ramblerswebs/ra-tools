<?php

// 18/07/23 Created from mod_ramblers
\defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;

//use Joomla\CMS\Factory;

echo '<h2>' . $this->params->get('page_title') . '</h2>';
//if ($group_type == "single") {
$group = $this->params->get('default_group');
//} else {
//    $group = $this->params->get('group_list');
//}

$display = new RJsonwalksStdWalkscount();
$header_tag = 'h4';

// Could probably use a loop here
echo '<' . $header_tag . '>' . "Monday Walks" . '</' . $header_tag . '>';
$options = new RJsonwalksFeedoptions($group . "&days=Monday");
$objFeed = new RJsonwalksFeed($options);
$objFeed->Display($display);

echo '<' . $header_tag . '>' . "Tuesday Walks" . '</' . $header_tag . '>';
$options = new RJsonwalksFeedoptions($group . "&days=Tuesday");
$objFeed = new RJsonwalksFeed($options);
$objFeed->Display($display);

echo '<' . $header_tag . '>' . "Wednesday Walks" . '</' . $header_tag . '>';
$options = new RJsonwalksFeedoptions($group . "&days=Wednesday");
$objFeed = new RJsonwalksFeed($options);
$objFeed->Display($display);

echo '<' . $header_tag . '>' . "Thursday Walks" . '</' . $header_tag . '>';
$options = new RJsonwalksFeedoptions($group . "&days=Thursday");
$objFeed = new RJsonwalksFeed($options);
$objFeed->Display($display);

echo '<' . $header_tag . '>' . "Friday Walks" . '</' . $header_tag . '>';
$options = new RJsonwalksFeedoptions($group . "&days=Friday");
$objFeed = new RJsonwalksFeed($options);
$message = $objFeed->Display($display);

echo '<' . $header_tag . '>' . "Saturday Walks" . '</' . $header_tag . '>';
$options = new RJsonwalksFeedoptions($group . "&days=Saturday");
$objFeed = new RJsonwalksFeed($options);
$objFeed->Display($display);

echo '<' . $header_tag . '>' . "Sunday Walks" . '</' . $header_tag . '>';
$options = new RJsonwalksFeedoptions($group . "&days=Sunday");
$objFeed = new RJsonwalksFeed($options);
$message = $objFeed->Display($display);

/*
// Could probably use a loop here
echo '<' . $header_tag . '>' . "Monday Walks" . '</' . $header_tag . '>';
$objFeed = new RJsonwalksFeed($feedurl . "&days=Monday");
$objFeed->Display($display);

echo '<' . $header_tag . '>' . "Tuesday Walks" . '</' . $header_tag . '>';
$objFeed = new RJsonwalksFeed($feedurl . "&days=Tuesday");
$objFeed->Display($display);

echo '<' . $header_tag . '>' . "Wednesday Walks" . '</' . $header_tag . '>';
$objFeed = new RJsonwalksFeed($feedurl . "&days=Wednesday");
$objFeed->Display($display);

echo '<' . $header_tag . '>' . "Thursday Walks" . '</' . $header_tag . '>';
$objFeed = new RJsonwalksFeed($feedurl . "&days=Thursday");
$objFeed->Display($display);

echo '<' . $header_tag . '>' . "Friday Walks" . '</' . $header_tag . '>';
$objFeed = new RJsonwalksFeed($feedurl . "&days=Friday");
$message = $objFeed->Display($display);

echo '<' . $header_tag . '>' . "Saturday Walks" . '</' . $header_tag . '>';
$objFeed = new RJsonwalksFeed($feedurl . "&days=Saturday");
$objFeed->Display($display);

echo '<' . $header_tag . '>' . "Sunday Walks" . '</' . $header_tag . '>';
$objFeed = new RJsonwalksFeed($feedurl . "&days=Sunday");
$message = $objFeed->Display($display);

echo '<' . $header_tag . '>' . "Walks in Total" . '</' . $header_tag . '>';
$objFeed = new RJsonwalksFeed($feedurl);
$objFeed->Display($display);
*/
