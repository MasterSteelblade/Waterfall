# Waterfall Setup

This guide assumes your server runs the latest Ubuntu Server LTS. 

## 1. Add PPAs

    The production server uses the ondrej PPAs to ensure the latest versions of PHP and Apache are installed. 

    ```
    sudo apt-get install curl ca-certificates gnupg
    curl https://www.postgresql.org/media/keys/ACCC4CF8.asc | sudo apt-key add -
    sudo sh -c 'echo "deb http://apt.postgresql.org/pub/repos/apt $(lsb_release -cs)-pgdg main" > /etc/apt/sources.list.d/pgdg.list'
    sudo add-apt-repository ppa:ondrej/php
    sudo add-apt-repository ppa:ondrej/apache2
    ```

## 3. Install Apache, PHP, Postgres and Composer

    ```
    sudo apt install apache2 php8.0-pgsql php8.0-xml php8.0-curl php8.0-mysql php8.0-mbstring php8.0-fpm unzip postgresql-13
    ```

    Enable Apache Modules:

    ```
    sudo a2enmod headers actions proxy_http proxy_fcgi rewrite
    ```

    Install Composer:

    ```
    php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
    sudo php composer-setup.php --install-dir=/usr/local/bin --filename=composer
    ```

## 4. Create Apache configs and run LetsEncrypt

    Two vhosts need setting up. The first should be to the core domain, and the www subdomain as an alias, pointing to the html folder. The second should be a wildcard, pointing to the blogs subfolder. 

    Ensure htaccess is enabled, and then use Lets Encrypt to generate an SSL certificate. Substitute your domain below, and follow the instructions.

    Lets Encrypt:
    ```
    sudo certbot --manual -d waterfall.social -d *.waterfall.social --preferred-challenges=dns
    ```

## 5. Configure .env file

Copy .env.dist to .env, and fill in the variables.

# Installing Raven

Raven is Waterfall's transcode manager, and is required for avatar, image, art, video, and audio uploading. It was designed to be run on a separate server with a high amount of available storage. 

This guide again assumes an Ubuntu LTS server, and assumes it is a fresh install, separate from the Waterfall core instance, and mimics the process used on the production site.

# Prepare new content node

1. Install packages
    ```sudo apt install apache2 zfsutils python3-pip ffmpeg zfs-dkms```
2. Create raven user
    ```sudo adduser --home /home/raven --gecos "Raven User" --shell /bin/bash --disabled-password --disabled-login raven```

3. Switch to Raven user and clone the git repo.

4. Switch to root and Create ZFS. The type used will be variable depending on disk count available.

For RAIDZ1 `zpool create -m /var/www/content contentstore raidz1 /dev/sda /dev/sdb ... /dev/sdX`

For mirror `zpool create -m /var/www/content contentstore mirror /dev/sda /dev/sdb`

RAIDZ levels are preferred. RAIDZ2 is ideal, RAIDZ3 for maximum redundancy. On a development server, It's fine to use stripe instead with a single drive, as any data there should be considered ephemeral.

5. Verify pool status with `zpool status`. Output should resemble the following.

```
pool: contentstore
state: ONLINE
scan: none requested

config:

        NAME            STATE   READ WRITE CKSUM
        contentstore    ONLINE     0     0     0
            raidz1-0
                xvdc
                xvde
```
etc.

#### Note
Ideally we have a ZIL *and* cache. These can be partitions, but it's recommended to use a full drive. 

If you can only use one, add a ZIL - system RAM will act as an ARC, and having L2ARC will only matter on large sites. Note that performance will suffer greatly on sites unequipped for the IO load. Regardless, ZIL and cache should always be SSDs, preferably NVMe. 
 
6. Add the cache with `zpool add contentstore cache /dev/whatever`

7. OPTIONAL: Add  ZIL. Only a small drive is required. Same command as above, but log instead of cache. This is only a would-be-nice thing - we're unlikely to be storing anything extremely critical on a content node, and if ther'es a bad upload/transcode a user is likely to just delete it and re-upload.

8. Set compression - `zfs set compression=lz4 contentstore`

Experiment frequently with what the best compression option is.

9. Add datasets and set mountpoints. 
    ```
    zfs set mountpoint=-/var/www/content contentstore
    zfs create -o mountpoint=/home/raven/Raven/tmp contentstore/raventmp
    zfs create -o mountpoint=/var/www/content/images contentstore/images
    zfs create -o mountpoint=/var/www/content/audio contentstore/audio
    zfs create -o mountpoint=/var/www/content/videos contentstore/videos
    ```
    Setting contentstore's mountpoint is important. Otherwise, the free space checking won't work.

10. Set record sizes. 
    ```
    zfs set recordsize=1M contentstore/videos
    zfs set recordsize=128K contentstore/audio
    zfs set recordsize=64K contentstore/images
    zfs set recordsize=128K contentstore/raventmp
    ```

11. Set permissions.
    ```
    chown -R raven:www-data /var/www
    chown -R raven:raven /home/raven/
    ```

12. Reboot and check permissions and mounts are still intactusing zfs list and ls -l where appropriate

13. Install requirements as sudo. 

```sudo pip3 install -r requirements.txt```

14. Switch to Raven user and run initial setup.

15. Create Service in /etc/systemd/system/raven.service

```
[Unit]
Description="Raven"
After=network.target

[Service]
User=raven
WorkingDirectory=/home/raven/Raven
ExecStart=python3 raven.py
Restart=always

[Install]
WantedBy=multi-user.target
```

```
sudo systemctl daemon-reload

