# elFinder- Opencart - Minio S3 

Exemple de remplacement du FileManager Opencart standard (upload image+summernote) par [Elfinder](https://github.com/Studio-42/elFinder)  

# New Features !

- Optimisation admin/catalog ModelToolImage avec jpegoptim et optipng
- TUI image editor 3.5.2
- Suppression elfinder-flysystem-driver S3 (plus performant via montage [s3fs](https://github.com/s3fs-fuse/s3fs-fuse))

### Core

* Opencart 3.0.3 standard 
* Elfinder 2.1.49 standard 
* Testé avec Minio RELEASE.2019-07-24T02-02-23Z + mount Filesystem S3FS avec [Rexray](https://rexray.io/) via [DockerPlugin](https://github.com/rexray/rexray#runtime---docker-plugin)

### Installation (hors vqmod/ocmod)

* Copier l'ensemble du répertoire Upload en écrasant les fichiers 
* Donner le droit d'accès admin 
    ```
    extension/module/elfinder
    extension/module/elfinderconnector
    ```      
* Installer les 2 extensions (Le statut est activé par défaut lors de l'installation)
* Rafraichir le cache (cache opencart / cache navigateur)

### Configuration

+ Configuration du [connector elFinder](https://github.com/Studio-42/elFinder/wiki/Connector-configuration-options) dans upload/admin/controller/extension/module/elfinderconnector.php 
+ Si "HTTP Basic Authentication" n'est pas setté via $_SERVER['PHP_AUTH_USER'], supprimer la ligne
    ```
    'attributes'	=> array( array( 'pattern'=>'/.+/', 'hidden'=>(isset($_SERVER['PHP_AUTH_USER']))? false     : true ))
    ```
+ Configuration du [client elFinder](https://github.com/Studio-42/elFinder/wiki/Client-configuration-options) dans admin/view/template/extension/module/elfinder.twig 
      
### Configuration Minio avec RexRay

	libstorage:
		service: s3fs
		integration:
		  volume:
			operations:
			  mount:
				rootPath: /
	s3fs:
	  accessKey: xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
	  secretKey: xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
	  endpoint: http://xxx.xxx.xxx.xxx:xxxx/
	  region: us-east-1
      disablePathStyle: false 
      options:
      - url=http://xxx.xxx.xxx.xxx:xxxx/
      - use_path_request_style
      - nonempty
      - allow_other  
      - umask=000    

+ Définir un point de montage entre le /var/lib/rexray/volumes/*bucket* et /var/www/opencart/upload/image/*bucket*
      
### Todos

 - Ocmod
 - Ckeditor (cf. futur release opencart)

**Free Software, Hell Yeah!**
