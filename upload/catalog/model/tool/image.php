<?php
class ModelToolImage extends Model {
	
	private static $status = null;
	
	public function resize($filename, $width, $height) {
		
		// Http Image 
		if (strncasecmp($filename, "http", 4) === 0) {
					
			$headers = @get_headers($filename,1);	
					
			$pathUrl = parse_url(urldecode($filename), PHP_URL_PATH);
			$extension = pathinfo(parse_url($filename,PHP_URL_PATH),PATHINFO_EXTENSION);
			
 			$image_old = $filename;
			$image_new = 'cache' . utf8_substr($pathUrl, 0, utf8_strrpos($pathUrl, '.')) . '-' . $width . 'x' . $height . '.' . $extension;						
			
			if (!is_file(DIR_IMAGE . $image_new) || (strtotime($headers['Last-Modified']) > filemtime(DIR_IMAGE . $image_new))) {
				list($width_orig, $height_orig, $image_type) = getimagesize($filename);
				
				if (!in_array($image_type, array(IMAGETYPE_PNG, IMAGETYPE_JPEG, IMAGETYPE_GIF))) {
					return $image_old ;
				}
				
				$path = '';
				
				$directories = explode('/', dirname($image_new));
				
				foreach ($directories as $directory) {
					$path = $path . '/' . $directory;
					
					if (!is_dir(DIR_IMAGE . $path)) {
						@mkdir(DIR_IMAGE . $path, 0777);
					}
				}
				
				if ($width_orig != $width || $height_orig != $height) {
					$image = new Image($image_old);
					$image->resize($width, $height);
					$image->save(DIR_IMAGE . $image_new);
				} else {
					copy($image_old, DIR_IMAGE . $image_new);
				}
				
				$img_log = new Log('img_log.log');  //storage/logs
				
				$optimized_image_path = escapeshellarg(DIR_IMAGE . $image_new);
				if (($extension == 'jpeg' || $extension == 'jpg') && (static::canDoOptimise()['jpegoptim'])) {
					$img_log->write(shell_exec("jpegoptim --max=85 -strip-all --all-progressive " . $optimized_image_path ."| tr '\n' ' '"));
				} elseif (($extension == 'png') && (static::canDoOptimise()['optipng'])) {
					$img_log->write(shell_exec("optipng -strip all -i0 -o4 ". $optimized_image_path ." 2>&1 | sed -n '/Processing/p;/Output file size/p' | tr '\n' ' '"));
				}	
				
			}
		}
		// Image File Standard opencart
		else {		
			if (!is_file(DIR_IMAGE . $filename) || substr(str_replace('\\', '/', realpath(DIR_IMAGE . $filename)), 0, strlen(DIR_IMAGE)) != str_replace('\\', '/', DIR_IMAGE)) {
				return;
			}

			$extension = pathinfo($filename, PATHINFO_EXTENSION);

			$image_old = $filename;
			$image_new = 'cache/' . utf8_substr($filename, 0, utf8_strrpos($filename, '.')) . '-' . (int)$width . 'x' . (int)$height . '.' . $extension;

			if (!is_file(DIR_IMAGE . $image_new) || (filemtime(DIR_IMAGE . $image_old) > filemtime(DIR_IMAGE . $image_new))) {
				list($width_orig, $height_orig, $image_type) = getimagesize(DIR_IMAGE . $image_old);
				 
				if (!in_array($image_type, array(IMAGETYPE_PNG, IMAGETYPE_JPEG, IMAGETYPE_GIF))) { 
					return DIR_IMAGE . $image_old;
				}
						
				$path = '';

				$directories = explode('/', dirname($image_new));

				foreach ($directories as $directory) {
					$path = $path . '/' . $directory;

					if (!is_dir(DIR_IMAGE . $path)) {
						@mkdir(DIR_IMAGE . $path, 0777);
					}
				}

				if ($width_orig != $width || $height_orig != $height) {
					$image = new Image(DIR_IMAGE . $image_old);
					$image->resize($width, $height);
					$image->save(DIR_IMAGE . $image_new);
				} else {
					copy(DIR_IMAGE . $image_old, DIR_IMAGE . $image_new);
				}
					
				$img_log = new Log('img_log.log');  //storage/logs
        
				$optimized_image_path = escapeshellarg(DIR_IMAGE . $image_new);
				if ($extension == 'jpeg' || $extension == 'jpg') {        
					$img_log->write(shell_exec("jpegoptim --max=85 -strip-all --all-progressive " . $optimized_image_path ."| tr '\n' ' '"));
				} elseif ($extension == 'png') {
					$img_log->write(shell_exec("optipng -strip all -i0 -o4 ". $optimized_image_path ." 2>&1 | sed -n '/Processing/p;/Output file size/p' | tr '\n' ' '"));                
				}					
			}		
		}
		
		
		$image_new = str_replace(' ', '%20', $image_new);  // fix bug when attach image on email (gmail.com). it is automatic changing space " " to +
		
		if ($this->request->server['HTTPS']) {
			return $this->config->get('config_ssl') . 'image/' . $image_new;
		} else {
			return $this->config->get('config_url') . 'image/' . $image_new;
		}
	}
	
	public function is_fileimg($filename):bool {					
		
		if (strncasecmp($filename, "http", 4) === 0) {			
			$headers = @get_headers($filename,1);
			if (is_array($headers)) {
				return (!strpos($headers[0], '200'))?false:true;
			} else {
			  return false;
			}				
		} 	
		elseif (is_file(DIR_IMAGE . $filename)){
			return true;
  		} else {
			return false;
		}
	}		
	
	public static function canDoOptimise() {
		if (static::$status === null) {
			static::$status = array(
					'optipng'   => shell_exec("optipng --version 2>/dev/null"),
					'jpegoptim' => shell_exec("jpegoptim --version 2>/dev/null"),
			);
		}
		
		return static::$status;
	}
	
}
