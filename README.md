# image-resizer
Картинки задаем так
/thumbs/360/216/assets/images/products/439/kuhnya-chernaya-108-1.jpg
 - /thumbs - префикс
 - /360/216 - размер
 - /assets/images/products/439/kuhnya-chernaya-108-1.jpg - оригинальный путь
 
Для модикса в index.php просто указываем вначале
<?php
if(strpos($_SERVER['REQUEST_URI'],'/thumbs')==0 && strpos($_SERVER['REQUEST_URI'],'/thumbs')!==false){
    include 'resize.php';
    exit();
}
