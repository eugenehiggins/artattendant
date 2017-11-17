<?php
/*
 * This file is part of the ManageWP Worker plugin.
 *
 * (c) ManageWP LLC <contact@managewp.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class MWP_Action_GetStats extends MWP_Action_Abstract
{
    public function execute(array $params = array())
    {
        try {
            return mmb_stats_get($params);
        } catch (MWP_Worker_ActionResponse $e) {
            return $e->getData();
        }
    }
}
