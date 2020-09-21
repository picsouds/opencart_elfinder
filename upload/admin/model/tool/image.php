<?php

class ModelToolImage extends Model {
	
	private static $status = null;
	
	public function resize($filename, $width, $height) {
		
		if (!is_file(DIR_IMAGE . $filename) || substr(str_replace('\\', '/', realpath(DIR_IMAGE . $filename)), 0, strlen(DIR_IMAGE)) != str_replace('\\', '/', DIR_IMAGE)) {			
			return;
		}
	
		$extension = pathinfo($filename, PATHINFO_EXTENSION);
	
		$image_old = $filename;
		$image_new = 'cache/' . utf8_substr($filename, 0, utf8_strrpos($filename, '.')) . '-' . $width . 'x' . $height . '.' . $extension;
	
		if (!is_file(DIR_IMAGE . $image_new) || (filemtime(DIR_IMAGE . $image_old) > filemtime(DIR_IMAGE . $image_new))) {
			list($width_orig, $height_orig, $image_type) = getimagesize(DIR_IMAGE . $image_old);
				 
			if (!in_array($image_type, array(IMAGETYPE_PNG, IMAGETYPE_JPEG, IMAGETYPE_GIF, IMAGETYPE_WEBP))) { 
				if ($this->request->server['HTTPS']) {
                    return HTTPS_CATALOG . 'image/' . $image_old;
                } else {
                    return HTTP_CATALOG . 'image/' . $image_old;
		}
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
			if (in_array($image_type, array(IMAGETYPE_JPEG)) && (static::canDoOptimise()['jpegoptim'])) {
				$img_log->write(shell_exec("jpegoptim --max=85 -strip-all --all-progressive " . $optimized_image_path ."| tr '\n' ' '"));
			} elseif (in_array($image_type, array(IMAGETYPE_PNG)) && (static::canDoOptimise()['optipng'])) {
				$img_log->write(shell_exec("optipng -strip all -i0 -o4 ". $optimized_image_path ." 2>&1 | sed -n '/Processing/p;/Output file size/p' | tr '\n' ' '"));
			} elseif (in_array($image_type, array(IMAGETYPE_WEBP)) && (static::canDoOptimise()['cwebp'])) {
				$img_log->write(shell_exec("cwebp -q 85 ". $optimized_image_path ." 2>&1 | sed -n '/File/,/Output/p' | tr '\n' ' '"));
			}
				
		}

		if ($this->request->server['HTTPS']) {
			return HTTPS_CATALOG . 'image/' . $image_new;
		} else {
			return HTTP_CATALOG . 'image/' . $image_new;
		}
	}
		
	public static function canDoOptimise() {
		if (static::$status === null) {
			static::$status = array(
					'optipng'   => shell_exec("optipng --version 2>/dev/null"),
					'jpegoptim' => shell_exec("jpegoptim --version 2>/dev/null"),
					'cwebp' => shell_exec("cwebp -version 2>/dev/null")
			);
		}
		
		return static::$status;
	}	
	
}
