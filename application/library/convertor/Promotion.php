<?php

/**
 * 促销转换器类
 */
class Convertor_Promotion extends Convertor_Base {

    public function __construct() {
        parent::__construct();
    }

    /**
     * 促销列表
     *
     * @param array $promotionList
     *            促销列表
     * @param array $tagList
     *            促销标签列表
     * @param int $promotionCount
     *            促销标签总数
     * @param array $param
     *            扩展参数
     * @return array
     */
    public function getPromotionListConvertor($promotionList, $tagList, $promotionCount, $param) {
        $tagListNew = array();
        foreach ($tagList as $tag) {
            $tagListNew [$tag ['id']] = $this->handlerMultiLang('title', $tag);
        }
        $data = array('list' => array());
        foreach ($promotionList as $news) {
            $newTemp = array();
            $newTemp ['id'] = $news ['id'];
            $newTemp ['title'] = $this->handlerMultiLang('title', $news);
            $newTemp ['article'] = Enum_Img::getPathByKeyAndType($this->handlerMultiLang('article', $news));
            $newTemp ['tagId'] = $news ['tagid'];
            $newTemp ['tagName'] = $tagListNew [$newTemp ['tagId']];
            $newTemp ['pdf'] = $news['pdf'] ? Enum_Img::getPathByKeyAndType($news['pdf']) : '';
            $newTemp ['video'] = $news['video'] ? Enum_Img::getPathByKeyAndType($news['video']) : '';
            $newTemp ['pic'] = $news['pic'] ? Enum_Img::getPathByKeyAndType($news['pic'], Enum_Img::PIC_TYPE_KEY_WIDTH750) : '';
            $newTemp ['createtime'] = $news ['createtime'];
            $newTemp ['updatetime'] = $news ['updatetime'];
            $newTemp ['url'] = $news ['url'];

            $newTemp ['homeShow'] = $news ['homeShow'];
            $newTemp ['startTime'] = date('Y-m-d H:i:s', $news ['startTime']);
            $newTemp ['endTime'] = date('Y-m-d H:i:s', $news ['endTime']);

            $data ['list'] [] = $newTemp;
        }
        $data ['total'] = $promotionCount;
        $data ['page'] = $param ['page'];
        $data ['limit'] = $param ['limit'];
        $data ['nextPage'] = Util_Tools::getNextPage($data ['page'], $data ['limit'], $data ['total']);
        return $data;
    }

    /**
     * 后台促销列表
     *
     * @param array $promotionList
     *            促销列表
     * @param array $tagList
     *            促销标签列表
     * @param int $promotionCount
     *            促销标签总数
     * @param array $param
     *            扩展参数
     * @return array
     */
    public function getAdminPromotionListConvertor($promotionList, $tagList, $promotionCount, $param) {
        $tagListNew = array();
        foreach ($tagList as $tag) {
            $tagListNew [$tag ['id']] ['titleLang1'] = $tag ['title_lang1'];
            $tagListNew [$tag ['id']] ['titleLang2'] = $tag ['title_lang2'];
            $tagListNew [$tag ['id']] ['titleLang3'] = $tag ['title_lang3'];
        }
        $data = array('list' => array());
        foreach ($promotionList as $news) {
            $newTemp = array();
            $newTemp ['id'] = $news ['id'];
            $newTemp ['title_lang1'] = $news ['title_lang1'];
            $newTemp ['title_lang2'] = $news ['title_lang2'];
            $newTemp ['title_lang3'] = $news ['title_lang3'];
            $newTemp ['article_lang1'] = $news ['article_lang1'];
            $newTemp ['article_lang2'] = $news ['article_lang2'];
            $newTemp ['article_lang3'] = $news ['article_lang3'];
            $newTemp ['tagId'] = $news ['tagid'];
            $newTemp ['status'] = $news ['status'];
            $newTemp ['tagName_lang1'] = $tagListNew [$newTemp ['tagId']] ['titleLang1'];
            $newTemp ['tagName_lang2'] = $tagListNew [$newTemp ['tagId']] ['titleLang2'];
            $newTemp ['tagName_lang3'] = $tagListNew [$newTemp ['tagId']] ['titleLang3'];
            $newTemp['enable_lang1'] = $news['enable_lang1'];
            $newTemp['enable_lang2'] = $news['enable_lang2'];
            $newTemp['enable_lang3'] = $news['enable_lang3'];
            $newTemp ['sort'] = $news ['sort'];
            $newTemp ['pdf'] = $news ['pdf'];
            $newTemp ['video'] = $news ['video'];
            $newTemp ['pic'] = $news ['pic'];
            $newTemp ['createTime'] = $news ['createtime'];
            $newTemp ['updateTime'] = $news ['updatetime'];
            $newTemp ['url'] = $news ['url'];
            
            $newTemp ['homeShow'] = $news ['homeShow'];
            $newTemp ['startTime'] = date('Y-m-d H:i:s', $news ['startTime']);
            $newTemp ['endTime'] = date('Y-m-d H:i:s', $news ['endTime']);

            $data ['list'] [] = $newTemp;
        }
        $data ['total'] = $promotionCount;
        $data ['page'] = $param ['page'];
        $data ['limit'] = $param ['limit'];
        $data ['nextPage'] = Util_Tools::getNextPage($data ['page'], $data ['limit'], $data ['total']);
        return $data;
    }

    /**
     * 促销详情
     *
     * @param array $promotionList
     *            促销详情
     * @return array
     */
    public function getPromotionDetailConvertor($list) {
        $data = array();
        $data ['id'] = $list ['id'];
        $data ['title'] = $this->handlerMultiLang('title', $list);
        $data ['article'] = Enum_Img::getPathByKeyAndType($this->handlerMultiLang('article', $list));
        $data ['createTime'] = $list ['createtime'];
        $data ['updateTime'] = $list ['updatetime'];
        $data ['url'] = $list ['url'];
        $data ['tagId'] = $list ['tagid'];
        return $data;
    }

    /**
     * 后台促销详情
     *
     * @param array $promotionList
     *            促销详情
     * @return array
     */
    public function getAdminPromotionDetailConvertor($list) {
        $data = array();
        $data ['id'] = $list ['id'];
        $data ['title_lang1'] = $list ['title_lang1'];
        $data ['title_lang2'] = $list ['title_lang2'];
        $data ['title_lang3'] = $list ['title_lang3'];
        $data ['article_lang1'] = $list ['article_lang1'];
        $data ['article_lang2'] = $list ['article_lang2'];
        $data ['article_lang3'] = $list ['article_lang3'];
        $data ['tagId'] = $list ['tagid'];
        $data ['createTime'] = $list ['createtime'];
        $data ['updateTime'] = $list ['updatetime'];
        $data ['url'] = $list ['url'];
        return $data;
    }
}
