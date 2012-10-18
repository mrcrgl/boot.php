<?php

class VModelFile extends VModelConnector 
{

    public function update($param, $dontCheckNeedles=false)
 {
        if (count($_FILES) > 0) {

            $file = ((isset($param['_upload_ident'])) ? $_FILES[($param['_upload_ident'])] : array_shift($_FILES));

            $param = $this->processUpload($param, $file);
        }

        return parent::update($param, $dontCheckNeedles);
  }

    public function delete()
  {

        unlink($this->getMediaPath().$this->path);

        return parent::delete();
    }

    public function processUpload($param, $file)
    {
      if ($file['error'] == 4) {
          // No file uploaded
          return $param;
      }

        if ($file['error'] > 0) {
          throw new Exception('Error while uploading. ErrStr.: '.$file['error']);
          return false;
      }

      if (!file_exists($file['tmp_name']) || !is_readable($file['tmp_name'])) {
          throw new Exception($file['tmp_name'].' is not readable');
          return false;
      }

      if (Validator::is($this->_AllowedMime, 'array')) {
          if (!in_array($file['type'], $this->_AllowedMime)) {
              throw new Exception("unsupportet filetype ".$file['type']."; allowed types: ('".implode("', '", $this->_AllowedMime)."')");
              return false;
          }
      }

      if ($file['type'] == 'image/jpeg') {
          $extension = '.jpg';
      } elseif ($file['type'] == 'image/png') {
          $extension = '.png';
      } elseif ($file['type'] == 'image/gif') {
          $extension = '.gif';
      } elseif ($file['type'] == 'application/pdf') {
          $extension = '.pdf';
      } elseif ($file['type'] == 'video/mp4') {
          $extension = '.mp4';
      } elseif ($file['type'] == 'video/x-flv') {
          $extension = '.flv';
      } elseif ($file['type'] == 'video/x-msvideo') {
          $extension = '.avi';
      }

      //throw new Exception("unsupportet filetype $types");
      $folder = $this->getMediaPath();
      if (!is_dir($folder)) {
          mkdir($folder);
      }
      if (!is_dir($folder.DS.'uploads')) {
            mkdir($folder.DS.'uploads');
      }

      $filename = 'uploads'.DS.md5(json_encode($file)).$extension;
      $filepath = $folder.DS.$filename;


      $param['filename']    = $file['name'];
      $param['size']            = $file['size'];
      $param['filetype']    = $file['type'];
      $param['path']            = $filename;



      $bOk = move_uploaded_file($file['tmp_name'], $filepath);

      if (!$bOk) {
          throw new Exception("Could not move uploaded File to $filepath");
      }

      return $param;
  }

    function getMediaPath()
  {
        return PROJECT_HTDOCS.DS.'media';
    }


}