<?php

namespace TipsForBitrix;

use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Fields\BooleanField;
use Bitrix\Main\ORM\Fields\DatetimeField;
use Bitrix\Main\ORM\Fields\IntegerField;
use Bitrix\Main\ORM\Fields\StringField;
use Bitrix\Main\ORM\Fields\TextField;
use Bitrix\Main\Type\DateTime;

class NoteTable extends DataManager
{
    public static function getTableName()
    {
        return 'b_tips_for_bitrix_note';
    }

    public static function getMap()
    {
        return array(
            new IntegerField(
                'ID',
                array(
                    'primary' => true,
                    'autocomplete' => true,
                )
            ),
            new BooleanField(
                'ACTIVE',
                array(
                    'values' => array('N', 'Y'),
                    'default_value' => 'Y',
                )
            ),
            new StringField(
                'AREA',
                array(
                    'required' => true,
                    'size' => 10,
                )
            ),
            new StringField(
                'PAGE_URL',
                array(
                    'required' => true,
                    'size' => 255,
                )
            ),
            new TextField(
                'NOTE_TEXT',
                array(
                    'required' => true,
                )
            ),
            new StringField(
                'STATUS',
                array(
                    'required' => true,
                    'size' => 32,
                    'default_value' => 'default',
                )
            ),
            new StringField(
                'COLOR',
                array(
                    'required' => true,
                    'size' => 32,
                    'default_value' => 'sand',
                )
            ),
            new IntegerField(
                'SORT',
                array(
                    'default_value' => 500,
                )
            ),
            new IntegerField('CREATED_BY'),
            new DatetimeField(
                'TIMESTAMP_X',
                array(
                    'default_value' => new DateTime(),
                )
            ),
            new DatetimeField(
                'DATE_CREATE',
                array(
                    'default_value' => new DateTime(),
                )
            ),
        );
    }
}
