<?php
require_once(realpath(dirname( __FILE__)) . '/../../define.php');
require_once(realpath(dirname( __FILE__)) . '/../entity/BlobFile.php');

use WindowsAzure\Common\ServicesBuilder;
use WindowsAzure\Blob\Models\CreateContainerOptions;
use WindowsAzure\Blob\Models\PublicAccessType;
use WindowsAzure\Blob\Models\CreateBlobOptions;
use WindowsAzure\Blob\Models\ListBlobsOptions;
use WindowsAzure\Blob\Models\ListBlobsResult;

class SC_Helper_AzureBlob {

    private static $instance = null;
    protected function __construct() {
        $this->initialize();
    }

    public static function getInstance() {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    protected function initialize() {
        $this->connectionString = 'DefaultEndpointsProtocol=' . ENDPOINT_PROTOCOL . ';AccountName=' . AZURE_BLOB_ACCOUNT_NAME . ';AccountKey=' . AZURE_BLOB_ACCOUNT_KEY;
        $this->containerName = sha1(HTTP_URL);
        $this->blobRestProxy = ServicesBuilder::getInstance()->createBlobService($this->connectionString);
        $objListContainersResult = $this->blobRestProxy->listContainers();
        foreach ($objListContainersResult->getContainers() as $objContainer) {
            if ($objContainer->getName() == $this->containerName) {
                return;
            }
        }

        $createContainerOptions = new CreateContainerOptions();
        $createContainerOptions->setPublicAccess(PublicAccessType::BLOBS_ONLY);
        try {
            // Create container.
            $this->blobRestProxy->createContainer($this->containerName, $createContainerOptions);
        } catch(ServiceException $e){
            $code = $e->getCode();
            $error_message = $e->getMessage();
            echo $code.": ".$error_message."<br />";
        }
    }


    public function copyToBlob(BlobFile $objFile) {
        if ($this->exists_blob($objFile)) {
            $this->outLog('exists_blob');
            $objBlobMetadataResult = $this->blobRestProxy->getBlobMetadata($this->containerName, 'save_image/' . $objFile->file_name);
            $arrMetadata = $objBlobMetadataResult->getMetadata();
            if ($arrMetadata['mtime'] == $objFile->getMtime()) {
                $this->outLog('equals');
                return;
            } elseif ($arrMetadata['mtime'] > $objFile->getMtime()) {
                $result = file_put_contents(IMAGE_SAVE_REALDIR . $objFile->file_name,
                                            ENDPOINT_PROTOCOL . '://' . AZURE_BLOB_ACCOUNT_NAME . '.blob.core.windows.net/' . $this->containerName . '/save_image/' . $objFile->file_name);
                $this->outLog($result);
            } else {
            }
        } else {
            try {
                $mimeType = $objFile->getMimeType();
                $this->outLog('create: ' . $objFile->file_name);
                $this->outLog('mimeType: ' . $mimeType);
                $createBlobOptions = new CreateBlobOptions();
                $createBlobOptions->setBlobContentType($mimeType);
                $createBlobOptions->setMetadata(array('mtime' => $objFile->getMtime()));
                $this->blobRestProxy->createBlockBlob($this->containerName, 'save_image/' . $objFile->file_name, $objFile->getResources(), $createBlobOptions);
            } catch (ServiceException $e) {
                $code = $e->getCode();
                $error_message = $e->getMessage();
                $this->outLog($code.": ".$error_message."<br />");
            }

        }
    }

    public function exists_blob(BlobFile $objFile) {
        $listBlobOptions = new ListBlobsOptions();
        $listBlobOptions->setPrefix('save_image/' . $objFile->file_name);
        $listBlobResults = $this->blobRestProxy->listBlobs($this->containerName, $listBlobOptions);
        $arrBlobs = $listBlobResults->getBlobs();
        if (is_array($arrBlobs) && count($arrBlobs) > 0) {
            return true;
        }
        return false;
    }

    private function outLog($message) {
        error_log($message . PHP_EOL, 3, realpath(dirname( __FILE__)) . '/blob.log');
    }
}
