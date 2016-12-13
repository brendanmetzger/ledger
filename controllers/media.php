<?php
namespace controllers;

use \models\Instructor as Admin;
use \models\Student;
use \bloc\view;
use \models\data;

/**
 * Overview
 */

class Media extends \bloc\controller
{
  use traits\config;

  const layout = 'views/layouts/journal.html';


  public function GETscreenshots($file)
  {
    echo "<em>{$file}</em>";
  }

  public function POSTscreenshot($request, $file)
  {
    $result = json_decode($_POST['result']);
    file_put_contents(PATH."media/screenshots/{$file}", file_get_contents(urldecode($result->image_url)));
  }

  public function CLIscreenshots($file)
  {
    $para = [
      'p2i_url'         => base64_decode($file),
      'p2i_screen'      => '1024x768',
      "p2i_device"      => 6,
      'p2i_fullpage'    => 1,
      'p2i_imageformat' => 'jpg',
      'p2i_key'         => '7c6891c828ebbe46',
      'p2i_size'        => '1024x0',
      'p2i_callback'    => "http://52.35.59.206/media/screenshot/{$file}/",
    ];


    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "http://api.page2images.com/restfullink");
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($para));
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    $data = curl_exec($ch);
    curl_close($ch);

    if ($data) {
      // this means the image was decoded already
      print_r(json_decode($data));
    }


    copy(PATH."media/images/waiting.jpg", PATH."media/screenshots/{$file}");
    echo file_get_contents(PATH."media/images/waiting.jpg");
  }

}
