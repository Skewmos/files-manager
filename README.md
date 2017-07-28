[![GitHub license](https://img.shields.io/badge/license-New%20BSD-blue.svg)](https://github.com/Fukotaku/files-manager/blob/master/LICENSE)
# files-manager

files-manager est une application web développé en php qui permet de uploader/télécharger vos fichiers sur le serveur en http. L'application dispose de divers fonctionnalités, comme le visionnage de vidéo et le partage de dossiers entre utilisateur ect...

Pour toutes contribution sur github, merci de lire le document [CONTRIBUTING.md](https://github.com/Fukotaku/files-manager/blob/master/CONTRIBUTING.md).


## Objectifs

- [x] Comptes utilisateur.
- [x] Répertoires utilisateur.
- [x] Upload de fichiers dans les répertoires utilisateur.
- [x] Bar de progression d'upload.
- [x] Téléchargement des fichiers (dossier utilisateur).
- [ ] Téléchargement des fichiers (dossier serveur).
- [ ] Visualisation des fichiers type (vidéo/image/musique/pdf...).
- [ ] Partage de répertoires du serveur.
- [ ] Partage de dossier entre utilisateur.

- [x] Panel d'administration.
- [x] Mise en place de logs au panel admin.
- [x] Ajout/Edition/Suppression des comptes utilisateur au panel admin.
- [ ] Ajout/Edition/Suppression de répertoires du serveur au panel admin.
- [x] Modification des paramètres d'application au panel admin.

- [ ] Mise en place d'une pagination pour les listes.

- [x] Système d'installation de l'application.
- [ ] Système de mise à jours de l'application.


## Pre-requis/configuration

- php5.6+
  - extension pdo
  - extension mbstring
  - php.ini
    - `session.upload.progress = On`
    - `session.upload_progress.cleanup = Off`
    - `file_uploads = On`
    - `upload_tmp_dir = tmp`
    - `post_max_size = (your_max_upload)M`
    - `upload_max_filesize = (your_max_upload)M`

- nginx
  - `client_max_body_size (your_max_upload)m;`


## Librairies/outils

Projet basé sur le template [Fukotaku/fufu](https://github.com/Fukotaku/fufu/)


## Installation

Via git

``` bash
$ git clone https://github.com/Fukotaku/files-manager
```

Via composer

``` bash
$ composer install
```

Autoriser les dossiers `cache`, `public` et `public/directory` à l'écriture (chmod 775).


#### Si vous utilisez apache :

Pas de configuration particulière.


#### Si vous utilisez nginx :

```
server {
    listen      80;
    server_name domaine.tld;

    root /path/files-manager/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ ^/.+\.php(/|$) {
        try_files $uri /index.php = 404;
        fastcgi_split_path_info ^(.+\.php)(/.+)$;

        fastcgi_pass unix:/var/run/php/php7.0-fpm.sock;
        # for php5 => fastcgi_pass unix:/var/run/php5-fpm.sock;

        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }

}
```

Pour finir, rendez-vous à l'url de votre application `http://domaine.tld/install` pour accéder à la page d'installation et suivez les étapes.
