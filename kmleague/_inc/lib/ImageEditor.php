<?php

/*
KOIVI GD Image Watermarker for PHP Copyright (C) 2004 Justin Koivisto
Version 2.0
Last Modified: 12/9/2004

    This library is free software; you can redistribute it and/or modify it
    under the terms of the GNU Lesser General Public License as published by
    the Free Software Foundation; either version 2.1 of the License, or (at
    your option) any later version.

    This library is distributed in the hope that it will be useful, but
    WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY
    or FITNESS FOR A PARTICULAR PURPOSE. See the GNU Lesser General Public
    License for more details.

    You should have received a copy of the GNU Lesser General Public License
    along with this library; if not, write to the Free Software Foundation,
    Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA 

    Full license agreement notice can be found in the LICENSE file contained
    within this distribution package.

    Justin Koivisto
    justin.koivisto@gmail.com
    http://www.koivi.com
*/

function resize_png_image($img,$newWidth,$newHeight,$target){
    $srcImage=imagecreatefrompng($img);
    if($srcImage==''){
        return FALSE;
    }
    $srcWidth=imagesx($srcImage);
    $srcHeight=imagesy($srcImage);
    $percentage=(double)$newWidth/$srcWidth;
    $destHeight=round($srcHeight*$percentage)+1;
    $destWidth=round($srcWidth*$percentage)+1;
    if($destHeight > $newHeight){
        // if the width produces a height bigger than we want, calculate based on height
        $percentage=(double)$newHeight/$srcHeight;
        $destHeight=round($srcHeight*$percentage)+1;
        $destWidth=round($srcWidth*$percentage)+1;
    }
    $destImage=imagecreatetruecolor($destWidth-1,$destHeight-1);
    if(!imagealphablending($destImage,FALSE)){
        return FALSE;
    }
    if(!imagesavealpha($destImage,TRUE)){
        return FALSE;
    }
    if(!imagecopyresampled($destImage,$srcImage,0,0,0,0,$destWidth,$destHeight,$srcWidth,$srcHeight)){
        return FALSE;
    }
    if(!imagepng($destImage,$target)){
        return FALSE;
    }
    imagedestroy($destImage);
    imagedestroy($srcImage);
    return TRUE;
}

function watermark($watermark,$image,$size){
	$edgePadding=5; // used when placing the watermark near an edge
	$quality = 90;
	$process['wm_size']=$size;
	// file upload success
	$original = $image;
	$size=getimagesize($original);
	if($size[2]==2 || $size[2]==3){
#		$target_name = 'p11_4.jpg';
		$target = $original;
#		$watermark = $watermark;
		$wmTarget = $watermark.'.tmp';

		$origInfo = getimagesize($original); 
		$origWidth = $origInfo[0]; 
		$origHeight = $origInfo[1]; 

		$waterMarkInfo = getimagesize($watermark);
		$waterMarkWidth = $waterMarkInfo[0];
		$waterMarkHeight = $waterMarkInfo[1];

		// watermark sizing info
		if($process['wm_size']=='larger'){
			$placementX=0;
			$placementY=0;
			$process['h_position']='center';
			$process['v_position']='center';
			$waterMarkDestWidth=$waterMarkWidth;
			$waterMarkDestHeight=$waterMarkHeight;
			
			// both of the watermark dimensions need to be 5% more than the original image...
			// adjust width first.
			if($waterMarkWidth > $origWidth*1.05 && $waterMarkHeight > $origHeight*1.05){
				// both are already larger than the original by at least 5%...
				// we need to make the watermark *smaller* for this one.
				
				// where is the largest difference?
				$wdiff=$waterMarkDestWidth - $origWidth;
				$hdiff=$waterMarkDestHeight - $origHeight;
				if($wdiff > $hdiff){
					// the width has the largest difference - get percentage
					$sizer=($wdiff/$waterMarkDestWidth)-0.05;
				}else{
					$sizer=($hdiff/$waterMarkDestHeight)-0.05;
				}
				$waterMarkDestWidth-=$waterMarkDestWidth * $sizer;
				$waterMarkDestHeight-=$waterMarkDestHeight * $sizer;
			}else{
				// the watermark will need to be enlarged for this one
				
				// where is the largest difference?
				$wdiff=$origWidth - $waterMarkDestWidth;
				$hdiff=$origHeight - $waterMarkDestHeight;
				if($wdiff > $hdiff){
					// the width has the largest difference - get percentage
					$sizer=($wdiff/$waterMarkDestWidth)+0.05;
				}else{
					$sizer=($hdiff/$waterMarkDestHeight)+0.05;
				}
				$waterMarkDestWidth+=$waterMarkDestWidth * $sizer;
				$waterMarkDestHeight+=$waterMarkDestHeight * $sizer;
			}
		}else{
			$waterMarkDestWidth=round($origWidth * floatval($process['wm_size']));
			$waterMarkDestHeight=round($origHeight * floatval($process['wm_size']));
			if($process['wm_size']==1){
				$waterMarkDestWidth-=2*$edgePadding;
				$waterMarkDestHeight-=2*$edgePadding;
			}
		}

		// OK, we have what size we want the watermark to be, time to scale the watermark image
		resize_png_image($watermark,$waterMarkDestWidth,$waterMarkDestHeight,$wmTarget);
		
		// get the size info for this watermark.
		$wmInfo=getimagesize($wmTarget);
		$waterMarkDestWidth=$wmInfo[0];
		$waterMarkDestHeight=$wmInfo[1];

		$differenceX = $origWidth - $waterMarkDestWidth;
		$differenceY = $origHeight - $waterMarkDestHeight;

		// where to place the watermark?
		switch($process['h_position']){
			// find the X coord for placement
			case 'left':
				$placementX = $edgePadding;
				break;
			case 'center':
				$placementX =  round($differenceX / 2);
				break;
			default: # 'right':
				$placementX = $origWidth - $waterMarkDestWidth - $edgePadding;
				break;
		}

		switch($process['v_position']){
			// find the Y coord for placement
			case 'top':
				$placementY = $edgePadding;
				break;
			case 'center':
				$placementY =  round($differenceY / 2);
				break;
			default: # 'bottom':
				$placementY = $origHeight - $waterMarkDestHeight - $edgePadding;
				break;
		}

		if($size[2]==3)
			$resultImage = imagecreatefrompng($original);
		else
			$resultImage = imagecreatefromjpeg($original);
		imagealphablending($resultImage, TRUE);

		$finalWaterMarkImage = imagecreatefrompng($wmTarget);
		$finalWaterMarkWidth = imagesx($finalWaterMarkImage);
		$finalWaterMarkHeight = imagesy($finalWaterMarkImage);

		imagecopy($resultImage,
				  $finalWaterMarkImage,
				  $placementX,
				  $placementY,
				  0,
				  0,
				  $finalWaterMarkWidth,
				  $finalWaterMarkHeight
		);
		
		if($size[2]==3){
			imagealphablending($resultImage,FALSE);
			imagesavealpha($resultImage,TRUE);
			imagepng($resultImage,$target,$quality);
		}else{
			imagejpeg($resultImage,$target,$quality); 
		}

		imagedestroy($resultImage);
		imagedestroy($finalWaterMarkImage);
		unlink($wmTarget);
	}
}

########################################################
# Script Info
# ===========
# File: ImageEditor.php
# Created: 05/06/03
# Modified: 16/05/04
# Author: Ash Young (ash@evoluted.net)
# Website: http://evoluted.net/php/image-editor.htm
# Requirements: PHP with the GD Library
#
# Description
# ===========
# This class allows you to edit an image easily and
# quickly via php.
#
# If you have any functions that you like to see 
# implemented in this script then please just send
# an email to ash@evoluted.net
#
# Limitations
# ===========
# - GIF Editing: this script will only edit gif files
#     your GD library allows this.
#
# Image Editing Functions
# =======================
# resize(int width, int height)
#    resizes the image to proportions specified.
#
# crop(int x, int y, int width, int height)
#    crops the image starting at (x, y) into a rectangle
#    width wide and height high.
#
# addText(String str, int x, int y, Array color)
#    adds the string str to the image at position (x, y)
#    using the colour given in the Array color which
#    represents colour in RGB mode.
#
# addLine(int x1, int y1, int x2, int y2, Array color)
#    adds the line starting at (x1,y1) ending at (x2,y2)
#    using the colour given in the Array color which
#    represents colour in RGB mode.
#
# setSize(int size)
#    sets the size of the font to be used with addText()
#
# setFont(String font) 
#    sets the font for use with the addText function. This
#    should be an absolute path to a true type font
#
# shadowText(String str, int x, int y, Array color1, Array color2, int shadowoffset)
#    creates show text, using the font specified by set font.
#    adds the string str to the image at position (x, y)
#    using the colour given in the Array color which
#    represents colour in RGB mode.
#
# Useage
# ======
# First you are required to include this file into your 
# php script and then to create a new instance of the 
# class, giving it the path and the filename of the 
# image that you wish to edit. Like so:
#
# include("ImageEditor.php");
# $imageEditor = new ImageEditor("filename.jpg", "directoryfileisin/");
#
# After you have done this you will be able to edit the 
# image easily and quickly. You do this by calling a 
# function to act upon the image. See below for function
# definitions and descriptions see above. An example 
# would be:
#
# $imageEditor->resize(400, 300);
#
# This would resize our imported image to 400 pixels by
# 300 pixels. To then export the edited image there are
# two choices, out put to file and to display as an image.
# If you are displaying as an image however it is assumed
# that this file will be viewed as an image rather than
# as a webpage. The first line below saves to file, the
# second displays the image.
#
# $imageEditor->outputFile("filenametosaveto.jpg", "directorytosavein/");
#
# $imageEditor->outputImage();
########################################################

class ImageEditor {
  var $x;
  var $y;
  var $type;
  var $img;  
  var $font;
  var $error;
  var $size;

  ########################################################
  # CONSTRUCTOR
  ########################################################
  function ImageEditor($filename, $path, $col=NULL) 
  {
    $this->font = false;
    $this->error = false;
    $this->size = 25;
    if(is_numeric($filename) && is_numeric($path))
    ## IF NO IMAGE SPECIFIED CREATE BLANK IMAGE
    {
      $this->x = $filename;
      $this->y = $path;
      $this->type = "jpg";
      $this->img = imagecreatetruecolor($this->x, $this->y);
      if(is_array($col)) 
      ## SET BACKGROUND COLOUR OF IMAGE
      {
        $colour = ImageColorAllocate($this->img, $col[0], $col[1], $col[2]);
        ImageFill($this->img, 0, 0, $colour);
      }
    }
    else
    ## IMAGE SPECIFIED SO LOAD THIS IMAGE
    {
      ## FIRST SEE IF WE CAN FIND IMAGE

      if(file_exists($path . $filename))
      {
        $file = $path . $filename;
      }
      else if (file_exists($path . "/" . $filename))
      {
        $file = $path . "/" . $filename;
      }
      else
      {
        $this->errorImage("File Could Not Be Loaded");
      }
      
      if(!($this->error)) 
      {
        ## LOAD OUR IMAGE WITH CORRECT FUNCTION
        $this->type = strtolower(end(explode('.', $filename)));
        if ($this->type == 'jpg' || $this->type == 'jpeg') 
        {
          $this->img = @imagecreatefromjpeg($file);
        } 
        else if ($this->type == 'png') 
        {
          $this->img = @imagecreatefrompng($file);
        } 
        else if ($this->type == 'gif') 
        {
          $this->img = @imagecreatefrompng($file);
        }
        ## SET OUR IMAGE VARIABLES
        $this->x = imagesx($this->img);
        $this->y = imagesy($this->img);
      }
    }
  }

  ########################################################
  # RESIZE IMAGE GIVEN X AND Y
  ########################################################
  function resize($width, $height) 
  {
    if(!$this->error) 
    {
      $tmpimage = imagecreatetruecolor($width, $height);
      imagecopyresampled($tmpimage, $this->img, 0, 0, 0, 0,
                           $width, $height, $this->x, $this->y);
      imagedestroy($this->img);
      $this->img = $tmpimage;
      $this->y = $height;
      $this->x = $width;
    }
  }
  
  ########################################################
  # CROPS THE IMAGE, GIVE A START CO-ORDINATE AND
  # LENGTH AND HEIGHT ATTRIBUTES
  ########################################################
  function crop($x, $y, $width, $height) 
  {
    if(!$this->error) 
    {
      $tmpimage = imagecreatetruecolor($width, $height);
      imagecopyresampled($tmpimage, $this->img, 0, 0, $x, $y,
                           $width, $height, $width, $height);
      imagedestroy($this->img);
      $this->img = $tmpimage;
      $this->y = $height;
      $this->x = $width;
    }
  }
  
  ########################################################
  # ADDS TEXT TO AN IMAGE, TAKES THE STRING, A STARTING
  # POINT, PLUS A COLOR DEFINITION AS AN ARRAY IN RGB MODE
  ########################################################
  function addText($str, $x, $y, $col)
  {
    if(!$this->error) 
    {
      if($this->font) {
        $colour = ImageColorAllocate($this->img, $col[0], $col[1], $col[2]);
        if(!imagettftext($this->img, $this->size, 0, $x, $y, $colour, $this->font, $str)) {
          $this->font = false;
          $this->errorImage("Error Drawing Text");
        }
      }
      else {
        $colour = ImageColorAllocate($this->img, $col[0], $col[1], $col[2]);
        Imagestring($this->img, 5, $x, $y, $str, $colour);
      }
    }
  }
  
  function shadowText($str, $x, $y, $col1, $col2, $offset=2) {
   $this->addText($str, $x, $y, $col1);
   $this->addText($str, $x-$offset, $y-$offset, $col2);   
  
  }
  
  ########################################################
  # ADDS A LINE TO AN IMAGE, TAKES A STARTING AND AN END
  # POINT, PLUS A COLOR DEFINITION AS AN ARRAY IN RGB MODE
  ########################################################
  function addLine($x1, $y1, $x2, $y2, $col) 
  {
    if(!$this->error) 
    {
      $colour = ImageColorAllocate($this->img, $col[0], $col[1], $col[2]);
      ImageLine($this->img, $x1, $y1, $x2, $y2, $colour);
    }
  }

  ########################################################
  # RETURN OUR EDITED FILE AS AN IMAGE
  ########################################################
  function outputImage() 
  {
    if ($this->type == 'jpg' || $this->type == 'jpeg') 
    {
      header("Content-type: image/jpeg");
      imagejpeg($this->img);
    } 
    else if ($this->type == 'png') 
    {
      header("Content-type: image/png");
      imagepng($this->img);
    } 
    else if ($this->type == 'gif') 
    {
      header("Content-type: image/png");
      imagegif($this->img);
    }
  }

  ########################################################
  # CREATE OUR EDITED FILE ON THE SERVER
  ########################################################
  function outputFile($filename, $path) 
  {
    if ($this->type == 'jpg' || $this->type == 'jpeg') 
    {
      imagejpeg($this->img, ($path . $filename));
    } 
    else if ($this->type == 'png') 
    {
      imagepng($this->img, ($path . $filename));
    } 
    else if ($this->type == 'gif') 
    {
      imagegif($this->img, ($path . $filename));
    }
  }


  ########################################################
  # SET OUTPUT TYPE IN ORDER TO SAVE IN DIFFERENT
  # TYPE THAN WE LOADED
  ########################################################
  function setImageType($type)
  {
    $this->type = $type;
  }
  
  ########################################################
  # ADDS TEXT TO AN IMAGE, TAKES THE STRING, A STARTING
  # POINT, PLUS A COLOR DEFINITION AS AN ARRAY IN RGB MODE
  ########################################################
  function setFont($font) {
    $this->font = $font;
  }

  ########################################################
  # SETS THE FONT SIZE
  ########################################################
  function setSize($size) {
    $this->size = $size;
  }
  
  ########################################################
  # GET VARIABLE FUNCTIONS
  ########################################################
  function getWidth()                {return $this->x;}
  function getHeight()               {return $this->y;} 
  function getImageType()            {return $this->type;}

  ########################################################
  # CREATES AN ERROR IMAGE SO A PROPER OBJECT IS RETURNED
  ########################################################
  function errorImage($str) 
  {
    $this->error = false;
    $this->x = 235;
    $this->y = 50;
    $this->type = "jpg";
    $this->img = imagecreatetruecolor($this->x, $this->y);
    $this->addText("AN ERROR OCCURED:", 10, 5, array(250,70,0));
    $this->addText($str, 10, 30, array(255,255,255));
    $this->error = true;
  }
} 
?>
