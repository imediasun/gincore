<?php
require_once __DIR__ . '/../Core/AModel.php';

class MLockFilters extends AModel
{
    public $table = 'lock_filters';

    /**
     * @param $filter
     * @return array|mixed
     */
    public function load($filter)
    {
        $saved = db()->query('SELECT value FROM ?t WHERE name=? AND user_id=?i', array($this->table, $filter, $this->getUserId()))->el();
        return empty($saved) ? array() : json_decode($saved, true);
    }

    /**
     * @param $filter
     * @param $post
     */
    public function toggle($filter, $post)
    {
        if (isset($post['lock-button'])) {
            if ($post['lock-button'] == 1) {
                $this->lock($filter, $post);
            } else {
                $this->unlock($filter);
            }
        }
    }

    /**
     * @param $filter
     * @return int
     */
    public function unlock($filter)
    {
        return $this->deleteAll(array('name' => $filter, 'user_id' => $this->getUserId()));
    }

    /**
     * @param $filter
     * @param $post
     */
    protected function lock($filter, $post)
    {
        $saved = $this->load($filter);
        if (empty($saved)) {
            $this->insert(array(
                'title' => lq('Сохраненнные настройки фильтра') . '-' . $filter,
                'name' => $filter,
                'value' => json_encode($post),
                'user_id' => $this->getUserId()
            ));
        } else {
            $this->update(array(
                'value' => json_encode($post)
            ), array('name' => $filter, 'user_id' => $this->getUserId()));
        }
    }

    /**
     * @return array
     */
    public function columns()
    {
        return array(
            'id',
            'user_id',
            'title',
            'name',
            'value',
        );
    }
}
