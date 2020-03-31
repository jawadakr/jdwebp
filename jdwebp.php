<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Content
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * Developer Name : Jawad Ul Hassan
 * Email : jawad@websysdynamics.com
 *
 */

	
defined('_JEXEC') or die;

if(!defined('DS'))
{
define('DS', DIRECTORY_SEPARATOR);
}


/**
 * Plugin to enable loading webp image into content (e.g. articles)
 * This uses the {jdwebp image_url,alt} syntax
 *
 * @since  1.5
 */
class PlgContentJdwebp extends JPlugin
{
	protected static $modules = array();

	protected static $mods = array();

	
	/**
	 * Plugin that loads webp images after conversion within content
	 *
	 * @param   string   $context   The context of the content being passed to the plugin.
	 * @param   object   &$article  The article object.  Note $article->text is also available
	 * @param   mixed    &$params   The article params
	 * @param   integer  $page      The 'page' number
	 *
	 * @return  mixed   true if there is an error. Void otherwise.
	 *
	 * @since   1.6
	 */
	public function onContentPrepare($context, &$article, &$params, $page = 0)
	{

		// Don't run this plugin when the content is being indexed
		if ($context === 'com_finder.indexer')
		{
			return true;
		}

		// Simple performance check to determine whether bot should process further
		if (strpos($article->text, 'jdwebp') === false ) // && strpos($article->text, 'jdwebp') === false)
		{
			return true;
		}

		// Expression to search for (jdwebp)
		$regex = '/{jdwebp\s(.*?)}/i';
		
		$quality = $this->params->def('quality', '75');


		preg_match_all($regex, $article->text, $matches, PREG_SET_ORDER);
		
		// No matches, skip this
		
		if ($matches != '' )
		{
			foreach ($matches as $match)
			{
				$matcheslist = explode(',', $match[1]);

				// We may not have a image alt text so fall back to the plugin default.
				if (!array_key_exists(1, $matcheslist))
				{
					$matcheslist[1] = '';
				}

				// do the main magic here.

				$img_link = trim($matcheslist[0]);
				$alt    = trim($matcheslist[1]);
				
				
				
				$filename_from_url = parse_url($img_link);
				
				$source= str_replace(JURI::base(),'',$img_link);
				
				/* the logic work as simple.
				1) check image path to make sure it exist on the website.
				2) check if image with same name is already creted in webp folder in images directory
				3) if webp image present then return that one.
				4) it not then create a webp image and return that one.
				*/
				
				
				$destination ='';
				
				$final_image_link= JPATH_BASE.'/'.$source;
						
				$output = $this->_load($final_image_link,$source,$alt,$quality);

				// magic ends here
				
				// We should replace only first occurrence in order to allow image link to regenerate webp image:
				if (($start = strpos($article->text, $match[0])) !== false)
				{
					$article->text = substr_replace($article->text, $output, $start, strlen($match[0]));
				}

				//$style = $this->params->def('style', 'none');
			}
		}
		
		
		
		
	}
	

	
	function convertImageToWebP($source, $destination, $quality=100) 
	{
		
	$extension = pathinfo($source, PATHINFO_EXTENSION);
	if ($extension == 'jpeg' || $extension == 'jpg') 
		$image = imagecreatefromjpeg($source);
	elseif ($extension == 'gif') 
		$image = imagecreatefromgif($source);
	elseif ($extension == 'png') 
		$image = imagecreatefrompng($source);
	return imagewebp($image, $destination, intval($quality));
	
	}

	function iswebp_supported()
	{
		if( strpos( $_SERVER['HTTP_ACCEPT'], 'image/webp' ) !== false || strpos( $_SERVER['HTTP_USER_AGENT'], ' Chrome/' ) !== false ) {
    		// webp is supported!
			// I ll probably update this one in future once more browsers become compatible. AT the moment in this corona pendamic time
			// only 2 browsers support it.
			return true;
		}
		else return false;

	}


	/**
	 * Loads and convert the image into webp image or show the original one.
	 *
	 * @param   string  $quality  Quality of the webp image at the time of conversion
	 *
	 * @return  mixed
	 *
	 * @since   1.6
	 */
	 
	 
	protected function _load($final_image_link,$source,$alt = 'none',$quality=100)
	{
						
						
		if(version_compare(PHP_VERSION, JOOMLA_MINIMUM_PHP, '>') && function_exists('imagewebp') && ($this->iswebp_supported()==true) )
		{
			
			//echo 'inside load';
			// checking for the file.
				// if exist then we will proceed for the conversion part.
				//$fileExists = JFile::exists(JPATH_BASE.'/'.$source);
				$fileExists = JFile::exists($final_image_link);	
				
				$final_img_html='';
			
				if($fileExists==true)
				{
				
				// going to check for webp image
					
				//$JImage = new JImage(JPath::clean($path . $fileName));
				//$JImage = new JImage(JPath::clean(JPATH_BASE.'/'.$source));
				//$JImage = new JImage(JPath::clean($final_image_link));
						
				// print_r($JImage->getImageFileProperties($final_image_link));		
				//$JImage = new JImage(JPATH_BASE.'/'.$source/* JPath::clean($filename_from_url['path'])*/);

			//	$image['address'] = $uri . $fileName;
			//	$image['path']    = $fileName;
			//	$image['height']  = $JImage->getHeight();
			//	$image['width']   = $JImage->getWidth();
				
			//	echo '<pre>';
			//	print_r($image);
			//	print_r($JImage);
			//	echo '</pre>';
				
				
				$image_info= pathinfo(JPATH_BASE.'/'.$source);
				
				/*print_r($image_info);
				echo $image_info['dirname'];
				echo '<br />';
				echo $image_info['filename'];
				echo '</pre>';
				*/
				
				$destination = $image_info['dirname'].DS.$image_info['filename'].'.webp';
				//echo $source;
				$destination_live_url=JURI::base().str_replace('.'.$image_info['extension'],'.webp', $source);
				
				//$destination_live_url= $source;
				
				if($alt=='')
				{
					$alt= $image_info['filename'];
				}
					
				
				$fileExists_wp = JFile::exists($destination);
		

				if($fileExists_wp==false)
				{
	
						$this->convertImageToWebP($source, $destination,$quality);
				
				}
				else
				{
					// will add something for future.
				}
				
				
				$final_img_html= '<img src="'.$destination_live_url.'" alt="'.$alt.'" />'; 
				

		}
		
		}
		else
		{
		//	echo $source;
		//	echo 'we are here ';
			$final_img_html =  '<img src="'.$final_image_link.'" alt="'.$alt.'" />';
			
			$final_img_html =  '<img src="'.$source.'" alt="'.$alt.'" />';

		}
		
		ob_start();
		
		
		echo ($final_img_html);		
		
		$html = ob_get_clean();
		
		return $html;
		
	
	}


}
