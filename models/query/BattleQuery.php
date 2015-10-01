<?php
namespace app\models\query;

use yii\db\ActiveQuery;
use app\models\BattleFilterForm;

class BattleQuery extends ActiveQuery
{
    public function filter(BattleFilterForm $filter)
    {
        return $this
            ->filterByScreenName($filter->screen_name)
            ->filterByRule($filter->rule)
            ->filterByMap($filter->map)
            ->filterByWeapon($filter->weapon);
    }

    public function filterByScreenName($value)
    {
        $value = trim((string)$value);
        if ($value === '') {
            return $this;
        }
        return $this->innerJoinWith('user')->andWhere(['{{user}}.[[screen_name]]' => $value]);
    }

    public function filterByRule($value)
    {
        $value = trim((string)$value);
        if ($value === '') {
            return $this;
        }
        $this->innerJoinWith('rule');
        if (substr($value, 0, 1) === '@') {
            $this->innerJoinWith('rule.mode');
            $this->andWhere(['{{game_mode}}.[[key]]' => substr($value, 1)]);
        } else {
            $this->andWhere(['{{rule}}.[[key]]' => $value]);
        }
        return $this;
    }

    public function filterByMap($value)
    {
        $value = trim((string)$value);
        if ($value === '') {
            return $this;
        }
        return $this->innerJoinWith('map')->andWhere(['{{map}}.[[key]]' => $value]);
    }

    public function filterByWeapon($value)
    {
        $value = trim((string)$value);
        if ($value === '') {
            return $this;
        }
        $this->innerJoinWith('weapon');
        switch (substr($value, 0, 1)) {
            default:
                $this->andWhere(['{{weapon}}.[[key]]' => $value]);
                break;

            case '@':
                $this->innerJoinWith('weapon.type');
                $this->andWhere(['{{weapon_type}}.[[key]]' => substr($value, 1)]);
                break;

            case '+':
                $this->innerJoinWith('weapon.subweapon');
                $this->andWhere(['{{subweapon}}.[[key]]' => substr($value, 1)]);
                break;

            case '*':
                $this->innerJoinWith('weapon.special');
                $this->andWhere(['{{special}}.[[key]]' => substr($value, 1)]);
                break;
        }
        return $this;
    }
}