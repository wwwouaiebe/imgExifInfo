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
    public function __construct($cache_dir, $self_name, $core)
    {
        parent::__construct($cache_dir, $self_name, $core);
	}
    public static function ImgExifInfoContent($attr)
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
		
		return '<?php echo  imgExifInfoTpl::AddExifInfo($_ctx->posts->getContent(\'0\'),\'' . $before . '\',\'' . $after . '\',\'' . $title . '\'); ?>';
    }
    public static function ImgExifInfoExcerpt($attr)
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
		
		return '<?php echo  imgExifInfoTpl::AddExifInfo($_ctx->posts->getExcerpt(\'0\'),\'' . $before . '\',\'' . $after . '\',\'' . $title . '\'); ?>';
    }
	
    public static function AddExifInfo( $text, $before, $after, $title )
	{
		$ImgsInfos = imgExifInfoTpl::SearchImgsInfo( $text );
		foreach ( $ImgsInfos as $key => $imgInfo ) {
			$exifData = imgExifInfoTpl::SearchExifData ( $imgInfo ['path'] );
			$beforeImg .= imgExifInfoTpl::addExifData ( $before, $exifData );
			$afterImg = imgExifInfoTpl::addExifData ( $after, $exifData );
			$text = str_replace ( $imgInfo ['img'] , $beforeImg . $imgInfo ['img'] . $afterImg , $text );
		}
		return $text;
	}
	public static function addExifData( $text, $exifData )
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
				default:
					$newText .= $textPart;
					break;
			}					
		}
		return $newText;
	}
	
	public static function SearchImgsInfo( $text )
	{
		global $core;
		$p_url = $core->blog->settings->system->public_url; //= /public
		preg_match_all( '/<img[^>]*>/msu', $text, $imgs );
		$results = array ( );
		foreach ( $imgs[0] as $key => $img ) {
			$imgPath = '';
			$imgTitle = '';
			$hasTitle = false;
			if ( preg_match ( '/src=("|\')[^("|\')]*/msu', $img, $path ) )
			{
				if ( preg_match ( $p_url . '.*/msu', $path[0],$subpath ) )
				{
					$imgPath = str_replace ('\\', '/', substr ( $subpath [ 0 ], strlen ( $p_url ) ) );
				}
			}
			if ( preg_match ( '/title=("|\')[^("|\')]*/msu', $img, $title ) ) 
			{
				$imgTitle = substr( $title [ 0 ],7 );
				$hasTitle= true;
			}
			$results [] = array ( 'img' => $img, 'path' => $imgPath, 'title' => $imgTitle, 'hasTitle' => $hasTitle );
		}
		unset ( $key );
		
		return $results;
	}
	
    public static function SearchExifData( $fi )
	{
		$mi = array(
			'RelUrl' => 'public/' . $fi,
			'Class' => 'Landscape',
			'ExposureTime' => '',
			'FNumber' => '',
			'FocalLength' => '',
			'ISOSpeedRatings' => '',
			'Make' => '',
			'Model' => '',
			'DateTimeOriginal' => '',
			'has_exif' => false,
			'ThumbnailUrl' => '',
			'Size' => '0',
			'MimeType' => '',
			'FileName' =>'',
			'has_thumbnail' =>false,
			'is_jpg' => false,
			'is_tiff' => false
		);
		if ( file_exists($mi['RelUrl']) )
		{
			$path_parts = pathinfo($mi['RelUrl']);
			$ThumbnailUrl = $path_parts['dirname'] . '/' . $path_parts['filename'] . '_s.' . $path_parts['extension'];
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
			if ( file_exists($ThumbnailUrl) )
			{
				$mi['ThumbnailUrl'] = $ThumbnailUrl;
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
				if ( $exif[ 'COMPUTED'] && $exif[ 'COMPUTED']['Height'] && $exif[ 'COMPUTED']['Width'] )
				{
					$mi['Class'] = $exif[ 'COMPUTED']['Height'] > $exif[ 'COMPUTED']['Width'] ? "Portrait" : "Landscape";
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

























