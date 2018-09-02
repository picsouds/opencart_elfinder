# elFinder - Opencart 3

Exemple de remplacement du FileManager Opencart standard (upload image+summernote) par [Elfinder](https://github.com/Studio-42/elFinder)

### Installation (hors vqmod/ocmod)

+ Copier l'ensemble du répertoire Upload en écrasant les fichiers (common.js et opencart.js)
+ Donner le droit d'accès admin   
    ```
        common/elfinder
        common/elfinderconnector 
        extension/module/elfinder
        extension/module/elfinderconnector
    ``` 
+ Rafraichir le cache (cache opencart / cache navigateur)
+ Installer (ou pas) dans extension/module "Elfinder File manager"

### Configuration

+ Configuration du [connector elFinder](https://github.com/Studio-42/elFinder/wiki/Connector-configuration-options) dans upload/admin/controller/common/elfinderconnector.php 
+ Si "HTTP Basic Authentication" n'est pas setté, supprimer la ligne
    ```
    'attributes'	=> array( array( 'pattern'=>'/.+/', 'hidden'=>(isset($_SERVER['PHP_AUTH_USER']))? false     : true ))
    ```
+ Configuration du [client elFinder](https://github.com/Studio-42/elFinder/wiki/Client-configuration-options) dans upload/admin/view/template/common/elfinder.twig
    
    **_idem pour la partie extension_** 

### Todos

 - Ocmod
 - Ckeditor (cf. béta opencart)
 - modification image

License
----

MIT


**Free Software, Hell Yeah!**
