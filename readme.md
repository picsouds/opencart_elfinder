# elFinder- Opencart - Minio S3 

Exemple de remplacement du FileManager Opencart standard (upload image+summernote) par [Elfinder](https://github.com/Studio-42/elFinder)  

# New Features !

- Ajout FULL support [minio S3](https://www.minio.io/) ***pour les images (Elfinder / Images admin et catalog)*** avec [CachedAdapter](https://github.com/thephpleague/flysystem-cached-adapter)
- Optimisation admin/catalog ModelToolImage avec jpegoptim et optipng
- TUI image editor 3.5.2

### Core

* Opencart 3.0.3 standard 
* Elfinder 2.1.48 standard complété de :
  * composer require barryvdh/elfinder-flysystem-driver (sans function resize)
  * composer require league/flysystem-aws-s3-v3
* Minio RELEASE.2019-03-06T22-47-10Z 

### Installation (hors vqmod/ocmod)

* Copier l'ensemble du répertoire Upload en écrasant les fichiers 
* Donner le droit d'accès admin 
    ```
    extension/module/elfinder
    extension/module/elfinderconnector
    ```      
* Rafraichir le cache (cache opencart / cache navigateur)

### Configuration

+ Configuration du [connector elFinder](https://github.com/Studio-42/elFinder/wiki/Connector-configuration-options) dans upload/admin/controller/extension/module/elfinderconnector.php 
+ Configuration du client S3 minio dans upload/admin/controller/extension/module/elfinderconnector.php 
	```
	$config_minio = [
				'key' => 'xxxxxxxxxxxxxxxxxxxxxxxxx',
				'secret' => 'xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx',
				'region' => 'us-east-1',  
				'bucket' => 'xxxxxxxxxxxxxxx',     					
				'endpoint' => 'http://xxx.xxx.xxx.xxx:xxxx/'	
		];
	```
+ Si "HTTP Basic Authentication" n'est pas setté via $_SERVER['PHP_AUTH_USER'], supprimer la ligne
    ```
    'attributes'	=> array( array( 'pattern'=>'/.+/', 'hidden'=>(isset($_SERVER['PHP_AUTH_USER']))? false     : true ))
    ```
+ Configuration du [client elFinder](https://github.com/Studio-42/elFinder/wiki/Client-configuration-options) dans admin/view/template/extension/module/elfinder.twig 
      
### Todos

 - Ocmod
 - Panel extension admin pour gérer minio
 - Ckeditor (cf. futur release opencart)


**Free Software, Hell Yeah!**
