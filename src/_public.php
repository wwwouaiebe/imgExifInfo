<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of ImgExifInfo, a plugin for Dotclear 2.
# 
# Copyright (c) 2019 wwwouaiebe
# contact: http://www.ouaie.be/contact
# this plugin is inspired by the entryPhotoExifWidget plugin  by Jean-Christian Denis
# and the attachments plugin by Olivier Meunier
# 
# This  program is free software;
# you can redistribute it and/or modify it under the terms of the 
# GNU General Public License as published by the Free Software Foundation;
# either version 3 of the License, or any later version.
# 
# This program is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
# 
# You should have received a copy of the GNU General Public License
# along with this program; if not, write to the Free Software
# Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA#
#
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_RC_PATH')) {return;}

$core->tpl->addValue('ImgExifInfoContent', array('imgExifInfoTpl', 'ImgExifInfoContent'));
$core->tpl->addValue('ImgExifInfoExcerpt', array('imgExifInfoTpl', 'ImgExifInfoExcerpt'));

class imgExifInfoTpl extends dcTemplate
{

	/* __construct */

    public function __construct($cache_dir, $self_name, $core)
    {
        parent::__construct($cache_dir, $self_name, $core);
	}
	
	/* ImgExifInfoContent */

    public static function ImgExifInfoContent($attr)
    {
		return imgExifInfoTpl::ImgExifInfoStart ($attr, 'getContent' );
	}
	
	/* ImgExifInfoExcerpt */

    public static function ImgExifInfoExcerpt($attr)
    {
		return imgExifInfoTpl::ImgExifInfoStart ($attr, 'getExcerpt' );
    }
	
	/* ImgExifInfostart */

    public static function ImgExifInfostart($attr, $fct)
    {
		$before = '';
        if (isset($attr['before'])) {
			$before = addslashes($attr['before']);
		}
		$after = '';
        if (isset($attr['after'])) {
			$after = addslashes($attr['after']);
		}
		$title = '';
        if (isset($attr['title'])) {
			$title = addslashes($attr['title']);
		}
		$addClass='0';
        if (isset($attr['addClass'])) {
			$addClass = $attr['addClass'];
		}
		
		return '<?php echo  imgExifInfoTpl::AddExifInfo($_ctx->posts->' . $fct . '(\'0\'),\'' . $before . '\',\'' . $after . '\',\'' . $title . '\',\'' . $addClass . '\'); ?>';
    }
	
	/* AddExifInfo */

    public static function AddExifInfo( $text, $before, $after, $title, $addClass )
	{
		$imgsInfos = imgExifInfoTpl::SearchImgsInfo( $text );
		foreach ( $imgsInfos as $key => $imgInfo ) {
			if ( preg_match ( '#/public.*#msu', $imgInfo ['path'], $subpath ) ){
				$filePath = getcwd() . $subpath[0];
				$exifData = fileExifInfo::SearchExifData ( $filePath );
				if ( $exifData ['has_exif'] ) {
					$beforeImg = imgExifInfoTpl::AddExifData ( $before, $exifData );
					$afterImg = imgExifInfoTpl::AddExifData ( $after, $exifData );
					$newImg = $imgInfo ['img'];
					if ( !empty( $title ) ){
						$newTitle = imgExifInfoTpl::AddExifData ( $title, $exifData, $imgInfo [ 'title' ]  );
						if ( $imgInfo [ hasTitle ] ) {
							$newImg = str_replace ( $imgInfo [ 'title' ], $newTitle, $newImg );
						}
						else {
							$newImg = str_replace ( '<img', '<img title=\'' . $newTitle . '\'' , $newImg );
						}
					}
					if ( '0' != $addClass ) {
						$classPrefix = ('1' == $addClass) ? '' : $addClass;
						if ( $imgInfo [ hasClass ] ) {
							$newImg = str_replace ( $imgInfo [ 'class' ], $imgInfo [ 'class' ] . ' ' . $classPrefix . $exifData [ 'Class' ] , $newImg );
						}
						else{
							$newImg = str_replace ( '<img', '<img class=\'' . $classPrefix . $exifData [ 'Class' ] . '\'' , $newImg );
						}
					}
					$text = str_replace ( $imgInfo ['img'] , $beforeImg . $newImg . $afterImg , $text );
				}
			}
		}
		return $text;
	}
	
	/* AddExifData */

	public static function AddExifData( $text, $exifData , $oldTitle )
	{
		$textArray = explode ( '%', $text);
		$newText = '';
		foreach ( $textArray  as $i => $textPart ) {
			switch ( $textPart ) {
				case 'Model':
				case 'FocalLength':
				case 'FNumber':
				case 'ExposureTime':
				case 'ISOSpeedRatings':
				case 'Make':
					$newText .= $exifData [ $textPart ];
					break;
				case 'Title':
					if ( !empty ( $oldTitle ) ) {
						$newText .= $oldTitle;
					}
					break;
				default:
					$newText .= $textPart;
					break;
			}					
		}
		return $newText;
	}
	
	/* SearchImgsInfo */

	public static function SearchImgsInfo( $text )
	{
		global $core;
		$p_url = $core->blog->settings->system->public_url;
		preg_match_all( '/<img[^>]*>/msu', $text, $imgs );
		$results = array ( );
		foreach ( $imgs[0] as $key => $img ) {
			$imgPath = '';
			$imgTitle = '';
			$hasTitle = false;
			$imgClass = '';
			$hasClass = false;
			if ( preg_match ( '/src=("|\')[^("|\')]*/msu', $img, $path ) )
			{
				if ( preg_match ( '#' . preg_quote( $p_url ) . '.*#msu', $path[0], $subpath ) )
				{
					$imgPath = str_replace ('\\', '/', $subpath [ 0 ] );
				}
			}
			if ( preg_match ( '/title=("|\')[^("|\')]*/msu', $img, $title ) ) 
			{
				$imgTitle = substr( $title [ 0 ],7 );
				$hasTitle= true;
			}
			
			if ( preg_match ( '/class=("|\')[^("|\')]*/msu', $img, $class ) ) 
			{
				$imgClass = substr( $class [ 0 ],7 );
				$hasClass= true;
			}
			
			
			$results [] = array ( 'img' => $img, 'path' => $imgPath, 'title' => $imgTitle, 'hasTitle' => $hasTitle, 'class' => $imgClass, 'hasClass' => $hasClass );
		}
		unset ( $key );
		
		return $results;
	}	
}

if ( ! class_exists('fileExifInfo') ) {
	class fileExifInfo 
	{
		/* SearchExifData */

		public static function SearchExifData( $fi )
		{
			$mi = array(
				'RelUrl' => $fi,
				'Class' => '',
				'ExposureTime' => '',
				'FNumber' => '',
				'FocalLength' => '',
				'ISOSpeedRatings' => '',
				'Make' => '',
				'Model' => '',
				'DateTimeOriginal' => '',
				'has_exif' => false,
				'ThumbnailRelUrl' => '',
				'Size' => '0',
				'Html' => '',
				'MimeType' => '',
				'FileName' =>'',
				'has_thumbnail' =>false,
				'is_jpg' => false,
				'is_tiff' => false
			);
			if ( file_exists($mi['RelUrl']) )
			{
				$path_parts = pathinfo($mi['RelUrl']);
				$ThumbnailRelUrl = $path_parts['dirname'] . '/' . $path_parts['filename'] . '_s.' . $path_parts['extension'];
				$ext = strtoupper ( $path_parts['extension'] );
				if ( 'JPG' != $ext && 'JPEG' != $ext && 'TIF' != $ext && 'TIFF' != $ext ) {
					return $mi;
				}
				if ( 'JPG' == $ext || 'JPEG' == $ext ) {
					$mi['is_jpg'] = true;
				}
				else {
					$mi['is_tiff'] = true;
				}
				if ( file_exists($ThumbnailRelUrl) )
				{
					$mi['ThumbnailRelUrl'] = $ThumbnailRelUrl;
					$mi['has_thumbnail'] = true;
				}

				$exif = exif_read_data($mi['RelUrl'], 'ANY_TAG', true );
				if ( $exif )
				{
					if ( $exif[ 'FILE'] )
					{
						if (!empty($exif[ 'FILE']['FileSize']))
						{
							$mi['Size'] = $exif[ 'FILE']['FileSize'];
						}
						if (!empty($exif[ 'FILE']['MimeType']))
						{
							$mi['MimeType'] = $exif[ 'FILE']['MimeType'];
						}
						if (!empty($exif[ 'FILE']['FileName']))
						{
							$mi['FileName'] = $exif[ 'FILE']['FileName'];
						}
					}
					if ( $exif[ 'COMPUTED'] ) {
						if ( $exif[ 'COMPUTED']['Height'] && $exif[ 'COMPUTED']['Width'] )
						{
							$mi['Class'] =  $exif[ 'COMPUTED']['Height'] == $exif[ 'COMPUTED']['Width'] ? 'Square' : ( $exif[ 'COMPUTED']['Height'] > $exif[ 'COMPUTED']['Width'] ? "Portrait" : "Landscape" );
						}
						if ( $exif[ 'COMPUTED']['html'] ) {
							$mi['Html'] = $exif[ 'COMPUTED']['html'];
						}
					}
					if ( $exif[ 'IFD0'] )
					{
						if (!empty($exif[ 'IFD0']['Make']))
						{
							$mi['Make'] = $exif[ 'IFD0']['Make'];
						}
						if (!empty($exif[ 'IFD0']['Model']))
						{
							$mi['Model'] = $exif[ 'IFD0']['Model'];
						}
					}
					if ( $exif[ 'EXIF'] )
					{
						if (!empty($exif[ 'EXIF']['FNumber']))
						{
							$fl = sscanf($exif[ 'EXIF']['FNumber'],'%d/%d');
							$mi['FNumber'] = $fl && $fl[0] && $fl[1] ? $fl[0]/$fl[1].'' : $exif[ 'EXIF']['FNumber'];
						}
						if (!empty($exif[ 'EXIF']['ExposureTime']))
						{
							$fl = sscanf($exif[ 'EXIF']['ExposureTime'],'%d/%d');
							if ( $fl && $fl[0] && $fl[1] )
							{
								if ( $fl[0] == $fl[1] )
								{
									$mi['ExposureTime'] = '1';
								}
								else if ( $fl[0] > $fl[1] )
								{
									$mi['ExposureTime'] = sprintf ( '%d',  $fl[0]/ $fl[1] );
								}
								else
								{
									$mi['ExposureTime'] = $exif[ 'EXIF']['ExposureTime'];
								}
							}
							else
							{
								$mi['ExposureTime'] = $exif[ 'EXIF']['ExposureTime'];
							}
						}
						if (!empty($exif[ 'EXIF']['ISOSpeedRatings']))
						{
							$mi['ISOSpeedRatings'] = $exif[ 'EXIF']['ISOSpeedRatings'];
						}
						if (!empty($exif[ 'EXIF']['FocalLength']))
						{
							$fl = sscanf($exif[ 'EXIF']['FocalLength'],'%d/%d');
							$mi['FocalLength'] = $fl && $fl[0] && $fl[1] ? sprintf ( '%d',  $fl[0]/ $fl[1] ) : $im['FocalLength'];
						}
						if (!empty($exif[ 'EXIF']['DateTimeOriginal']))
						{
							$mi['DateTimeOriginal'] = $exif[ 'EXIF']['DateTimeOriginal'];
						}
					}
				}
				
				if ( !empty($mi['ISOSpeedRatings']) && !empty($mi['FocalLength']) && !empty($mi['FNumber']) && !empty($mi['ExposureTime']) )
				{
					$mi['has_exif'] = true;
				}			
			} 
			
			return $mi;
		}
	}	
}