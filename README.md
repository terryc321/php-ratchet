# php-ratchet

http://socketo.me/

http://socketo.me/docs/install

install php
```
sudo apt install php-cli
```

install composer

https://getcomposer.org/download/

```
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
php -r "if (hash_file('sha384', 'composer-setup.php') === 'dac665fdc30fdd8ec78b38b9800061b4150413ff2e3b6f88543c636f7cd84f6db9189d43a81e5503cda447da73c7e5b6') { echo 'Installer verified'.PHP_EOL; } else { echo 'Installer corrupt'.PHP_EOL; unlink('composer-setup.php'); exit(1); }"
php composer-setup.php
php -r "unlink('composer-setup.php');"
```

```
sudo mv composer.phar /usr/local/bin/composer
```

composer.phar is now /usr/local/bin/composer 

```
php ~/composer.phar require cboden/ratchet
becomes 
php /usr/local/bin/composer require cboden/ratchet
```

now ready to start using ratchet
```
<?php
    require __DIR__ . '/vendor/autoload.php';
```

lets make a project directory
```
GITHUB_USER='terryc321'
PROJECT='php-ratchet'
mkdir -pv ~/code/$PROJECT
cd ~/code/$PROJECT

echo "# symmetrical-potato" >> README.md
git init
git add README.md
git commit -m "first commit"
git branch -M main
git remote add origin git@github.com:$GITHUB_USER/$PROJECT.git
git push -u origin main
```



