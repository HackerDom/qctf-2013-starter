qctf-2013
=========

web
------------
- web100

http://90.157.117.82:8088/web100/

Ну для первого таска, наверно, нужна инъекция, но не слишком банальная. Как решается: **1)** смотрим url картинок, видим *image/1* и *image/2* **2)** пытаемся сделать что-то типа *image/3*, получаем error **3)** *image/'* - MySQL error **4)** пробуем union: *image/3 union select 1* - получаем file not found [1] **5)** *image/3 union select 'index.php'* - видим исходный код index.php, но флага там нет **6)** вспоминаем, что запущен apache, смотрим файл .htaccess: *image/3 union select '.htaccess'*, получаем флаг

Можно немного усложнить: всегда выводить error (без MySQL error)