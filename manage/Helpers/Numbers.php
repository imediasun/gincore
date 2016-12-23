<?php

require_once __DIR__ . '/../Core/Helper.php';

class Numbers extends Helper
{
    /**
     * @param      $price
     * @param int  $zero
     * @param null $course
     * @return string
     */
    public function price($price, $zero = 2, $course = null)
    {
        // делим на курс
        if ($course > 0) {
            $price = $price * ($course / 100);
        }

        // округляем и переводим с копеек
        $price = round($price / 100, 2);
        return number_format($price, $zero, '.', '');
    }
}
