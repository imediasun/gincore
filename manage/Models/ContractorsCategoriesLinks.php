<?php
require_once __DIR__ . '/../Core/AModel.php';

class MContractorsCategoriesLinks extends AModel
{
    public $table = 'contractors_categories_links';

    /**
     * @param $contractor_category
     * @param $contractors
     */
    public function addCategoryToContractors($contractor_category, $contractors)
    {
        if(!is_array($contractors)) {
            $contractors = array($contractors);
        }
        foreach ($contractors as $contractor) {
            if ($this->query('SELECT count(*) FROM ?t WHERE contractors_categories_id=?i and contractors_id=?i',
                array($this->table, $contractor_category, $contractor))->el()
            ) {
                $this->query('UPDATE ?t SET `deleted`=0 WHERE contractors_categories_id=?i and contractors_id=?i',
                    array($this->table, $contractor_category, $contractor));
            } else {
                $this->query('INSERT ?t (contractors_categories_id, contractors_id) VALUES (?i, ?i)',
                    array($this->table, $contractor_category, $contractor));
            }
        }
    }

    /**
     * @param $contractor_category
     * @param $contractors
     */
    public function updateCategoryToContractors($contractor_category, $contractors)
    {
        $this->deleteContractorsCategoryLink($contractor_category, $contractors);
        $this->addCategoryToContractors($contractor_category, $contractors);
    }

    /**
     * @param $contractor_category
     * @param $contractors
     */
    public function deleteContractorsCategoryLink($contractor_category, $contractors)
    {
        if(!is_array($contractors)) {
            $contractors = array($contractors);
        }
        $this->query('UPDATE ?t SET deleted=1 WHERE contractors_categories_id=?i and contractors_id in (?li)',
            array($this->table, $contractor_category, $contractors));

    }

    /**
     * @return array
     */
    public function columns()
    {
        return array(
            'id',
            'contractors_categories_id',
            'contractors_id',
            'deleted'
        );
    }
}
