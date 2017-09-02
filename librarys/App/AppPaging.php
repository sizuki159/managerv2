<?php

    namespace Librarys\App;

    if (defined('LOADED') == false)
        exit;

    final class AppPaging
    {

        private $itemNums;
        private $urlDefault;
        private $urlBegin;
        private $urlEnd;

        const ITEM_NUMS = 7;

        /**
         * [__construct Init paging object]
         * @param [string] $urlDefault [Url default of page current is 1]
         * @param [string] $urlBegin   [Url begin first number page]
         * @param [string] $urlEnd     [Url end last number page]
         * @param [string] $itemNums   [Value of items on a page]
         */
        public function __construct($urlDefault = null, $urlBegin = null, $urlEnd = null, $itemNums = self::ITEM_NUMS)
        {
            $this->setUrlDefault($urlDefault);
            $this->setUrlBegin($urlBegin);
            $this->setUrlEnd($urlEnd);
            $this->setItemNums($itemNums);
        }

        /**
         * [setUrlDefault Set value url default for page]
         * @param [string] $urlDefault [Can write null value]
         */
        public function setUrlDefault($urlDefault)
        {
            $this->urlDefault = $urlDefault;
        }

        /**
         * [getUrlDefault Return url default]
         * @return [string] [Return url default]
         */
        public function getUrlDefault()
        {
            return $this->urlDefault;
        }

        /**
         * [setUrlBegin Set value url begin for page]
         * @param [string] $urlBegin [Can write null value]
         */
        public function setUrlBegin($urlBegin)
        {
            $this->urlBegin = $urlBegin;
        }

        /**
         * [getUrlBegin Return url begin]
         * @return [string] [Return url begin]
         */
        public function getUrlBegin()
        {
            return $this->urlBegin;
        }

        /**
         * [setUrlEnd Set value url end for page]
         * @param [string] $urlEnd [Can write null value]
         */
        public function setUrlEnd($urlEnd)
        {
            $this->urlEnd = $urlEnd;
        }

        /**
         * [getUrlEnd Return url end]
         * @return [string] [Return url end]
         */
        public function getUrlEnd()
        {
            return $this->urlEnd;
        }

        /**
         * [setItemNums Parameters to set the number of items on a page]
         * @param [integer] $itemNums [Parameters to set the number of items on a page]
         */
        public function setItemNums($itemNums)
        {
            if ($itemNums == null || $itemNums < 1)
                $itemNums = self::ITEM_NUMS;

            if ($itemNums % 2 === 0)
                $itemNums++;

            $this->itemNums = $itemNums;
        }

        /**
         * [getItemNums Return the number if items on a page]
         * @return [integer] [Returns the number of items on a page]
         */
        public function getItemNums()
        {
            return $this->itemNums;
        }

        /**
         * [display description]
         * @return [string] [Return html display of page]
         */
        public function display($current, $total)
        {
            $buffer = '<ul class="paging">';
            $between = $this->itemNums - 2;

            if ($total <= $this->itemNums) {
                for ($i = 1; $i <= $total; ++$i) {
                    if ($current == $i) {
                        $buffer .= '<li class="current">';
                            $buffer .= '<strong>' . $i . '</strong>';
                        $buffer .= '</li>';
                    } else {
                        $buffer .= '<li class="other">';

                        if ($i == 1) {
                            $buffer .= '<a href="' . $this->urlDefault . '">';
                                $buffer .= '<span>' . $i . '</span>';
                            $buffer .= '</a>';
                        } else {
                            $buffer .= '<a href="' . $this->urlBegin . $i . $this->urlEnd . '">';
                                $buffer .= '<span>' . $i . '</span>';
                            $buffer .= '</a>';
                        }

                        $buffer .= '</li>';
                    }
                }
            } else {
                if ($current == 1) {
                    $buffer .= '<li class="current">';
                        $buffer .= '<strong>1</strong>';
                    $buffer .= '</li>';
                } else {
                    $buffer .= '<li class="other">';
                        $buffer .= '<a href="' . $this->urlDefault . '">';
                            $buffer .= '<span>1</span>';
                        $buffer .= '</a>';
                    $buffer .= '</li>';
                }

                if ($current > $between) {
                    if ($current - $between < 1)
                        $index = 1;
                    else
                        $index = $current - $between;

                    $buffer .= '<li class="jump">';

                    if ($index == 1) {
                        $buffer .= '<a href="' . $this->urlDefault . '">';
                            $buffer .= '<span>...</span>';
                        $buffer .= '</a>';
                    } else {
                        $buffer .= '<a href="' . $this->urlBegin . $index . $this->urlEnd . '">';
                            $buffer .= '<span>...</span>';
                        $buffer .= '</a>';
                    }

                    $buffer .= '</li>';
                }

                $offset = [
                    'begin' => 0,
                    'end'   => 0
                ];

                if ($current <= $between) {
                    $offset['begin'] = 2;
                } else {
                    if ($current > $total - $between)
                        $offset['begin'] = $current - ($total - $between);
                    else
                        $offset['begin'] = floor($between >> 1);

                    $offset['begin'] = $current - $offset['begin'];
                }

                if ($current >= $total - $between + 1) {
                    $offset['end'] = $total - 1;
                } else {
                    if ($current <= $between)
                        $offset['end'] = ($between + 1) - $current;
                    else
                        $offset['end'] = floor($between >> 1);

                    $offset['end'] += $current;
                }

                for ($i = $offset['begin']; $i <= $offset['end']; ++$i) {
                    if ($current == $i) {
                        $buffer .= '<li class="current">';
                            $buffer .= '<strong>' . $i . '</strong>';
                        $buffer .= '</li>';
                    } else {
                        $buffer .= '<li class="other">';
                            $buffer .= '<a href="' . $this->urlBegin . $i . $this->urlEnd . '">';
                                $buffer .= '<span>' . $i . '</span>';
                            $buffer .= '</a>';
                        $buffer .= '</li>';
                    }
                }

                if ($current < $total - $between + 1) {
                    $buffer .= '<li class="jump">';
                        $buffer .= '<a href="' . $this->urlBegin;

                        if ($current + $between > $total)
                            $buffer .= $total;
                        else
                            $buffer .= $current + $between;

                        $buffer .= $this->urlEnd . '">';
                            $buffer .= '<span>...</span>';
                        $buffer .= '</a>';
                    $buffer .= '</li>';
                }

                if ($current == $total) {
                    $buffer .= '<li class="current">';
                        $buffer .= '<strong>' . $total . '</strong>';
                    $buffer .= '</li>';
                } else {
                    $buffer .= '<li class="other">';
                        $buffer .= '<a href="' . $this->urlBegin . $total . $this->urlEnd . '">';
                            $buffer .= '<span>' . $total . '</span>';
                        $buffer .= '</a>';
                    $buffer .= '</li>';
                }
            }

            return $buffer . '</ul>';
        }

    }

?>