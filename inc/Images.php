<?php

/**
* Images management class
*
* Configuration :
*   folderPath : path to image folder,
*   types : Supported images file types,
*   sortByName : to sort by name. Default false, images will be sorted by date,
*   reverseOrder : to invert sort order, if 'true'
*                   if sorted by date, ordered by newests images,
*                   if sorted by name order is naturally inverted,
*   lastModifiedDateFormat : date format in label (http://php.net/manual/en/function.date.php)
*   pagination : [usePagination : true/false, imagesPerPage : number of images per pages]
*
*/

require_once "Pagination.php";

class Images {

    public function __construct() {
        $this->folderPath = "uploads/";
        $this->types = "{*.jpg,*.JPG,*.jpeg,*.JPEG,*.png,*.PNG,*.gif,*.GIF}";
        $this->sortByName = false;
        $this->reverseOrder = true;
        $this->lastModifiedDateFormat = "F d Y"; //"F d Y H:i:s"
        $this->pagination = array (
                    "usePagination" => false,
                    "imagesPerPage" => 5
                    );
        # Images array list generation
        $this->imagesList = glob($this->folderPath.$this->types, GLOB_BRACE);
    }

    /**
     *
     * Sort images list
     *
     * @param    array $imagesList to sort
     * @return    array $sortedImages
     *
     */
    public function sortImagesList(Array $imagesList){
        $sortedImages = array();
        # check sort by name or by date
        if ($this->sortByName) {
            $sortedImages = $imagesList;
            if ($this->reverseOrder) {
                rsort($sortedImages);
            } else {
                natsort($sortedImages);
            }

        } else {
            # sort by 'last modified' timestamp
            $count = count($imagesList);
            for ($i = 0; $i < $count; $i++) {
                $sortedImages[date('YmdHis', filemtime($imagesList[$i])) . $i] = $imagesList[$i];
            }
            if ($this->reverseOrder) {
                krsort($sortedImages);
            } else {
                ksort($sortedImages);
            }
        }
        return $sortedImages;
    }

    /**
     *
     * Html images list rendering
     *
     * @param    array $imagesList to render
     * @return    void, echoes Html
     *
     */
    public function renderImagesHtml(Array $imagesList) {
        foreach ($imagesList as $image) {
            $this->renderImageHtml($image);
        }
    }

    /**
     *
     * Html image rendering
     *
     * @param    string $image to render
     * @return    void, echoes Html
     *
     */
    private function renderImageHtml($image) {
        # Get image name without path and extension
        $imageName = basename($image);
        $imageName = pathinfo($imageName, PATHINFO_FILENAME);

        # Get 'last modified' date
        $lastModifiedDate = date($this->lastModifiedDateFormat, filemtime($image));

        $imageLabel = 'Image name: ' . $imageName;
        $lastModifiedLabel = '(last modified: ' . $lastModifiedDate . ')';
        $label = $imageLabel.' '.$lastModifiedLabel;

        # Begin addition
        echo <<<EOT
        <li class="ins-imgs-li">
            <div class="ins-imgs-img" onclick=this.classList.toggle("zoom");>
                <a name="$image" href="#$image ">
                    <img src="$image" alt="$imageName" title="$imageName">
                </a>
            </div>
            <div class="ins-imgs-label">$label</div>
        </li>
EOT;
    }

    /**
     *
     * Manage pagination
     *
     * @param    array $imagesList
     * @return    array data to display, array imagesToDisplay and html for pagination
     *
     */
    public function managePagination(Array $imagesList) {
        $htmlPagination = false;
        if ($this->pagination['usePagination']) {
            $Pagination  = new Pagination($imagesList);
            $pageNumber = 1;
            if (isset($_GET['page']) && is_numeric($_GET['page']) && ($_GET['page'] > 0)) {
                $pageNumber = (int) $_GET['page'];
            }
            $imagesToDisplay = $Pagination->getPageData($imagesList, $this->pagination['imagesPerPage'], $pageNumber);
            $htmlPagination = $Pagination->renderPaginationHtml($pageNumber);
        } else {
            $imagesToDisplay = $imagesList;
        }
        return array("imagesToDisplay"=>$imagesToDisplay, "htmlPagination"=>$htmlPagination);
    }

    /**
     *
     * Action render images list and eventually pagination
     *
     * @param    array $dataToDisplay
     * @return    void, echoes html
     *
     */
    public function renderHtmlData(Array $dataToDisplay) {
        echo('<ul class="ins-imgs">');
            $this->renderImagesHtml($dataToDisplay["imagesToDisplay"]);
        echo('</ul>');
        echo $dataToDisplay["htmlPagination"];
    }


}
