<?php

class ImageHandler {
	/**
	 * Download an image
	 * @param string $url Url to the image
	 * @return false|string False if failed, else the data of the image (converted to grayscale jpeg)
	 */
	public static function DownloadImage($url){
		$data = Http::Request($url);
		$imgFile = @imagecreatefromstring($data);
		
		if($imgFile !== false){
			$result = self::CreateImage($imgFile);
			imagedestroy($imgFile);
			return $result;
		}
		return false;
	}
	/**
	 * Create an image
	 * @param resource $img Create an image created with createimagetruecolor
	 * @return false|string False if failed, else the data of the image (converted to grayscale jpeg)
	 */
	public static function CreateImage($img){
		try{
			imagefilter($img, IMG_FILTER_GRAYSCALE);
	
			ob_start();
			imagejpeg($img);
			$image = ob_get_contents();
			ob_end_clean();
			
			return $image;
		}catch(Exception $e){
			return false;
		}
	}
}
?>
