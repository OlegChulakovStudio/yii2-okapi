# Yii2 компонент для OK API
[English](README.md)

## Установка через composer
composer require oleg-chulakov-studio/yii2-okapi
## Или добавьте эту строку в секцию require файла composer.json и выполните команду composer update в консоли
"oleg-chulakov-studio/yii2-okapi": "*"
## Использование
В конфигурационном файле пропишите:
```php
<?php
  'components'  =>  [
        'OkApi' => [
            'class' => \OlegChulakovStudio\okapi\YiiOkComponent::className(),
            'applicationId' => '',
            'applicationKey' => '',
            'applicationSecretKey' => '',
            'accessToken' => ''
        ],
  ]
 ?>
 ```
Простой пример использования
 ```php
<?php
$responseData = Yii::$app->YiiVk->request('search.tagContents', [
     'count' => 25,
     'query' => $this->tagSearch,
     'filter' => '{ "types" : ["USER_TOPIC", "USER_PHOTO","GROUP_PHOTO", "GROUP_TOPIC"]}',
     'fields' => 'group_photo.ID,group_photo.ALBUM_ID,group_photo.GROUP_ID,group_photo.PIC1024MAX',
 ]);
?>
