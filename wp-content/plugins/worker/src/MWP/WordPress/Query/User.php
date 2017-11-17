<?php
/*
 * This file is part of the ManageWP Worker plugin.
 *
 * (c) ManageWP LLC <contact@managewp.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class MWP_WordPress_Query_User extends MWP_WordPress_Query_Abstract
{
    public function query(array $options = array())
    {
        $options += array(
            'query'          => null,
            'prefixName'     => '{{prefix}}',
            'deserialize'    => array(),
            'tryDeserialize' => array(),
        );

        // We change the {{prefix}}users and {{prefix}}usermeta string with base_prefix because in a multisite network,
        // all of the websites are sharing the same users and usermeta tables.
        $query = str_replace($options['prefixName'].'users', $this->getDb()->base_prefix.'users', $options['query']);
        $query = str_replace($options['prefixName'].'usermeta', $this->getDb()->base_prefix.'usermeta', $query);
        $query = str_replace($options['prefixName'], $this->getDb()->prefix, $query);
        $users = $this->getDb()->get_results($query, ARRAY_A);
        $this->deserialize($users, $options['deserialize'], $options['tryDeserialize']);

        return $users;
    }
}

