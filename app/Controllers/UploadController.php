<?php
namespace App\Controllers;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class UploadController extends Controller
{

  public function getUpload(RequestInterface $request, $response)
  {
    if(isset($_SESSION['upload_progress_uploadform'])){
      $_SESSION['upload_progress_uploadform']['bytes_processed'] = 0;
    }
    $id_upload = ini_get("session.upload_progress.name");
    r($_SESSION);
    $this->render($response, 'pages/upload.twig', ["id_upload" => $id_upload]);
  }

  public function postUpload(RequestInterface $request, $response)
  {
    $this->render($response, 'pages/upload.twig');
  }

  public function getUploadProgress(RequestInterface $request, $response)
  {
    //r($_SESSION);
    if (isset($_SESSION["upload_progress_uploadform"]) && !empty($_SESSION["upload_progress_uploadform"])) {
        $current = $_SESSION["upload_progress_uploadform"]["bytes_processed"];
        $total = $_SESSION["upload_progress_uploadform"]["content_length"];
        echo $current < $total ? ceil($current / $total * 100) : 100;
    }
    else {
        echo 25;
    }
  }

}
