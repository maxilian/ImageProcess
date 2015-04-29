<?php if(!defined('BASEPATH')) exit('No direct script access allowed');

 /**
  * Image Upload Processing Library
  *
  * This is a wrapper class/library for image manager on codeigniter
  *
  * @package    CodeIgniter
  * @subpackage libraries
  * @category   library
  * @version    1.0
  * @author     maxilian <panicscript@icloud.com>
  *            
  * @link       http://panicscript.com
  */

class image_process {

	public $CI;
	public $newpath 	= '';
	public $year		= '';
	public $month		= '';
	public $imageurl	= IMAGEURL; //url to image directory
	
	public function __construct() {
		
		$this->CI =& get_instance();
		
		$this->CI->load->library('session');
		$this->CI->load->helper('url');
		
		$imagePath	 = IMAGEDIR; //path to image directory
		$this->year  = date("Y");   //current year
		$this->month = date("m");  //current month
		
		//check if current year directory is exist
		if(!is_dir($imagePath.$this->year)) {
			//make directory if not exist
			mkdir($imagePath.$this->year, 0775, TRUE);
			
		} 
		
		if (!is_dir($imagePath.$this->year.'/'.$this->month)) {
			
			//make directory based on current month
			mkdir($imagePath.$this->year.'/'.$this->month, 0775, TRUE);
			
			$this->newpath 		= $imagePath.$this->year.'/'.$this->month;
			
			//create index.html to prevent browse image folder from browser
			$createIndexHTML	= $imagePath.$this->year.'/'.$this->month.'/index.html';
			$handle 			= fopen($createIndexHTML, 'w') or die('Cannot open file:  '.$createIndexHTML); //implicitly creates file
		
		} else {
		
			$this->newpath		= $imagePath.$this->year.'/'.$this->month;
		
		}
		
		
	}
	
	/*
	*		function to upload image
	*		@return		array 
	*/
	public function image_upload() {
	
		$dataToSave		= array();
		
		if(!empty($_FILES['file']['name'])) {
			
			$caption	=  $_POST['caption'];
			
			//cleanup file name
			$new_name	=  preg_replace('/[^a-zA-Z0-9.]/', '_', $_FILES['file']['name']);
			
			//setup upload configuration
			$config		= array(
							'upload_path' 	=> $this->newpath,
							'allowed_types' => "gif|jpg|png|jpeg",
							'overwrite'		=> false,
							'max_size'		=> "10485760", 
							'file_name'		=> strtolower(basename($new_name)),
							'remove_spaces' => true,
							//'max_height' => "768",
							//'max_width' => "1024"
							);
						
			$this->CI->load->library('upload', $config);

            // Attempt upload
            if($this->CI->upload->do_upload('file')) {
						
                $image_data = $this->CI->upload->data(); //get uploaded image detail
				
				//setup image detail to saved in database
				$image_link = $this->imgurl.$this->year.'/'.$this->month.'/'.$image_data["file_name"];
				$image_path = '/'.$this->year.'/'.$this->month.'/'.$image_data["file_name"];
				$thumb_path = '/'.$this->year.'/'.$this->month.'/'.$image_data["raw_name"].'_thumb'.$image_data['file_ext'];

                //generate image thumbnail thumbnail			
				$this->generate_thumb($image_data['full_path'], $image_data['file_path']);
							
				$image_name		= $this->CI->input->post('name');
				
				//generate array to save data
				$dataToSave 	= array(
									'captions' 	=> $caption,
									'name'		=> $image_name,
									'image_url' => $image_path,
									'thumb_url' => $thumb_path,
									'error'		=> false
									);
										
			
			} else { 
			
				//return error if image can not be uploaded
				$dataToSave 	= array(
									'error'		=> true
									);
				
			}
				
				
		} else {
			
			//return error if no image has uploaded
			$dataToSave 	= array(
									'error'		=> true
									);
					
		}
		
		
		return $dataToSave;
		
		
    }
	
	/*
	*		function to generate image thumbnail
	*		
	*		@param1			string
	*		@param2			string
	*
	*/
	private function generate_thumb($source_path, $target_path) {
		
		//config to generate image thumbnail
		$config_ = array(
						'image_library'	 => 'gd2',
						'source_image'	 => $source_path,
						'new_image' 	 => $target_path,
						'maintain_ratio' => TRUE,
						'create_thumb' 	 => TRUE,
						'thumb_marker'	 => '_thumb',
						'width' 		 => 150,	//set thumbnail width
						'height'		 => 150		//set thumbnail height
					);
		
		$this->CI->load->library('image_lib', $config_);
		
		if (!$this->CI->image_lib->resize()) {
			
			echo $this->CI->image_lib->display_errors();
			
		}
		
		// clear //
		$this->CI->image_lib->clear();
		
	}

		
}
?>