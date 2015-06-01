<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 25.05.2015
 */

namespace skeeks\cms\cmsWidgets\contentElements;

use skeeks\cms\base\Widget;
use skeeks\cms\base\WidgetRenderable;
use skeeks\cms\components\Cms;
use skeeks\cms\helpers\UrlHelper;
use skeeks\cms\models\CmsContentElement;
use skeeks\cms\models\Search;
use skeeks\cms\models\Tree;
use yii\data\ActiveDataProvider;
use yii\db\ActiveQuery;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Json;

/**
 * Class СontentElementsCmsWidget
 * @package skeeks\cms\cmsWidgets\contentElements
 */
class ContentElementsCmsWidget extends WidgetRenderable
{
    //Навигация
    public $enabledPaging               = CMS::BOOL_Y;
    public $enabledPjaxPagination       = CMS::BOOL_Y;

    public $pageSize                    = 10;
    public $pageParamName               = 'page';

    //Сортировка
    public $orderBy                     = "published_at";
    public $order                       = SORT_DESC;

    //Дополнительные настройки
    public $label                       = null;
    public $enabledSearchParams         = CMS::BOOL_Y;
    public $enabledCurrentTree          = CMS::BOOL_Y;
    public $enabledCurrentTreeChild     = CMS::BOOL_Y;

    public $tree_ids                    = [];

    //Условия для запроса
    public $limit                       = 0;
    public $active                      = "";
    public $createdBy                   = [];
    public $content_ids                 = [];

    static public function descriptorConfig()
    {
        return array_merge(parent::descriptorConfig(), [
            'name' => 'Элементы контента'
        ]);
    }

    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(),
        [
            'enabledPaging'             => 'Включить постраничную навигацию',
            'enabledPjaxPagination'     => 'Включить ajax навигацию',
            'pageParamName'             => 'Названия парамтера страниц, при постраничной навигации',
            'pageSize'                  => 'Количество записей на одной странице',

            'orderBy'                   => 'По какому параметру сортировать',
            'order'                     => 'Направление сортировки',

            'label'                     => 'Заголовок',
            'enabledSearchParams'       => 'Учитывать параметры из поисковый строки (для фильтрации)',

            'limit'                     => 'Максимальное количество записей в выборке (limit)',
            'active'                    => 'Учитывать флаг активности',
            'createdBy'                 => 'Выбор записей пользователей',
            'content_ids'               => 'Элементы контента',
            'enabledCurrentTree'        => 'При выборке учитывать текущий раздел (где показывается виджет)',
            'enabledCurrentTreeChild'   => 'При выборке учитывать текущий раздел (где показывается виджет) и все его подразделы',
            'tree_ids'                  => 'Показывать элементы привязанные к разделам',
        ]);
    }

    public function rules()
    {
        return ArrayHelper::merge(parent::rules(),
        [
            [['enabledPaging'], 'string'],
            [['enabledPjaxPagination'], 'string'],
            [['pageParamName'], 'string'],
            [['pageSize'], 'string'],
            [['orderBy'], 'string'],
            [['order'], 'integer'],
            [['label'], 'string'],
            [['label'], 'string'],
            [['enabledSearchParams'], 'string'],
            [['limit'], 'integer'],
            [['active'], 'string'],
            [['createdBy'], 'safe'],
            [['content_ids'], 'safe'],
            [['enabledCurrentTree'], 'string'],
            [['enabledCurrentTreeChild'], 'string'],
            [['tree_ids'], 'safe'],
        ]);
    }

    protected function _run()
    {
        $this->initDataProvider();

        if ($this->createdBy)
        {
            $this->dataProvider->query->andWhere(['created_by' => $this->createdBy]);
        }

        if ($this->active)
        {
            $this->dataProvider->query->andWhere(['active' => $this->active]);
        }

        if ($this->content_ids)
        {
            $this->dataProvider->query->andWhere(['content_id' => $this->content_ids]);
        }

        if ($this->limit)
        {
            $this->dataProvider->query->limit($limit);
        }


        if ($this->enabledCurrentTree == Cms::BOOL_Y)
        {
            $tree = \Yii::$app->cms->getCurrentTree();
            if ($tree)
            {
                $ids[] = $tree->id;
                /*if ($tree->hasChildrens() && $this->enabledCurrentTreeChild == Cms::BOOL_Y)
                {
                    if ($childrens = $tree->findChildrens()->all())
                    {
                        foreach ($childrens as $chidren)
                        {
                            $ids[] = $chidren->id;
                        }
                    }
                }*/

                $this->dataProvider->query->andWhere(['tree_id' => $ids]);
            }
        }

        return parent::_run();
    }

    /**
     * @var ActiveDataProvider
     */
    public $dataProvider    = null;

    /**
     * @var Search
     */
    public $search          = null;

    public function initDataProvider()
    {
        $this->search         = new Search(CmsContentElement::className());
        $this->dataProvider   = $this->search->getDataProvider();

        if ($this->enabledPaging == Cms::BOOL_Y)
        {
            $this->dataProvider->getPagination()->defaultPageSize   = $this->pageSize;
            $this->dataProvider->getPagination()->pageParam         = $this->pageParamName;
        } else
        {
            $this->dataProvider->pagination = false;
        }

        if ($this->orderBy)
        {
            $this->dataProvider->getSort()->defaultOrder =
            [
                $this->orderBy => (int) $this->order
            ];
        }

        return $this;
    }

}