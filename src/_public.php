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

$core->tpl->addValue('ImgExifInfo', array('imgExifInfoTpl', 'ImgExifInfo'));

class imgExifInfoTpl extends dcTemplate
{
    public function __construct($cache_dir, $self_name, $core)
    {
        parent::__construct($cache_dir, $self_name, $core);
	}
    public static function ImgExifInfo($attr)
    {
		$before = 'aaa';
		$after = 'zzz';
		$title = 'ttt';
		$excerpt = '$_ctx->posts->getExcerpt(\'0\') . ';
        return '<?php echo  imgExifInfoTpl::AddExifInfo(' . $excerpt . '$_ctx->posts->getContent(\'0\'),\'' . $before . '\',\'' . $after . '\',\'' . $title . '\'); ?>';
    }
    public static function AddExifInfo($content, $before, $after, $title)
	{
		return $before . $content . $title . $after;
	}

}