<?php
/**
 * @author Alex Phillips <aphillips@cbcnewmedia.com>
 * Date: 11/1/14
 * Time: 1:23 PM
 */

class PersistentObject extends App
{
    public static function create($params = array())
    {
        $po = null;
        if (is_string($params)) {
            $po = static::findByIdentifier($params);
        }
        else if (isset($params['identifier'])) {
            $po = static::findByIdentifier($params['identifier']);
            if ($po) {
                unset($params['identifier']);
                $po->update($params);
            }
        }

        if (!$po) {
            if (is_string($params)) {
                $params = array(
                    'identifier' => $params,
                );
            }

            return parent::create($params);
        }

        return $po;
    }

    public function getData()
    {
        return unserialize($this->data['value']);
    }

    public function setData($data)
    {
        $this->data['value'] = serialize($data);
    }
}