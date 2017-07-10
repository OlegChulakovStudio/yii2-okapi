# Yii2 component for OK API
[Russian](README.ru.md)

## Install by composer
composer require oleg-chulakov-studio/yii2-okapi
## Or add this code into require section of your composer.json and then call composer update in console
"oleg-chulakov-studio/yii2-okapi": "*"
## Usage
In configuration file do
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
 Use as simple component
 ```php
<?php
$responseData = Yii::$app->YiiVk->request('search.tagContents', [
     'count' => 25,
     'query' => $this->tagSearch,
     'filter' => '{ "types" : ["USER_TOPIC", "USER_PHOTO","GROUP_PHOTO", "GROUP_TOPIC"]}',
     'fields' => 'group_photo.ID,group_photo.ALBUM_ID,group_photo.GROUP_ID,group_photo.PIC1024MAX',
 ]);
?>
 ```
