<?php
/*
 * This file is part of the ManageWP Worker plugin.
 *
 * (c) ManageWP LLC <contact@managewp.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class MWP_EventListener_MasterRequest_AuthenticateServiceRequest implements Symfony_EventDispatcher_EventSubscriberInterface
{
    private $configuration;

    private $signer;

    private $context;

    function __construct(MWP_Worker_Configuration $configuration, MWP_Signer_Interface $signer, MWP_WordPress_Context $context)
    {
        $this->configuration = $configuration;
        $this->signer        = $signer;
        $this->context       = $context;
    }

    public static function getSubscribedEvents()
    {
        return array(
            MWP_Event_Events::MASTER_REQUEST => array('onMasterRequest', 350),
        );
    }

    public function onMasterRequest(MWP_Event_MasterRequest $event)
    {
        $request = $event->getRequest();

        if ($request->isAuthenticated()) {
            return;
        }

        if ($request->getAction() === 'add_site') {
            return;
        }

        $serviceSignature = $request->getServiceSignature();
        $keyName          = $request->getKeyName();

        if (empty($serviceSignature) || empty($keyName)) {
            return;
        }

        $publicKey = $this->configuration->getLivePublicKey($keyName);

        if (empty($publicKey)) {
            // for now do not throw an exception, just do not authenticate the request
            // later we should start throwing an exception here when this becomes the main communication method
            return;
        }

        $communicationKey = $this->configuration->getCommunicationStringByKeyName($keyName);
        $messageToCheck   = $request->server['HTTP_HOST'].$communicationKey.$request->getAction().$request->getNonce().json_encode($request->getParams());

        if (empty($messageToCheck)) {
            // for now do not throw an exception, just do not authenticate the request
            // later we should start throwing an exception here when this becomes the main communication method
            return;
        }

        $verify = $this->signer->verify($messageToCheck, $serviceSignature, $publicKey);

        if (!$verify) {
            // for now do not throw an exception, just do not authenticate the request
            // later we should start throwing an exception here when this becomes the main communication method
            return;
        }

        $request->setAuthenticated(true);
        $this->context->optionSet('mwp_new_communication_established', true);
    }
}
