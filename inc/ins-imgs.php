<?php
require_once "Images.php";

/**
*
* Images insertions
*
*/

$Images  = new Images();
$imagesList = $Images->sortImagesList($Images->imagesList);
$dataToDisplay = $Images->managePagination($imagesList);
//render full html
$Images->renderHtmlData($dataToDisplay);
