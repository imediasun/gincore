<?php

interface ItemsInterface
{
    /**
     * @param $data
     * @return mixed
     */
    public function getTitle($data);

    /**
     * @param $data
     * @return mixed
     */
    public function getCategories($data);
    
    /**
     * @param $data
     * @return mixed
     */
    public function getCategory($data);
    
    /**
     * @param $data
     * @return mixed
     */
    public function getSubcategories($data);

    /**
     * @param $data
     * @return int
     */
    public function getPrice($data);

    /**
     * @param $data
     * @return int
     */
    public function getPurchase($data);

    /**
     * @param $data
     * @return int
     */
    public function getWholesale($data);
}