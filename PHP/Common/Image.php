<?php

class Image
{
   static function GetCompressedJpegFromFile($mime, $filename, $maxWidth=NULL, $maxHeight=NULL)
   {
      $image = false;
      
      if ($mime == 'image/jpeg')
         $image = imagecreatefromjpeg($filename);
      else if ($mime == 'image/gif')
         $image = imagecreatefromgif($filename);
      else if ($mime == 'image/png')
         $image = imagecreatefrompng($filename);
      else if ($mime == 'image/bmp')
         $image = imagecreatefrombmp($filename);
         
      if ($image === false)
         return NULL;
         
      $w = imagesx($image);
      $h = imagesy($image);

      // maximum size
      $new_w = $maxWidth != NULL && $maxWidth < $w ? $maxWidth : $w;
      $new_h = $maxHeight != NULL && $maxHeight < $h ? $maxHeight : $h;

      // preserve original image aspect ratio
      if (($w/$h) > ($new_w/$new_h))
      {
         $new_h = $new_w*($h/$w);
      }
      else
      {
         $new_w = $new_h*($w/$h);
      }

      $image2 = ImageCreateTrueColor($new_w, $new_h);
      imagecopyResampled($image2, $image, 0, 0, 0, 0, $new_w, $new_h, $w, $h);

      $degrees = 0;
      $exif = NULL;
      try
      {
// DRL FIXIT! Disable this until I figure out how to prevent this call from writing to the Web output!
//         $exif = exif_read_data($filename);
      }
      catch (Exception $e)
      {
         if (strpos($e->getMessage(), 'File not supported') === FALSE)
            WriteInfo("Exif error: " . $e->getMessage());
      }
      $ort = -1;
      if ($exif && isset($exif['IFD0']) && isset($exif['IFD0']['Orientation']))
      {
         $ort = $exif['IFD0']['Orientation'];
      }
      if ($exif && isset($exif['Orientation']))
      {
         $ort = $exif['Orientation'];
      }
      if ($ort != -1)
      {
         switch($ort)
         {
            case 3:
               $degrees = 180;
               break;
            case 6:
               $degrees = -90;
               break;
            case 8:
               $degrees = 90;
               break;
         }
      }
      if ($degrees != 0)
         $image2 = imagerotate($image2, $degrees, 0) ;
         
      $destination = tempnam(sys_get_temp_dir(), "Image");
      imagejpeg($image2, $destination, 80);
   	$buf = file_get_contents($destination);
      File::Delete($destination);
      
      return $buf;
   }

   static function GetCompressedJpeg($mime, $data, $maxWidth=NULL, $maxHeight=NULL)
   {
      $destination = tempnam(sys_get_temp_dir(), "Image");
      File::WriteBinaryFile($destination, $data);
      $result = Image::GetCompressedJpegFromFile($mime, $destination, $maxWidth, $maxHeight);
      File::Delete($destination);
      
      return $result;
   }
}

?>
