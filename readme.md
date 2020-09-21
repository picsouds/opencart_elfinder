# elFinder- Opencart - Minio S3 

Exemple de remplacement du FileManager Opencart standard (upload image+summernote) par [Elfinder](https://github.com/Studio-42/elFinder)  

# New Features !

- Optimisation admin/catalog ModelToolImage avec jpegoptim / optipng / cwebp
- Rajout format image Webp dans system/library/image
- TUI image editor 3.5.2
- Suppression elfinder-flysystem-driver S3 (plus performant via montage [s3fs](https://github.com/s3fs-fuse/s3fs-fuse))

### Core

* Opencart 3.0.3 standard 
* Elfinder 2.1.56 standard 
* Testé avec Minio RELEASE.2019-07-24T02-02-23Z + mount Filesystem S3FS avec [Rexray](https://rexray.readthedocs.io/en/stable/) via [Service (Docker)](https://github.com/rexray/rexray#runtime---service-docker)

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
      
### Exemple configuration RexRay avec [play.minio.io](https://play.minio.io:9000/minio/login) 

	libstorage:
		service: s3fs
		integration:
		  volume:
			operations:
			  mount:
				rootPath: /
	s3fs:
	  accessKey: Q3AM3UQ867SPQQA43P2F
	  secretKey: zuf+tfteSlswRu7BJ86wekitnifILbZam1KYY3TG
	  endpoint: https://play.minio.io:9000  
	  region: us-east-1
      disablePathStyle: false 
      options:
      - url=https://play.minio.io:9000
      - use_path_request_style
      - nonempty
      - allow_other  
      - umask=000    

+ Définir un point de montage entre le /var/lib/rexray/volumes/*bucket* et /var/www/opencart/upload/image/*bucket*
      
### Todos

 - Ocmod
 - Ckeditor (cf. futur release opencart)

**Free Software, Hell Yeah!**
