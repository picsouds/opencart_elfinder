

# elFinder- Opencart - Minio S3 - Plugin S3fs

Exemple de remplacement du FileManager Opencart standard (upload image + summernote) par [Elfinder](https://github.com/Studio-42/elFinder)  

# New Features !

- Optimisation admin/catalog ModelToolImage.php avec jpegoptim / optipng / cwebp
- Rajout format image Webp system/library/image.php

### Core

* Opencart 3.0.3.7 standard (php:7.4-fpm-alpine / mysql:8.0)
* Elfinder 2.1.59 standard 
* Testé avec Minio RELEASE.2021-04-06T23-11-00Z + montage [s3fs-volume-plugin](https://github.com/marcelo-ochoa/docker-volume-plugins/tree/master/s3fs-volume-plugin) via docker plugin
* Docker CE - version 20.10.6, build 370c289

### Installation (hors vqmod/ocmod)

* git clone https://github.com/picsouds/opencart_elfinder.git 
* Copier l'ensemble du répertoire upload en écrasant les fichiers 
* Donner le droit d'accès admin 
    ```
	    extension/module/elfinder
	    extension/module/elfinderconnector
    ```
* Installer les 2 extensions (activation par défaut lors de l'installation)
* Rafraîchir le cache (cache opencart / cache navigateur)

### Configuration

+ Configuration du [connector elFinder](https://github.com/Studio-42/elFinder/wiki/Connector-configuration-options) dans upload/admin/controller/extension/module/elfinderconnector.php 
+ Si "HTTP Basic Authentication" n'est pas utilisé via $_SERVER['PHP_AUTH_USER'], supprimer la ligne
    ```
    'attributes'	=> array( array( 'pattern'=>'/.+/', 'hidden'=>(isset($_SERVER['PHP_AUTH_USER']))? false     : true ))
    ```
+ Configuration du [client elFinder](https://github.com/Studio-42/elFinder/wiki/Client-configuration-options) dans admin/view/template/extension/module/elfinder.twig 

### Exemple configuration (docker plugin) avec [play.minio.io](https://play.minio.io:9000/minio/login)  

    docker plugin install --alias s3fs mochoa/s3fs-volume-plugin --grant-all-permissions --disable
    docker plugin set s3fs AWSACCESSKEYID=Q3AM3UQ867SPQQA43P2F
    docker plugin set s3fs AWSSECRETACCESSKEY=zuf+tfteSlswRu7BJ86wekitnifILbZam1KYY3TG
    docker plugin enable s3fs

      
### Exemple docker-compose.yml

	volumes:
	  shared:
	    driver: s3fs:latest
	    driver_opts:
	    s3fsopts: nonempty,allow_other,use_path_request_style,url=https://play.minio.io:9000,uid=$UID,gid=$GID
	    name: "bucket_de_play.minio.io"   
    
Puis définir un point de montage entre le volume *shared* et le répertoire image opencart 

    php-fpm:    
      ...        
      volumes:
	     - type: volume
	       source: shared
	       target: /var/www/opencart/upload/image/minio
	    ...
    nginx:
	  ...
	  volumes:
	     - type: volume
	       source: shared
	       target: /var/www/opencart/upload/image/minio
      ...

**Free Software, Hell Yeah!**
