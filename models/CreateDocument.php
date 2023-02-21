<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

/**
 *  Copyright (c) Ascensio System SIA 2022. All rights reserved.
 *  http://www.onlyoffice.com
 */

namespace humhub\modules\onlyoffice\models;

use Yii;
use yii\base\Model;
use humhub\modules\onlyoffice\Module;
use humhub\modules\file\models\File;
use humhub\modules\cfiles\models\Folder;
use humhub\modules\cfiles\permissions\WriteAccess;

/**
 * Description of CreateDocument
 *
 * @author Luke
 */
class CreateDocument extends Model
{

    public $extension;
    public $fileName;
    public $fid;
    public $openFlag = true;
    private $localPath = [
        "az" => "az-Latn-AZ",
        "bg" => "bg-BG",
        "cs" => "cs-CZ",
        "de" => "de-DE",
        "el" => "el-GR",
        "en_GB" => "en-GB",
        "en-US" => "en-US",
        "es" => "es-ES",
        "fr" => "fr-FR",
        "it" => "it-IT",
        "ja" => "ja-JP",
        "ko" => "ko-KR",
        "lv" => "lv-LV",
        "nl" => "nl-NL",
        "pl" => "pl-PL",
        "pt_BR" => "pt-BR",
        "pt" => "pt-PT",
        "ru" => "ru-RU",
        "sk" => "sk-SK",
        "sv" => "sv-SE",
        "tr" => "tr-TR",
        "uk" => "uk-UA",
        "vi" => "vi-VN",
        "zh-CN" => "zh-CN",
        "zh-TW" => "zh-CN"
    ];

    public function rules()
    {
        return [
            ['fileName', 'required'],
            ['openFlag', 'boolean'],
            ['fid', 'string'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'openFlag' => Yii::t('OnlyofficeModule.base', 'Open the new document in the next step')
        ];
    }

    public function save()
    {
        if (empty($this->extension)) {
            throw new Exception("File extension cannot be empty");
        }

        $folder = Folder::findOne($this->fid);        
        if (!$folder->content->container->permissionManager->can(WriteAccess::class)) {
            return false;
        }

        if ($this->validate()) {
            $module = Yii::$app->getModule('onlyoffice');

            $source = $this->templatePath() . '/new.' . $this->extension;
            $newFile = $this->fileName . '.' . $this->extension;

            $mime = $module->mimes[$this->extension];

            $file = new File();
            $file->file_name = $newFile;
            $file->size = filesize($source);
            $file->mime_type = $mime;
            $file->save();
            $file->store->setContent(file_get_contents($source));

            return $file;
        }

        return false;
    }

    private function templatePath() {
        $module = Yii::$app->getModule('onlyoffice');
        $user = Yii::$app->user->getIdentity();

        $lang = ($user) && !empty($user->language) ? $user->language : Yii::$app->language;
        if (!array_key_exists($lang, $this->localPath)) {
            $lang = 'en-US';
        }

        return $module->getAssetPath() . '/templates/' . $this->localPath[$lang];
    }

}
